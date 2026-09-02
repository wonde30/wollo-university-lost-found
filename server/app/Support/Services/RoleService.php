<?php

declare(strict_types=1);

namespace App\Support\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RoleService
{
    /**
     * Get paginated roles with optional search and filter.
     */
    public function getRoles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = min(100, max(1, $perPage));

        return Role::with('permissions')->withCount('users')
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = '%' . trim((string) $filters['search']) . '%';
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', $search)
                        ->orWhere('display_name', 'like', $search)
                        ->orWhere('display_name_am', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null, function ($q) use ($filters) {
                $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when(isset($filters['is_system']) && $filters['is_system'] !== '' && $filters['is_system'] !== null, function ($q) use ($filters) {
                $q->where('is_system', filter_var($filters['is_system'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('display_name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get all active roles (for dropdowns/selects).
     */
    public function getAllActiveRoles(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('roles.active', 3600, function () {
            return Role::with('permissions')
                ->where('is_active', true)
                ->orderBy('display_name', 'asc')
                ->get();
        });
    }

    /**
     * Create a new role.
     */
    public function createRole(array $data, array $permissionIds = []): Role
    {
        $role = DB::transaction(function () use ($data, $permissionIds) {
            $name = Str::slug($data['name'] ?? $data['display_name'], '_');

            $role = Role::create([
                'name' => $name,
                'display_name' => $data['display_name'],
                'display_name_am' => $data['display_name_am'] ?? null,
                'description' => $data['description'] ?? null,
                'description_am' => $data['description_am'] ?? null,
                'is_system' => false,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (! empty($permissionIds)) {
                $role->permissions()->sync($permissionIds);
            }

            return $role->load('permissions');
        });

        User::flushPermissionCache();
        \Illuminate\Support\Facades\Cache::forget('roles.active');

        return $role;
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Role $role, array $data, ?array $permissionIds = null): Role
    {
        $updated = DB::transaction(function () use ($role, $data, $permissionIds) {
            $payload = [
                'display_name' => $data['display_name'] ?? $role->display_name,
                'display_name_am' => $data['display_name_am'] ?? $role->display_name_am,
                'description' => $data['description'] ?? $role->description,
                'description_am' => $data['description_am'] ?? $role->description_am,
                'is_active' => $data['is_active'] ?? $role->is_active,
            ];

            // System roles cannot have their internal slug name modified
            if (! $role->is_system && ! empty($data['name'])) {
                $payload['name'] = Str::slug($data['name'], '_');
            }

            $role->update($payload);

            if ($permissionIds !== null) {
                $role->permissions()->sync($permissionIds);
            }

            return $role->load('permissions');
        });

        User::flushPermissionCache();
        \Illuminate\Support\Facades\Cache::forget('roles.active');

        return $updated;
    }

    /**
     * Delete a role (protected from system roles or roles with assigned users).
     */
    public function deleteRole(Role $role): bool
    {
        if ($role->is_system) {
            throw new InvalidArgumentException('System roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            throw new InvalidArgumentException('Cannot delete role while users are assigned to it. Reassign users first.');
        }

        $deleted = (bool) $role->delete();
        User::flushPermissionCache();
        \Illuminate\Support\Facades\Cache::forget('roles.active');

        return $deleted;
    }

    /**
     * Sync permissions to a role.
     */
    public function syncPermissions(Role $role, array $permissionIds): Role
    {
        DB::transaction(function () use ($role, $permissionIds) {
            $role->permissions()->sync($permissionIds);
        });

        User::flushPermissionCache();
        \Illuminate\Support\Facades\Cache::forget('roles.active');

        return $role->load('permissions');
    }

}
