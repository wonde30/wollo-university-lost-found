<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Items;

use App\Events\ItemReported;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Items\StoreFoundItemRequest;
use App\Http\Requests\Api\V1\Items\StoreLostItemRequest;
use App\Http\Requests\Api\V1\Items\UpdateItemRequest;
use App\Http\Resources\Api\V1\ItemDetailResource;
use App\Http\Resources\Api\V1\ItemResource;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\ItemStatusHistory;
use App\Models\ItemTag;
use App\Support\Enums\CustodyEventType;
use App\Support\Enums\ItemHeldAt;
use App\Support\Enums\ItemStatus;
use App\Support\Enums\ItemType;
use App\Support\Helpers\ReferenceCode;
use App\Support\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Item::with(['category', 'location', 'reporter', 'photos'])
            ->where('is_deleted', false); // exclude soft-deleted items for all roles

        if (! $user->isAdmin() && ! $user->isOfficer()) {
            $query->where('reporter_id', $user->id);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $items   = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => ItemResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function storeLost(StoreLostItemRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $item = DB::transaction(function () use ($validated, $user, $request) {
            $ref = ReferenceCode::generate('WU');

            $item = Item::create([
                'reference_code' => $ref,
                'reporter_id' => $user->id,
                'campus_id' => $validated['campus_id'] ?? 1,
                'type' => ItemType::LOST,
                'status' => ItemStatus::LOST,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'location_id' => $validated['location_id'] ?? null,
                'location_detail' => $validated['location_detail'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'color' => $validated['color'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'incident_date' => $validated['incident_date'],
                'incident_time' => $validated['incident_time'] ?? null,
                'estimated_value' => $validated['estimated_value'] ?? null,
                'is_high_value' => $validated['is_high_value'] ?? false,
                'last_activity_at' => now(),
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store('item-photos', 'public');
                    ItemPhoto::create([
                        'item_id' => $item->id,
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'size_bytes' => $photo->getSize(),
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $user->id,
                'from_status' => null,
                'to_status' => ItemStatus::LOST->value,
                'changed_by_role' => $user->role->value,
                'note' => 'Initial lost item report submitted',
                'ip_address' => $request->ip(),
            ]);

            AuditLogger::log('item.created', $item, null, $item->toArray(), $user);

            return $item;
        });

        $item->load(['category', 'location', 'reporter', 'photos', 'tags']);

        // Fire event — listener kicks off async match-suggestion generation
        ItemReported::dispatch($item);

        return response()->json([
            'message' => 'Lost item report submitted successfully.',
            'data' => new ItemDetailResource($item),
        ], JsonResponse::HTTP_CREATED);
    }

    public function storeFound(StoreFoundItemRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $item = DB::transaction(function () use ($validated, $user, $request) {
            $ref = ReferenceCode::generate('WU');
            $heldAt = $validated['held_at'] ?? 'security_office';

            $item = Item::create([
                'reference_code' => $ref,
                'reporter_id' => $user->id,
                'campus_id' => $validated['campus_id'] ?? 1,
                'type' => ItemType::FOUND,
                'status' => ItemStatus::FOUND_UNCLAIMED,
                'held_at' => $heldAt,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'location_id' => $validated['location_id'] ?? null,
                'location_detail' => $validated['location_detail'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'color' => $validated['color'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'incident_date' => $validated['incident_date'],
                'incident_time' => $validated['incident_time'] ?? null,
                'last_activity_at' => now(),
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    $path = $photo->store('item-photos', 'public');
                    ItemPhoto::create([
                        'item_id' => $item->id,
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'size_bytes' => $photo->getSize(),
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            if (! empty($validated['storage_location_id'])) {
                CustodyEvent::create([
                    'item_id' => $item->id,
                    'actor_id' => $user->id,
                    'storage_location_id' => $validated['storage_location_id'],
                    'event_type' => CustodyEventType::DEPOSITED,
                    'condition' => 'good',
                    'notes' => 'Initial deposit into physical storage',
                ]);
            }

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by' => $user->id,
                'from_status' => null,
                'to_status' => ItemStatus::FOUND_UNCLAIMED->value,
                'changed_by_role' => $user->role->value,
                'note' => 'Found item registered into system',
                'ip_address' => $request->ip(),
            ]);

            AuditLogger::log('item.created', $item, null, $item->toArray(), $user);

            return $item;
        });

        $item->load(['category', 'location', 'reporter', 'photos', 'tags']);

        // Fire event — listener kicks off async match-suggestion generation
        ItemReported::dispatch($item);

        return response()->json([
            'message' => 'Found item report submitted successfully.',
            'data' => new ItemDetailResource($item),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $item = Item::with(['category', 'location', 'reporter', 'photos', 'tags', 'statusHistories', 'custodyEvents'])
            ->withCount('claims')
            ->findOrFail($id);

        return response()->json([
            'data' => new ItemDetailResource($item),
        ]);
    }

    public function update(UpdateItemRequest $request, int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $this->authorize('update', $item);

        $item->update($request->validated());
        AuditLogger::log('item.updated', $item, null, $item->toArray(), $request->user());

        return response()->json([
            'message' => 'Item updated successfully.',
            'data' => new ItemDetailResource($item->load(['category', 'location', 'reporter', 'photos'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = Item::findOrFail($id);
        $this->authorize('delete', $item);

        $item->delete();
        AuditLogger::log('item.deleted', $item, null, null, auth()->user());

        return response()->json([
            'message' => 'Item deleted successfully.',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
