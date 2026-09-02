<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCampusRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCampusRequest;
use App\Http\Resources\Api\V1\CampusResource;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Campus::class);

        $query = Campus::with(['organizationalUnits', 'locations'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('short_code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name');

        if ($request->boolean('all') && ! $request->filled('search')) {
            $data = \Illuminate\Support\Facades\Cache::remember('campuses.all', 3600, function () {
                $campuses = Campus::with(['organizationalUnits', 'locations'])
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
                return CampusResource::collection($campuses)->resolve();
            });

            return response()->json([
                'data' => $data,
            ]);
        }

        if ($request->boolean('all')) {
            return response()->json([
                'data' => CampusResource::collection($query->get()),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $campuses = $query->paginate($perPage);

        return response()->json([
            'data' => CampusResource::collection($campuses),
            'meta' => [
                'current_page' => $campuses->currentPage(),
                'last_page'    => $campuses->lastPage(),
                'per_page'     => $campuses->perPage(),
                'total'        => $campuses->total(),
                'from'         => $campuses->firstItem(),
                'to'           => $campuses->lastItem(),
            ],
        ]);
    }

    public function store(StoreCampusRequest $request): JsonResponse
    {
        $this->authorize('create', Campus::class);
        $validated = $request->validated();
        if (empty($validated['short_code'])) {
            $validated['short_code'] = $validated['code'] ?? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 5));
        }
        unset($validated['code']);
        $validated['city'] = $validated['city'] ?? 'Dessie';
        $validated['region'] = $validated['region'] ?? 'Amhara';

        $campus = Campus::create($validated);
        \Illuminate\Support\Facades\Cache::forget('campuses.all');

        return response()->json([
            'message' => 'Campus created successfully',
            'data' => new CampusResource($campus),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $campus = Campus::with(['organizationalUnits', 'locations'])->findOrFail($id);
        $this->authorize('view', $campus);

        return response()->json([
            'data' => new CampusResource($campus),
        ]);
    }

    public function update(UpdateCampusRequest $request, int $id): JsonResponse
    {
        $campus = Campus::findOrFail($id);
        $this->authorize('update', $campus);

        $validated = $request->validated();
        if (empty($validated['short_code']) && !empty($validated['code'])) {
            $validated['short_code'] = $validated['code'];
        }
        unset($validated['code'], $validated['description']);

        $campus->update($validated);
        \Illuminate\Support\Facades\Cache::forget('campuses.all');

        return response()->json([
            'message' => 'Campus updated successfully',
            'data' => new CampusResource($campus),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $campus = Campus::findOrFail($id);
        $this->authorize('delete', $campus);

        // Soft delete: deactivate instead of hard delete
        $campus->update(['is_active' => false]);
        \Illuminate\Support\Facades\Cache::forget('campuses.all');

        return response()->json([
            'message' => 'Campus deactivated successfully',
        ]);
    }

    /**
     * Restore (reactivate) a deactivated campus.
     */
    public function restore(int $id): JsonResponse
    {
        $campus = Campus::findOrFail($id);
        $this->authorize('update', $campus);

        $campus->update(['is_active' => true]);
        \Illuminate\Support\Facades\Cache::forget('campuses.all');

        return response()->json([
            'message' => 'Campus activated successfully',
            'data' => new CampusResource($campus),
        ]);
    }
}

