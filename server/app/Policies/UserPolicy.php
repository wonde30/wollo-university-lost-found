<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_USERS') || $user->isOfficer();
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin() || $user->hasPermission('MANAGE_USERS') || $user->isOfficer();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_USERS');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin() || $user->hasPermission('MANAGE_USERS');
    }

    public function delete(User $user, User $model): bool
    {
        return ($user->isAdmin() || $user->hasPermission('MANAGE_USERS')) && $user->id !== $model->id;
    }
}
