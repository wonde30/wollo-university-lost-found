<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreDepartmentRequest;
use App\Http\Requests\Api\V1\Admin\UpdateDepartmentRequest;
use App\Http\Resources\Api\V1\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Department::class);
        $depts = Department::with('campus')->get();

        return response()->json([
            'data' => DepartmentResource::collection($depts),
        ]);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Department::class);
        $validated = $request->validated();
        if (empty($validated['short_code'])) {
            $validated['short_code'] = $validated['code'] ?? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['name']), 0, 6));
        }
        unset($validated['code']);
        $dept = Department::create($validated);

        return response()->json([
            'message' => 'Department created successfully',
            'data' => new DepartmentResource($dept->load('campus')),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $dept = Department::with('campus')->findOrFail($id);
        $this->authorize('view', $dept);

        return response()->json([
            'data' => new DepartmentResource($dept),
        ]);
    }

    public function update(UpdateDepartmentRequest $request, int $id): JsonResponse
    {
        $dept = Department::findOrFail($id);
        $this->authorize('update', $dept);

        $dept->update($request->validated());

        return response()->json([
            'message' => 'Department updated successfully',
            'data' => new DepartmentResource($dept->load('campus')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $dept = Department::findOrFail($id);
        $this->authorize('delete', $dept);

        $dept->delete();

        return response()->json([
            'message' => 'Department deleted successfully',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
