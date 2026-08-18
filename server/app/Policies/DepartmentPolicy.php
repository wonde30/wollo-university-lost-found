<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Department $dept): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Department $dept): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Department $dept): bool
    {
        return $user->isAdmin();
    }
}
