<?php

namespace App\Policies;

use App\Models\CustodyEvent;
use App\Models\User;

class CustodyEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function view(User $user, CustodyEvent $event): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_CUSTODY');
    }
}
