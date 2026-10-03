<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }
}
