<?php

namespace App\Policies;

use App\Models\StorageLocation;
use App\Models\User;

class StorageLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function view(User $user, StorageLocation $location): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function update(User $user, StorageLocation $location): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function delete(User $user, StorageLocation $location): bool
    {
        return $user->isAdmin();
    }
}
