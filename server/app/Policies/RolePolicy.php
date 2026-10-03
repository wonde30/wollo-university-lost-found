<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_PERMISSIONS');
    }
}
