<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Claims;

use App\Events\ClaimSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Claims\StoreClaimRequest;
use App\Http\Resources\Api\V1\ClaimResource;
use App\Models\Claim;
use App\Models\ClaimEvidence;
use App\Models\ClaimStatusHistory;
use App\Models\Item;
use App\Support\Enums\ClaimStatus;
use App\Support\Enums\ItemStatus;
use App\Support\Enums\ItemType;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Claim::with(['item', 'claimant', 'evidence']);

        if (! $user->isAdmin() && ! $user->isOfficer()) {
            $query->where('claimant_id', $user->id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $claims = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => ClaimResource::collection($claims),
            'meta' => [
                'current_page' => $claims->currentPage(),
                'last_page' => $claims->lastPage(),
                'total' => $claims->total(),
            ],
        ]);
    }

    public function store(StoreClaimRequest $request): JsonResponse
    {
        $user = $request->user();
        $itemId = $request->integer('item_id');

        try {
            $claim = DB::transaction(function () use ($request, $user, $itemId) {
                $item = Item::lockForUpdate()->findOrFail($itemId);

                // FR-34: Cannot claim lost items or own found items
                if ($item->type !== ItemType::FOUND || $item->status !== ItemStatus::FOUND_UNCLAIMED) {
                    throw ValidationException::withMessages([
                        'item_id' => ['Claims can only be submitted for unclaimed found items.'],
                    ]);
                }

                if ($item->reporter_id === $user->id) {
                    throw ValidationException::withMessages([
                        'item_id' => ['You cannot submit a claim on an item you reported as found.'],
                    ]);
                }

                // FR-36: UNIQUE constraint on (item_id, claimant_id)
                if (Claim::where('item_id', $item->id)->where('claimant_id', $user->id)->exists()) {
                    throw ValidationException::withMessages([
                        'item_id' => ['You have already submitted an active claim for this item.'],
                    ]);
                }

                $claim = Claim::create([
                    'item_id' => $item->id,
                    'claimant_id' => $user->id,
                    'explanation' => $request->input('explanation'),
                    'status' => ClaimStatus::PENDING,
                    'ip_address' => $request->ip(),
                ]);

                if ($request->hasFile('evidence')) {
                    foreach ($request->file('evidence') as $file) {
                        $path = $file->store('claim-evidence', 'local');
                        ClaimEvidence::create([
                            'claim_id' => $claim->id,
                            'uploaded_by' => $user->id,
                            'evidence_type' => 'photo',
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size_bytes' => $file->getSize(),
                            'uploaded_at' => now(),
                        ]);
                    }
                }

                ClaimStatusHistory::create([
                    'claim_id' => $claim->id,
                    'changed_by' => $user->id,
                    'from_status' => null,
                    'to_status' => ClaimStatus::PENDING->value,
                    'changed_by_role' => $user->role->value,
                    'note' => 'Claim submitted by student',
                    'ip_address' => $request->ip(),
                ]);

                AuditLogger::log('claim.created', $claim, null, $claim->toArray(), $user);

                return $claim;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'item_id' => ['You have already submitted an active claim for this item.'],
            ]);
        }

        // Fire event — listeners deliver notifications to claimant & item reporter
        ClaimSubmitted::dispatch($claim);

        return response()->json([
            'message' => 'Claim submitted successfully.',
            'data' => new ClaimResource($claim->load(['item', 'claimant', 'evidence'])),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $claim = Claim::with(['item', 'claimant', 'reviewer', 'evidence', 'statusHistories'])->findOrFail($id);
        $this->authorize('view', $claim);

        return response()->json([
            'data' => new ClaimResource($claim),
        ]);
    }
}
