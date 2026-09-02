<?php

namespace App\Policies;

use App\Models\ReturnRecord;
use App\Models\User;

class ReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function view(User $user, ReturnRecord $return): bool
    {
        return $user->id === $return->returned_to || $user->isAdmin() || $user->isOfficer();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('PROCESS_RETURNS');
    }
}
