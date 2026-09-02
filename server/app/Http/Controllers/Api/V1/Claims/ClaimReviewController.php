<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Claims;

use App\Events\ClaimApproved;
use App\Events\ClaimRejected;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Claims\ClaimReviewRequest;
use App\Http\Resources\Api\V1\ClaimResource;
use App\Models\Claim;
use App\Models\ClaimStatusHistory;
use App\Models\ItemStatusHistory;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Handles staff review decisions on pending claims
 * and admin dispute reversals.
 */
class ClaimReviewController extends Controller
{
    /**
     * Staff: Approve or reject a pending claim.
     */
    public function review(ClaimReviewRequest $request, int $id): JsonResponse
    {
        $user       = $request->user();
        $status     = $request->input('status');      // 'approved' | 'rejected'
        $reviewNote = $request->input('review_note');

        DB::transaction(function () use ($id, $user, $status, $reviewNote, $request) {
            $claim = Claim::with('item')
                ->lockForUpdate()
                ->findOrFail($id);

            $this->authorize('review', $claim);

            // Guard: only pending claims can be reviewed
            if ($claim->status !== 'pending') {
                throw ValidationException::withMessages([
                    'claim' => ["This claim is already {$claim->status} and cannot be reviewed again."],
                ]);
            }

            $fromStatus = (string) $claim->status;

            $claim->status      = $status === 'approved' ? 'approved' : 'rejected';
            $claim->reviewed_by = $user->id;
            $claim->review_note = $reviewNote;
            $claim->reviewed_at = now();
            $claim->save();

            ClaimStatusHistory::create([
                'claim_id'        => $claim->id,
                'changed_by'      => $user->id,
                'from_status'     => $fromStatus,
                'to_status'       => (string) $claim->status,
                'changed_by_role' => $user->getRoleName(),
                'note'            => $reviewNote,
                'ip_address'      => $request->ip(),
            ]);

            if ($status === 'approved') {
                $item = $claim->item;

                // Transition item to claimed
                $item->status           = 'claimed';
                $item->last_activity_at = now();
                $item->save();

                ItemStatusHistory::create([
                    'item_id'         => $item->id,
                    'changed_by'      => $user->id,
                    'from_status'     => 'found_unclaimed',
                    'to_status'       => 'claimed',
                    'changed_by_role' => $user->getRoleName(),
                    'note'            => "Claim #{$claim->id} approved.",
                    'ip_address'      => $request->ip(),
                ]);

                // FR-39: Auto-reject all other pending claims for the same item
                $competing = Claim::where('item_id', $item->id)
                    ->where('id', '!=', $claim->id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->get();

                foreach ($competing as $other) {
                    $other->status      = 'rejected';
                    $other->auto_rejected = true;
                    $other->review_note = 'Automatically rejected because another claim was verified and approved.';
                    $other->reviewed_at = now();
                    $other->reviewed_by = $user->id;
                    $other->save();

                    ClaimStatusHistory::create([
                        'claim_id'        => $other->id,
                        'changed_by'      => $user->id,
                        'from_status'     => 'pending',
                        'to_status'       => 'rejected',
                        'changed_by_role' => 'system',
                        'was_auto_rejected' => true,
                        'note'            => 'Auto-rejected due to competing claim approval.',
                        'ip_address'      => $request->ip(),
                    ]);
                }
            }

            AuditLogger::log(
                "claim.{$status}",
                $claim,
                ['status' => $fromStatus],
                ['status' => (string) $claim->status],
                $user
            );
        });

        $claim = Claim::with(['item', 'claimant', 'reviewer'])->findOrFail($id);

        // Fire notification event AFTER the transaction so the claim is fully committed
        if ($status === 'approved') {
            ClaimApproved::dispatch($claim);
        } else {
            ClaimRejected::dispatch($claim);
        }

        return response()->json([
            'message' => "Claim {$status} successfully.",
            'data'    => new ClaimResource($claim),
        ]);
    }

    /**
     * Admin: Reverse a previously approved claim (dispute resolution).
     */
    public function reverse(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'review_note' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $user       = $request->user();
        $reviewNote = $request->input('review_note');

        DB::transaction(function () use ($id, $user, $reviewNote, $request) {
            $claim = Claim::with('item')
                ->lockForUpdate()
                ->findOrFail($id);

            $this->authorize('reverse', $claim);

            // Only approved claims can be reversed
            if ($claim->status !== 'approved') {
                throw ValidationException::withMessages([
                    'claim' => ['Only approved claims can be reversed.'],
                ]);
            }

            // Guard: cannot reverse if return already recorded
            if ($claim->returnRecord()->exists()) {
                throw ValidationException::withMessages([
                    'claim' => ['Cannot reverse a claim that already has a physical return recorded.'],
                ]);
            }

            $fromStatus = (string) $claim->status;

            // Reset claim to rejected
            $claim->status      = 'rejected';
            $claim->review_note = $reviewNote;
            $claim->reviewed_at = now();
            $claim->reviewed_by = $user->id;
            $claim->save();

            ClaimStatusHistory::create([
                'claim_id'        => $claim->id,
                'changed_by'      => $user->id,
                'from_status'     => $fromStatus,
                'to_status'       => 'rejected',
                'changed_by_role' => $user->getRoleName(),
                'note'            => "Dispute reversal: {$reviewNote}",
                'ip_address'      => $request->ip(),
            ]);

            // Reset item back to found_unclaimed
            $item = $claim->item;
            $item->status           = 'found_unclaimed';
            $item->last_activity_at = now();
            $item->save();

            ItemStatusHistory::create([
                'item_id'         => $item->id,
                'changed_by'      => $user->id,
                'from_status'     => 'claimed',
                'to_status'       => 'found_unclaimed',
                'changed_by_role' => $user->getRoleName(),
                'note'            => "Claim #{$claim->id} reversed by admin dispute resolution.",
                'ip_address'      => $request->ip(),
            ]);

            // Reopen auto-rejected competing claims so they can be reviewed again
            $autoRejected = Claim::where('item_id', $item->id)
                ->where('id', '!=', $claim->id)
                ->where('status', 'rejected')
                ->where('auto_rejected', true)
                ->lockForUpdate()
                ->get();

            foreach ($autoRejected as $other) {
                $other->status        = 'pending';
                $other->auto_rejected = false;
                $other->review_note   = null;
                $other->reviewed_at   = null;
                $other->reviewed_by   = null;
                $other->save();

                ClaimStatusHistory::create([
                    'claim_id'        => $other->id,
                    'changed_by'      => $user->id,
                    'from_status'     => 'rejected',
                    'to_status'       => 'pending',
                    'changed_by_role' => 'system',
                    'note'            => 'Reopened after original approval was reversed.',
                    'ip_address'      => $request->ip(),
                ]);
            }

            AuditLogger::log(
                'claim.reversed',
                $claim,
                ['status' => $fromStatus],
                ['status' => 'rejected'],
                $user
            );
        });

        $claim = Claim::with(['item', 'claimant', 'reviewer'])->findOrFail($id);

        return response()->json([
            'message' => 'Claim approval reversed. Item is available for new claims.',
            'data'    => new ClaimResource($claim),
        ]);
    }
}
