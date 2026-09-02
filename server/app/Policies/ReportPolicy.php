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

    public function view(User $user, Report $report): bool
    {
        return $user->isAdmin() || $user->isOfficer() || $report->requested_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->isAdmin() || $report->requested_by === $user->id;
    }
}
