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
use App\Support\Enums\ClaimStatus;
use App\Support\Enums\ItemStatus;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Handles staff review decisions on pending claims
 * and admin dispute reversals.
 *
 * Security/correctness fixes applied:
 *  - Fix #3: lockForUpdate() added on claim approval to prevent
 *    concurrent double-approval race conditions.
 *  - Fix #4: reverse() method implemented (was a missing route handler).
 */
class ClaimReviewController extends Controller
{
    /**
     * Staff: Approve or reject a pending claim.
     *
     * On approval:
     *  - claim row is row-locked before update (lockForUpdate)
     *  - item transitions to `claimed`
     *  - competing pending claims are auto-rejected
     *  - full audit and history recorded
     */
    public function review(ClaimReviewRequest $request, int $id): JsonResponse
    {
        $user       = $request->user();
        $status     = $request->input('status');      // 'approved' | 'rejected'
        $reviewNote = $request->input('review_note');

        DB::transaction(function () use ($id, $user, $status, $reviewNote, $request) {
            // Fix #3: Lock the claim row before reading to prevent
            // two concurrent approvals from racing each other.
            $claim = Claim::with('item')
                ->lockForUpdate()
                ->findOrFail($id);

            $this->authorize('review', $claim);

            // Guard: only pending claims can be reviewed
            if ($claim->status !== ClaimStatus::PENDING) {
                throw ValidationException::withMessages([
                    'claim' => ["This claim is already {$claim->status->value} and cannot be reviewed again."],
                ]);
            }

            $fromStatus = $claim->status->value;

            $claim->status      = $status === 'approved' ? ClaimStatus::APPROVED : ClaimStatus::REJECTED;
            $claim->reviewed_by = $user->id;
            $claim->review_note = $reviewNote;
            $claim->reviewed_at = now();
            $claim->save();

            ClaimStatusHistory::create([
                'claim_id'        => $claim->id,
                'changed_by'      => $user->id,
                'from_status'     => $fromStatus,
                'to_status'       => $claim->status->value,
                'changed_by_role' => $user->role->value,
                'note'            => $reviewNote,
                'ip_address'      => $request->ip(),
            ]);

            if ($status === 'approved') {
                $item = $claim->item;

                // Transition item to claimed
                $item->status           = ItemStatus::CLAIMED;
                $item->last_activity_at = now();
                $item->save();

                ItemStatusHistory::create([
                    'item_id'         => $item->id,
                    'changed_by'      => $user->id,
                    'from_status'     => ItemStatus::FOUND_UNCLAIMED->value,
                    'to_status'       => ItemStatus::CLAIMED->value,
                    'changed_by_role' => $user->role->value,
                    'note'            => "Claim #{$claim->id} approved.",
                    'ip_address'      => $request->ip(),
                ]);

                // FR-39: Auto-reject all other pending claims for the same item
                // Uses lockForUpdate to prevent a race where a second claim
                // could also be marked approved by another concurrent request.
                $competing = Claim::where('item_id', $item->id)
                    ->where('id', '!=', $claim->id)
                    ->where('status', ClaimStatus::PENDING)
                    ->lockForUpdate()
                    ->get();

                foreach ($competing as $other) {
                    $other->status      = ClaimStatus::REJECTED;
                    $other->auto_rejected = true;
                    $other->review_note = 'Automatically rejected because another claim was verified and approved.';
                    $other->reviewed_at = now();
                    $other->reviewed_by = $user->id;
                    $other->save();

                    ClaimStatusHistory::create([
                        'claim_id'        => $other->id,
                        'changed_by'      => $user->id,
                        'from_status'     => ClaimStatus::PENDING->value,
                        'to_status'       => ClaimStatus::REJECTED->value,
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
                ['status' => $claim->status->value],
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
     *
     * Fix #4: This method was entirely missing. The route existed but had no handler.
     *
     * Reversal resets:
     *  - claim → rejected
     *  - item  → found_unclaimed (available for new claims)
     *  - All competing auto-rejected claims → pending again (reopened)
     *  - Full history and audit recorded
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
            if ($claim->status !== ClaimStatus::APPROVED) {
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

            $fromStatus = $claim->status->value;

            // Reset claim to rejected
            $claim->status      = ClaimStatus::REJECTED;
            $claim->review_note = $reviewNote;
            $claim->reviewed_at = now();
            $claim->reviewed_by = $user->id;
            $claim->save();

            ClaimStatusHistory::create([
                'claim_id'        => $claim->id,
                'changed_by'      => $user->id,
                'from_status'     => $fromStatus,
                'to_status'       => ClaimStatus::REJECTED->value,
                'changed_by_role' => $user->role->value,
                'note'            => "Dispute reversal: {$reviewNote}",
                'ip_address'      => $request->ip(),
            ]);

            // Reset item back to found_unclaimed
            $item = $claim->item;
            $item->status           = ItemStatus::FOUND_UNCLAIMED;
            $item->last_activity_at = now();
            $item->save();

            ItemStatusHistory::create([
                'item_id'         => $item->id,
                'changed_by'      => $user->id,
                'from_status'     => ItemStatus::CLAIMED->value,
                'to_status'       => ItemStatus::FOUND_UNCLAIMED->value,
                'changed_by_role' => $user->role->value,
                'note'            => "Claim #{$claim->id} reversed by admin dispute resolution.",
                'ip_address'      => $request->ip(),
            ]);

            // Reopen auto-rejected competing claims so they can be reviewed again
            $autoRejected = Claim::where('item_id', $item->id)
                ->where('id', '!=', $claim->id)
                ->where('status', ClaimStatus::REJECTED)
                ->where('auto_rejected', true)
                ->lockForUpdate()
                ->get();

            foreach ($autoRejected as $other) {
                $other->status        = ClaimStatus::PENDING;
                $other->auto_rejected = false;
                $other->review_note   = null;
                $other->reviewed_at   = null;
                $other->reviewed_by   = null;
                $other->save();

                ClaimStatusHistory::create([
                    'claim_id'        => $other->id,
                    'changed_by'      => $user->id,
                    'from_status'     => ClaimStatus::REJECTED->value,
                    'to_status'       => ClaimStatus::PENDING->value,
                    'changed_by_role' => 'system',
                    'note'            => 'Reopened after original approval was reversed.',
                    'ip_address'      => $request->ip(),
                ]);
            }

            AuditLogger::log(
                'claim.reversed',
                $claim,
                ['status' => $fromStatus],
                ['status' => ClaimStatus::REJECTED->value],
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
