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

        $perPage = min(100, max(1, $request->integer('per_page', 10)));

        $events = CustodyEvent::with(['item', 'actor', 'storageLocation'])
            ->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->integer('item_id')))
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->string('event_type')->toString()))
            ->when($request->filled('storage_location_id'), fn ($q) => $q->where('storage_location_id', $request->integer('storage_location_id')))
            ->when($request->filled('actor_id'), fn ($q) => $q->where('actor_id', $request->integer('actor_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('notes', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('condition', 'like', "%{$search}%")
                        ->orWhereHas('item', fn ($iq) => $iq->where('title', 'like', "%{$search}%")->orWhere('reference_code', 'like', "%{$search}%"))
                        ->orWhereHas('actor', fn ($uq) => $uq->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('storageLocation', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('building', 'like', "%{$search}%")->orWhere('room', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => CustodyEventResource::collection($events),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'per_page'     => $events->perPage(),
                'total'        => $events->total(),
                'from'         => $events->firstItem(),
                'to'           => $events->lastItem(),
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
