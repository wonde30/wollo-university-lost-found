<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\UniversityDomain;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UniversityDomainPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_SETTINGS') || $user->hasPermission('ACCESS_ADMIN_DASHBOARD');
    }

    public function view(User $user, ?UniversityDomain $domain = null): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_SETTINGS') || $user->hasPermission('ACCESS_ADMIN_DASHBOARD');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_SETTINGS');
    }

    public function update(User $user, ?UniversityDomain $domain = null): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_SETTINGS');
    }

    public function delete(User $user, ?UniversityDomain $domain = null): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_SETTINGS');
    }
}
