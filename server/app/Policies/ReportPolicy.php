<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }
}
