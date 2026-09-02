<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateUserRequest;
use App\Http\Requests\Api\V1\Admin\UpdateUserRoleRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $perPage = min(100, max(1, $request->integer('per_page', 10)));

        $users = User::with(['profile', 'role.permissions', 'organizationalUnits', 'directPermissions'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->string('search')->toString());
                $q->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('university_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $role = trim($request->string('role')->toString());
                $q->whereHas('role', fn ($sub) => $sub->where('name', $role));
            })
            ->when($request->has('is_active'), function ($q) use ($request) {
                $q->where('is_active', $request->boolean('is_active'));
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'from'         => $users->firstItem(),
                'to'           => $users->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'full_name'              => ['required', 'string', 'max:150'],
            'university_id'          => ['required', 'string', 'max:30', 'unique:users,university_id'],
            'email'                  => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'password'               => ['required', 'string', 'min:8'],
            'role_id'                => ['required', 'integer', 'exists:roles,id'],
            'phone'                  => ['nullable', 'string', 'max:20'],
            'is_active'              => ['nullable', 'boolean'],
            'organizational_unit_id' => ['nullable', 'integer', 'exists:organizational_units,id'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'full_name'     => $validated['full_name'],
                'university_id' => $validated['university_id'],
                'email'         => $validated['email'],
                'password'      => bcrypt($validated['password']),
                'role_id'       => $validated['role_id'],
                'phone'         => $validated['phone'] ?? null,
                'is_active'     => $validated['is_active'] ?? true,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
            ]);

            if (! empty($validated['organizational_unit_id'])) {
                $user->organizationalUnits()->syncWithoutDetaching([
                    $validated['organizational_unit_id'] => ['is_primary' => true],
                ]);
            }

            return $user;
        });

        return response()->json([
            'message' => 'User created successfully',
            'data'    => new UserResource($user->load(['profile', 'role', 'organizationalUnits'])),
        ], JsonResponse::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['profile', 'role', 'organizationalUnits', 'directPermissions'])->findOrFail($id);
        $this->authorize('view', $user);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $user->update($request->validated());

        return response()->json([
            'message' => 'User updated successfully',
            'data'    => new UserResource($user->fresh(['profile', 'role', 'organizationalUnits', 'directPermissions'])),
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        if ($request->filled('role_id')) {
            $roleId = (int) $request->input('role_id');
        } else {
            $roleName = (string) $request->input('role');
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => ucfirst($roleName),
                    'is_system'    => in_array($roleName, ['admin', 'staff', 'student'], true),
                    'is_active'    => true,
                ]
            );
            $roleId = $role->id;
        }

        DB::transaction(function () use ($user, $roleId) {
            $user->update(['role_id' => $roleId]);
        });

        User::flushPermissionCache();

        return response()->json([
            'message' => 'User role updated successfully',
            'data'    => new UserResource($user->fresh(['profile', 'role', 'organizationalUnits'])),
        ]);
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $oldStatus = $user->is_active;
        $newStatus = ! $oldStatus;

        DB::transaction(function () use ($user, $newStatus) {
            $user->update(['is_active' => $newStatus]);
        });

        \App\Support\Services\AuditLogger::log(
            $newStatus ? 'user.activated' : 'user.deactivated',
            $user,
            ['is_active' => $oldStatus],
            ['is_active' => $newStatus],
            $request->user()
        );

        return response()->json([
            'message' => $user->is_active ? 'User account activated' : 'User account deactivated',
            'data'    => new UserResource($user),
        ]);
    }

    public function getPermissions(int $id): JsonResponse
    {
        $user = User::with(['role.permissions', 'directPermissions'])->findOrFail($id);
        $this->authorize('view', $user);

        $allPermissions = \App\Models\Permission::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $rolePermissions      = $user->getRolePermissionNames();
        $directPermissions    = $user->getDirectPermissionNames();
        $effectivePermissions = $user->getPermissionNames();
        $directPermissionIds  = $user->directPermissions()->pluck('permissions.id')->all();

        return response()->json([
            'data' => [
                'user_id'                => $user->id,
                'role'                   => $user->getRoleName(),
                'role_permissions'       => $rolePermissions,
                'direct_permissions'     => $directPermissions,
                'direct_permission_ids'  => $directPermissionIds,
                'effective_permissions'  => $effectivePermissions,
                'available_permissions'  => $allPermissions,
            ],
        ]);
    }

    public function syncPermissions(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $validated = $request->validate([
            'permission_ids'   => ['present', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $oldDirect = $user->getDirectPermissionNames();

        DB::transaction(function () use ($user, $validated) {
            $user->syncDirectPermissions($validated['permission_ids']);
        });

        $newDirect = $user->getDirectPermissionNames();

        \App\Support\Services\AuditLogger::log(
            'user.permissions_synced',
            $user,
            ['direct_permissions' => $oldDirect],
            ['direct_permissions' => $newDirect],
            $request->user()
        );

        return response()->json([
            'message' => 'User custom permissions updated successfully',
            'data'    => [
                'user'                  => new UserResource($user->fresh(['profile', 'role', 'organizationalUnits', 'directPermissions'])),
                'direct_permissions'    => $newDirect,
                'effective_permissions' => $user->getPermissionNames(),
            ],
        ]);
    }
}
