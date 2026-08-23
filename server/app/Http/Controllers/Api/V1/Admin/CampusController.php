<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCampusRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCampusRequest;
use App\Http\Resources\Api\V1\CampusResource;
use App\Models\Campus;
use Illuminate\Http\JsonResponse;

class CampusController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Campus::class);
        $campuses = Campus::with(['departments', 'locations'])->get();

        return response()->json([
            'data' => CampusResource::collection($campuses),
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

        return response()->json([
            'message' => 'Campus created successfully',
            'data' => new CampusResource($campus),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $campus = Campus::with(['departments', 'locations'])->findOrFail($id);
        $this->authorize('view', $campus);

        return response()->json([
            'data' => new CampusResource($campus),
        ]);
    }

    public function update(UpdateCampusRequest $request, int $id): JsonResponse
    {
        $campus = Campus::findOrFail($id);
        $this->authorize('update', $campus);

        $campus->update($request->validated());

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

        return response()->json([
            'message' => 'Campus activated successfully',
            'data' => new CampusResource($campus),
        ]);
    }
}
