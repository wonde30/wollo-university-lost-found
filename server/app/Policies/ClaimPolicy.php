<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;

class ClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function view(User $user, Claim $claim): bool
    {
        return $user->id === $claim->claimant_id
            || $user->id === $claim->item?->reporter_id
            || $user->isAdmin()
            || $user->isOfficer();
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_active;
    }

    public function review(User $user, Claim $claim): bool
    {
        return $user->isAdmin() || $user->hasPermission('REVIEW_CLAIMS');
    }

    public function reverse(User $user, Claim $claim): bool
    {
        return $user->isAdmin() || $user->hasPermission('REVERSE_CLAIMS');
    }

    public function update(User $user, Claim $claim): bool
    {
        $status = (string) $claim->status;
        return $user->id === $claim->claimant_id && $status === 'pending';
    }

    public function delete(User $user, Claim $claim): bool
    {
        $status = (string) $claim->status;
        return ($user->id === $claim->claimant_id && $status === 'pending') || $user->isAdmin();
    }
}
