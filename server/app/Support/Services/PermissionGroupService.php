<?php

declare(strict_types=1);

namespace App\Support\Services;

use App\Models\PermissionGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PermissionGroupService
{
    /**
     * Get paginated permission groups with search and active status filter.
     */
    public function getGroups(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = min(100, max(1, $perPage));

        return PermissionGroup::query()->withCount('permissions')
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = '%' . trim((string) $filters['search']) . '%';
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', $search)
                        ->orWhere('display_name', 'like', $search)
                        ->orWhere('display_name_am', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('description_am', 'like', $search);
                });
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null && $filters['is_active'] !== 'all', function ($q) use ($filters) {
                $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('display_name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get all active permission groups with their active permissions loaded.
     */
    public function getAllActiveGroups(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('permission_groups.all', 3600, function () {
            return PermissionGroup::where('is_active', true)
                ->with(['activePermissions'])
                ->withCount('permissions')
                ->orderBy('is_system', 'desc')
                ->orderBy('display_name', 'asc')
                ->get();
        });
    }

    /**
     * Create a new permission group.
     */
    public function createGroup(array $data): PermissionGroup
    {
        $name = ! empty($data['name'])
            ? Str::slug($data['name'], '_')
            : Str::slug($data['display_name'], '_');

        $group = PermissionGroup::create([
            'name' => $name,
            'display_name' => $data['display_name'],
            'display_name_am' => $data['display_name_am'] ?? null,
            'description' => $data['description'] ?? null,
            'description_am' => $data['description_am'] ?? null,
            'is_system' => false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        \Illuminate\Support\Facades\Cache::forget('permission_groups.all');

        return $group;
    }

    /**
     * Update an existing permission group.
     */
    public function updateGroup(PermissionGroup $group, array $data): PermissionGroup
    {
        $payload = [
            'display_name' => $data['display_name'] ?? $group->display_name,
            'display_name_am' => $data['display_name_am'] ?? $group->display_name_am,
            'description' => $data['description'] ?? $group->description,
            'description_am' => $data['description_am'] ?? $group->description_am,
            'is_active' => $data['is_active'] ?? $group->is_active,
        ];

        // System groups cannot have their name/key changed
        if (! $group->is_system && ! empty($data['name'])) {
            $payload['name'] = Str::slug($data['name'], '_');
        }

        $group->update($payload);
        \Illuminate\Support\Facades\Cache::forget('permission_groups.all');

        return $group->fresh(['permissions']);
    }

    /**
     * Delete a permission group with safety checks.
     * System groups and groups containing permissions cannot be deleted.
     */
    public function deleteGroup(PermissionGroup $group): bool
    {
        if ($group->is_system) {
            throw new InvalidArgumentException('System permission groups cannot be deleted.');
        }

        if ($group->permissions()->exists()) {
            throw new InvalidArgumentException('Cannot delete permission group with assigned permissions. Reassign or remove permissions first.');
        }

        $deleted = (bool) $group->delete();
        \Illuminate\Support\Facades\Cache::forget('permission_groups.all');

        return $deleted;
    }

    /**
     * Toggle the active state of a permission group.
     */
    public function toggleActive(PermissionGroup $group): PermissionGroup
    {
        $group->update(['is_active' => ! $group->is_active]);
        \Illuminate\Support\Facades\Cache::forget('permission_groups.all');

        return $group->fresh(['permissions']);
    }
}

