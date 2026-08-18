<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreLocationRequest;
use App\Http\Requests\Api\V1\Admin\UpdateLocationRequest;
use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Location::class);
        $locations = Location::with('campus')->get();

        return response()->json([
            'data' => LocationResource::collection($locations),
        ]);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $this->authorize('create', Location::class);
        $location = Location::create($request->validated());

        return response()->json([
            'message' => 'Location created successfully',
            'data' => new LocationResource($location->load('campus')),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $location = Location::with('campus')->findOrFail($id);
        $this->authorize('view', $location);

        return response()->json([
            'data' => new LocationResource($location),
        ]);
    }

    public function update(UpdateLocationRequest $request, int $id): JsonResponse
    {
        $location = Location::findOrFail($id);
        $this->authorize('update', $location);

        $location->update($request->validated());

        return response()->json([
            'message' => 'Location updated successfully',
            'data' => new LocationResource($location->load('campus')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $location = Location::findOrFail($id);
        $this->authorize('delete', $location);

        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
