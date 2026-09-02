<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreLocationRequest;
use App\Http\Requests\Api\V1\Admin\UpdateLocationRequest;
use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Location::class);

        $query = Location::with('campus')
            ->when($request->filled('campus_id'), fn ($q) => $q->where('campus_id', $request->integer('campus_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('name_am', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('building', 'like', "%{$search}%")
                        ->orWhere('room_number', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name');

        if ($request->boolean('all')) {
            return response()->json([
                'data' => LocationResource::collection($query->get()),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $locations = $query->paginate($perPage);

        return response()->json([
            'data' => LocationResource::collection($locations),
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

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $this->authorize('create', Location::class);
        $location = Location::create($request->validated());
        \Illuminate\Support\Facades\Cache::forget('locations.all');

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
        \Illuminate\Support\Facades\Cache::forget('locations.all');

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
        \Illuminate\Support\Facades\Cache::forget('locations.all');

        return response()->json([
            'message' => 'Location deleted successfully',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}

