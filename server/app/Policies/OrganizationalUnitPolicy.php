<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrganizationalUnit;
use App\Models\User;

class OrganizationalUnitPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, OrganizationalUnit $unit): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_CAMPUSES');
    }

    public function update(User $user, OrganizationalUnit $unit): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_CAMPUSES');
    }

    public function delete(User $user, OrganizationalUnit $unit): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_CAMPUSES');
    }
}
