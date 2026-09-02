<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Custody;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Custody\StoreStorageLocationRequest;
use App\Http\Requests\Api\V1\Custody\UpdateStorageLocationRequest;
use App\Http\Resources\Api\V1\StorageLocationResource;
use App\Models\StorageLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorageLocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StorageLocation::class);

        $query = StorageLocation::with('campus')
            ->when($request->filled('campus_id'), fn ($q) => $q->where('campus_id', $request->integer('campus_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('building', 'like', "%{$search}%")
                        ->orWhere('shelf_cabinet_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        if ($request->boolean('all')) {
            return response()->json([
                'data' => StorageLocationResource::collection($query->get()),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $locations = $query->paginate($perPage);

        return response()->json([
            'data' => StorageLocationResource::collection($locations),
            'meta' => [
                'current_page' => $locations->currentPage(),
                'last_page'    => $locations->lastPage(),
                'per_page'     => $locations->perPage(),
                'total'        => $locations->total(),
                'from'         => $locations->firstItem(),
                'to'           => $locations->lastItem(),
            ],
        ]);
    }

    public function store(StoreStorageLocationRequest $request): JsonResponse
    {
        $this->authorize('create', StorageLocation::class);

        $location = StorageLocation::create($request->validated());

        return response()->json([
            'message' => 'Storage location created successfully',
            'data' => new StorageLocationResource($location->load('campus')),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $location = StorageLocation::with('campus')->findOrFail($id);
        $this->authorize('view', $location);

        return response()->json([
            'data' => new StorageLocationResource($location),
        ]);
    }

    public function update(UpdateStorageLocationRequest $request, int $id): JsonResponse
    {
        $location = StorageLocation::findOrFail($id);
        $this->authorize('update', $location);

        $location->update($request->validated());

        return response()->json([
            'message' => 'Storage location updated successfully',
            'data' => new StorageLocationResource($location->load('campus')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $location = StorageLocation::findOrFail($id);
        $this->authorize('delete', $location);

        $location->delete();

        return response()->json([
            'message' => 'Storage location deleted',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
