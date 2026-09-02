<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrganizationalUnitType;
use App\Models\User;

class OrganizationalUnitTypePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, OrganizationalUnitType $type): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_CAMPUSES');
    }

    public function update(User $user, OrganizationalUnitType $type): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_CAMPUSES');
    }

    public function delete(User $user, OrganizationalUnitType $type): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_CAMPUSES');
    }
}
