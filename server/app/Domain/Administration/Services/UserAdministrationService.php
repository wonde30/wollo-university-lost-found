<?php

declare(strict_types=1);

namespace App\Domain\Administration\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserAdministrationService
{
    public function updateUserRole(User $user, string $newRole): User
    {
        return DB::transaction(function () use ($user, $newRole) {
            $roleId = Role::where('name', $newRole)->value('id');
            $user->update(['role_id' => $roleId]);
            return $user;
        });
    }

    public function toggleUserActiveStatus(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $user->update(['is_active' => ! $user->is_active]);
            return $user;
        });
    }
}
