<?php

namespace App\Policies;

use App\Models\Campus;
use App\Models\User;

class CampusPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Campus $campus): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Campus $campus): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Campus $campus): bool
    {
        return $user->isAdmin();
    }
}
