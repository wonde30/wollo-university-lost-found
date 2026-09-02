<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StorePermissionGroupRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePermissionGroupRequest;
use App\Http\Resources\Api\V1\PermissionGroupResource;
use App\Models\PermissionGroup;
use App\Support\Services\AuditLogger;
use App\Support\Services\PermissionGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class PermissionGroupController extends Controller
{
    public function __construct(
        protected PermissionGroupService $permissionGroupService
    ) {}

    /**
     * Display a listing of permission groups.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if ($request->boolean('all')) {
            $groups = $this->permissionGroupService->getAllActiveGroups();
            return response()->json([
                'data' => PermissionGroupResource::collection($groups),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $groups = $this->permissionGroupService->getGroups($request->all(), $perPage);

        return PermissionGroupResource::collection($groups);
    }

    /**
     * Store a newly created permission group in storage.
     */
    public function store(StorePermissionGroupRequest $request): JsonResponse
    {
        $group = $this->permissionGroupService->createGroup($request->validated());

        AuditLogger::log('permission_group.created', null, [], $group->toArray(), $request->user());

        return (new PermissionGroupResource($group))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified permission group.
     */
    public function show(PermissionGroup $permissionGroup): PermissionGroupResource
    {
        return new PermissionGroupResource($permissionGroup->load(['permissions']));
    }

    /**
     * Update the specified permission group in storage.
     */
    public function update(UpdatePermissionGroupRequest $request, PermissionGroup $permissionGroup): PermissionGroupResource
    {
        $oldData = $permissionGroup->toArray();
        $updated = $this->permissionGroupService->updateGroup($permissionGroup, $request->validated());

        AuditLogger::log('permission_group.updated', null, $oldData, $updated->toArray(), $request->user());

        return new PermissionGroupResource($updated);
    }

    /**
     * Remove the specified permission group from storage.
     */
    public function destroy(Request $request, PermissionGroup $permissionGroup): JsonResponse
    {
        try {
            $oldData = $permissionGroup->toArray();
            $this->permissionGroupService->deleteGroup($permissionGroup);

            AuditLogger::log('permission_group.deleted', null, $oldData, [], $request->user());

            return response()->json([
                'message' => 'Permission group deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Toggle the active state of the permission group.
     */
    public function toggleActive(Request $request, PermissionGroup $permissionGroup): PermissionGroupResource
    {
        $updated = $this->permissionGroupService->toggleActive($permissionGroup);

        AuditLogger::log('permission_group.toggle_active', null, [
            'is_active' => ! $updated->is_active,
        ], [
            'is_active' => $updated->is_active,
        ], $request->user());

        return new PermissionGroupResource($updated);
    }
}
