<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StorePermissionRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePermissionRequest;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Models\Permission;
use App\Support\Services\AuditLogger;
use App\Support\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if ($request->boolean('all')) {
            $permissions = $this->permissionService->getAllPermissions();
            return response()->json([
                'data' => PermissionResource::collection($permissions),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $permissions = $this->permissionService->getPermissions($request->all(), $perPage);

        return PermissionResource::collection($permissions);
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->createPermission($request->validated());

        AuditLogger::log('permission.created', null, [], $permission->toArray(), $request->user());

        return (new PermissionResource($permission))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): PermissionResource
    {
        return new PermissionResource($permission);
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): PermissionResource
    {
        $oldData = $permission->toArray();
        $updated = $this->permissionService->updatePermission($permission, $request->validated());

        AuditLogger::log('permission.updated', null, $oldData, $updated->toArray(), $request->user());

        return new PermissionResource($updated);
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Request $request, Permission $permission): JsonResponse
    {
        try {
            $oldData = $permission->toArray();
            $this->permissionService->deletePermission($permission);

            AuditLogger::log('permission.deleted', null, $oldData, [], $request->user());

            return response()->json([
                'message' => 'Permission deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Toggle the active state of the permission.
     */
    public function toggleActive(Request $request, Permission $permission): PermissionResource
    {
        $updated = $this->permissionService->toggleActive($permission);

        AuditLogger::log('permission.toggle_active', null, [
            'is_active' => ! $updated->is_active,
        ], [
            'is_active' => $updated->is_active,
        ], $request->user());

        return new PermissionResource($updated);
    }
}
