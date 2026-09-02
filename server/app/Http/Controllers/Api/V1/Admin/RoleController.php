<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreRoleRequest;
use App\Http\Requests\Api\V1\Admin\SyncRolePermissionsRequest;
use App\Http\Requests\Api\V1\Admin\UpdateRoleRequest;
use App\Http\Resources\Api\V1\RoleResource;
use App\Models\Role;
use App\Support\Services\AuditLogger;
use App\Support\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if ($request->boolean('all')) {
            $roles = $this->roleService->getAllActiveRoles();
            return response()->json([
                'data' => RoleResource::collection($roles),
            ]);
        }

        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $roles = $this->roleService->getRoles($request->all(), $perPage);

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->createRole(
            $request->validated(),
            $request->input('permission_ids', [])
        );

        AuditLogger::log('role.created', null, [], $role->toArray(), $request->user());

        return (new RoleResource($role))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): RoleResource
    {
        return new RoleResource($role->load('permissions')->loadCount('users'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $oldData = $role->toArray();
        $updated = $this->roleService->updateRole(
            $role,
            $request->validated(),
            $request->has('permission_ids') ? $request->input('permission_ids') : null
        );

        AuditLogger::log('role.updated', null, $oldData, $updated->toArray(), $request->user());

        return new RoleResource($updated->loadCount('users'));
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        try {
            $oldData = $role->toArray();
            $this->roleService->deleteRole($role);

            AuditLogger::log('role.deleted', null, $oldData, [], $request->user());

            return response()->json([
                'message' => 'Role deleted successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Sync permissions for the role.
     */
    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): RoleResource
    {
        $oldPerms = $role->permissions->pluck('name')->all();
        $updated = $this->roleService->syncPermissions($role, $request->input('permission_ids', []));

        AuditLogger::log('role.permissions_synced', null, [
            'role' => $role->name,
            'permissions' => $oldPerms,
        ], [
            'role' => $role->name,
            'permissions' => $updated->permissions->pluck('name')->all(),
        ], $request->user());

        return new RoleResource($updated->loadCount('users'));
    }
}
