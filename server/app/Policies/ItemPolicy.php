<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

/**
 * Authorization gates for Item operations.
 *
 * Fix applied: update() and changeStatus() now correctly check
 * reporter_id (not the stale user_id alias) to prevent IDOR.
 */
class ItemPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Item $item): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * A user may update an item if:
     * - they are the original reporter, OR
     * - they are staff or admin.
     *
     * Uses reporter_id (the actual FK column) — not the old user_id alias.
     */
    public function update(User $user, Item $item): bool
    {
        return $user->id === $item->reporter_id
            || $user->isAdmin()
            || $user->isOfficer();
    }

    /**
     * Hard delete is restricted to the reporter (own report withdrawal) or admin.
     * Staff may not arbitrarily delete items — they use status changes instead.
     */
    public function delete(User $user, Item $item): bool
    {
        return $user->id === $item->reporter_id
            || $user->isAdmin();
    }

    /**
     * Status transitions are allowed for the reporter (e.g. withdraw)
     * or any staff/admin (custody operations, closures).
     */
    public function changeStatus(User $user, Item $item): bool
    {
        return $user->id === $item->reporter_id
            || $user->isAdmin()
            || $user->isOfficer();
    }
}
