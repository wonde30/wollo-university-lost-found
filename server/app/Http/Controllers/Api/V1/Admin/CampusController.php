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
        $campus = Campus::create($request->validated());

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

        $campus->delete();

        return response()->json([
            'message' => 'Campus deleted successfully',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
