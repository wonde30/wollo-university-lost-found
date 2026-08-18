<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreStorageLocationRequest;
use App\Http\Requests\Api\V1\Admin\UpdateStorageLocationRequest;
use App\Http\Resources\Api\V1\StorageLocationResource;
use App\Models\StorageLocation;
use Illuminate\Http\JsonResponse;

class StorageLocationController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', StorageLocation::class);
        $locations = StorageLocation::with('campus')->get();

        return response()->json([
            'data' => StorageLocationResource::collection($locations),
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
            'message' => 'Storage location deleted successfully',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
