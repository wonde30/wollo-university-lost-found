<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PermissionGroup;
use App\Models\User;

class PermissionGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function view(User $user, PermissionGroup $permissionGroup): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function update(User $user, PermissionGroup $permissionGroup): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function delete(User $user, PermissionGroup $permissionGroup): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }
}
