<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Returns;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Returns\StoreReturnRequest;
use App\Http\Resources\Api\V1\ReturnResource;
use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Models\ReturnRecord;
use App\Support\Enums\ClaimStatus;
use App\Support\Enums\CustodyEventType;
use App\Support\Enums\ItemStatus;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Records physical handover of found items to verified owners.
 *
 * Security/correctness fix applied:
 *  - Fix #5: store() now verifies the associated claim is 'approved'
 *    before processing a return. A pending or rejected claim can no
 *    longer trigger a return record.
 */
class ReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReturnRecord::class);

        $perPage = min($request->integer('per_page', 20), 100);

        $returns = ReturnRecord::with(['claim', 'item', 'recipient', 'staff', 'storageLocation'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => ReturnResource::collection($returns),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page'    => $returns->lastPage(),
                'per_page'     => $returns->perPage(),
                'total'        => $returns->total(),
            ],
        ]);
    }

    public function store(StoreReturnRequest $request): JsonResponse
    {
        $this->authorize('create', ReturnRecord::class);

        $user      = $request->user();
        $validated = $request->validated();

        $return = DB::transaction(function () use ($validated, $user, $request) {

            // Fix #5: Verify the claim is approved before processing a physical return.
            // A pending or rejected claim must never trigger a handover.
            $claim = Claim::lockForUpdate()->findOrFail($validated['claim_id']);

            if ($claim->status !== ClaimStatus::APPROVED) {
                throw ValidationException::withMessages([
                    'claim_id' => [
                        "Only claims with status 'approved' can be processed for a physical return. "
                        . "Current claim status: {$claim->status->value}.",
                    ],
                ]);
            }

            // Guard: prevent duplicate returns on the same claim (DB UNIQUE enforces this too)
            if ($claim->returnRecord()->exists()) {
                throw ValidationException::withMessages([
                    'claim_id' => ['A return record already exists for this claim.'],
                ]);
            }

            $item = Item::lockForUpdate()->findOrFail($validated['item_id']);

            // Verify the item belongs to this claim
            if ($item->id !== $claim->item_id) {
                throw ValidationException::withMessages([
                    'item_id' => ['The specified item does not match the claim.'],
                ]);
            }

            // Verify item is in claimed state (not already returned or in another state)
            if ($item->status !== ItemStatus::CLAIMED) {
                throw ValidationException::withMessages([
                    'item_id' => [
                        "Item must be in 'claimed' status to process a return. "
                        . "Current status: {$item->status->value}.",
                    ],
                ]);
            }

            $returnRecord = ReturnRecord::create([
                'claim_id'            => $claim->id,
                'item_id'             => $item->id,
                'returned_to'         => $validated['returned_to'],
                'handed_over_by'      => $user->id,
                'storage_location_id' => $validated['storage_location_id'] ?? null,
                'return_date'         => $validated['return_date'],
                'return_time'         => $validated['return_time'] ?? now()->format('H:i:s'),
                'condition_on_return' => $validated['condition_on_return'],
                'notes'               => $validated['notes'] ?? null,
            ]);

            // Transition item to returned
            $fromStatus             = $item->status->value;
            $item->status           = ItemStatus::RETURNED;
            $item->last_activity_at = now();
            $item->save();

            ItemStatusHistory::create([
                'item_id'         => $item->id,
                'changed_by'      => $user->id,
                'from_status'     => $fromStatus,
                'to_status'       => ItemStatus::RETURNED->value,
                'changed_by_role' => $user->role->value,
                'note'            => 'Physical item handed over to verified owner.',
                'ip_address'      => $request->ip(),
            ]);

            // Release from custody
            CustodyEvent::create([
                'item_id'             => $item->id,
                'actor_id'            => $user->id,
                'storage_location_id' => $validated['storage_location_id'] ?? null,
                'event_type'          => CustodyEventType::RELEASED,
                'condition'           => $validated['condition_on_return'],
                'notes'               => 'Released from custody on verified physical return.',
            ]);

            AuditLogger::log('return.recorded', $returnRecord, null, $returnRecord->toArray(), $user);

            return $returnRecord;
        });

        return response()->json([
            'message' => 'Physical return recorded successfully.',
            'data'    => new ReturnResource(
                $return->load(['claim', 'item', 'recipient', 'staff', 'storageLocation'])
            ),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $return = ReturnRecord::with(['claim', 'item', 'recipient', 'staff', 'storageLocation', 'documents'])
            ->findOrFail($id);

        $this->authorize('view', $return);

        return response()->json([
            'data' => new ReturnResource($return),
        ]);
    }
}
