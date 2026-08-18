<?php

namespace App\Policies;

use App\Models\SystemAnnouncement;
use App\Models\User;

class SystemAnnouncementPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, SystemAnnouncement $announcement): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOfficer();
    }

    public function update(User $user, SystemAnnouncement $announcement): bool
    {
        return $user->isAdmin() || $user->id === $announcement->created_by;
    }

    public function delete(User $user, SystemAnnouncement $announcement): bool
    {
        return $user->isAdmin() || $user->id === $announcement->created_by;
    }
}
