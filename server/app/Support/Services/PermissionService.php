<?php

declare(strict_types=1);

namespace App\Support\Services;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PermissionService
{
    /**
     * Get paginated permissions with optional search, category, and permission group filters.
     */
    public function getPermissions(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = min(100, max(1, $perPage));

        return Permission::query()->with('permissionGroup')
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = '%' . trim((string) $filters['search']) . '%';
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', $search)
                        ->orWhere('display_name', 'like', $search)
                        ->orWhere('display_name_am', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when(! empty($filters['category']) && $filters['category'] !== 'all', function ($q) use ($filters) {
                $q->where('category', $filters['category']);
            })
            ->when(! empty($filters['permission_group_id']) && $filters['permission_group_id'] !== 'all', function ($q) use ($filters) {
                $q->where('permission_group_id', (int) $filters['permission_group_id']);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null && $filters['is_active'] !== 'all', function ($q) use ($filters) {
                $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('permission_group_id', 'asc')
            ->orderBy('display_name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get all active permissions with their permission group.
     */
    public function getAllPermissions(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('permissions.all', 3600, function () {
            return Permission::where('is_active', true)
                ->with('permissionGroup')
                ->orderBy('permission_group_id', 'asc')
                ->orderBy('display_name', 'asc')
                ->get();
        });
    }

    /**
     * Create a new permission.
     */
    public function createPermission(array $data): Permission
    {
        $name = strtoupper(Str::slug($data['name'] ?? $data['display_name'], '_'));

        $permission = Permission::create([
            'permission_group_id' => $data['permission_group_id'] ?? null,
            'name' => $name,
            'display_name' => $data['display_name'],
            'display_name_am' => $data['display_name_am'] ?? null,
            'description' => $data['description'] ?? null,
            'description_am' => $data['description_am'] ?? null,
            'category' => $data['category'] ?? 'items',
            'is_system' => false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        \Illuminate\Support\Facades\Cache::forget('permissions.all');

        return $permission;
    }

    /**
     * Update an existing permission.
     */
    public function updatePermission(Permission $permission, array $data): Permission
    {
        $payload = [
            'display_name' => $data['display_name'] ?? $permission->display_name,
            'display_name_am' => $data['display_name_am'] ?? $permission->display_name_am,
            'description' => $data['description'] ?? $permission->description,
            'description_am' => $data['description_am'] ?? $permission->description_am,
            'category' => $data['category'] ?? $permission->category,
            'is_active' => $data['is_active'] ?? $permission->is_active,
        ];

        if (array_key_exists('permission_group_id', $data)) {
            $payload['permission_group_id'] = $data['permission_group_id'];
        }

        // System permissions cannot have their key modified
        if (! $permission->is_system && ! empty($data['name'])) {
            $payload['name'] = strtoupper(Str::slug($data['name'], '_'));
        }

        $permission->update($payload);
        \Illuminate\Support\Facades\Cache::forget('permissions.all');

        return $permission->fresh(['permissionGroup']);
    }

    /**
     * Delete a permission (protected from system permissions).
     */
    public function deletePermission(Permission $permission): bool
    {
        if ($permission->is_system) {
            throw new InvalidArgumentException('System core permissions cannot be deleted.');
        }

        $deleted = (bool) $permission->delete();
        \Illuminate\Support\Facades\Cache::forget('permissions.all');

        return $deleted;
    }

    /**
     * Toggle active state of a permission.
     */
    public function toggleActive(Permission $permission): Permission
    {
        $permission->update(['is_active' => ! $permission->is_active]);
        \Illuminate\Support\Facades\Cache::forget('permissions.all');

        return $permission->fresh(['permissionGroup']);
    }
}

