<?php

namespace App\Domain\Administration\Services;

use App\Models\User;

class UserAdministrationService
{
    public function updateUserRole(User $user, string $newRole): User
    {
        $user->update(['role' => $newRole]);
        return $user;
    }

    public function toggleUserActiveStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user;
    }
}
