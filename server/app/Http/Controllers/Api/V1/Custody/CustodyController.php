<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Custody;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Custody\MoveStorageRequest;
use App\Http\Requests\Api\V1\Custody\StoreCustodyEventRequest;
use App\Http\Resources\Api\V1\CustodyEventResource;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\StorageLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustodyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CustodyEvent::class);

        $query = CustodyEvent::with(['item', 'actor', 'storageLocation']);

        if ($itemId = $request->query('item_id')) {
            $query->where('item_id', $itemId);
        }

        if ($eventType = $request->query('event_type')) {
            $query->where('event_type', $eventType);
        }

        $events = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => CustodyEventResource::collection($events),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    public function store(StoreCustodyEventRequest $request): JsonResponse
    {
        $this->authorize('create', CustodyEvent::class);

        $validated = $request->validated();
        $user = $request->user();
        $event = DB::transaction(function () use ($validated, $user) {
            return CustodyEvent::create([
                'item_id' => $validated['item_id'],
                'actor_id' => $user->id,
                'storage_location_id' => $validated['storage_location_id'] ?? null,
                'event_type' => $validated['event_type'],
                'condition' => $validated['condition'] ?? 'unknown',
                'notes' => $validated['notes'] ?? null,
                'reference_photo' => $validated['reference_photo'] ?? null,
            ]);
        });

        return response()->json([
            'message' => 'Custody event recorded successfully',
            'data' => new CustodyEventResource($event->load(['item', 'actor', 'storageLocation'])),
        ], JsonResponse::HTTP_CREATED);
    }

    public function move(MoveStorageRequest $request, int $itemId): JsonResponse
    {
        $this->authorize('create', CustodyEvent::class);

        $item = Item::findOrFail($itemId);
        $user = $request->user();

        $event = DB::transaction(function () use ($item, $user, $request) {
            return CustodyEvent::create([
                'item_id' => $item->id,
                'actor_id' => $user->id,
                'storage_location_id' => $request->storage_location_id,
                'event_type' => 'transferred',
                'condition' => 'good',
                'notes' => $request->notes ?? 'Transferred to another storage shelf/room',
            ]);
        });

        return response()->json([
            'message' => 'Item moved in storage successfully',
            'data' => new CustodyEventResource($event->load(['item', 'actor', 'storageLocation'])),
        ]);
    }
}
