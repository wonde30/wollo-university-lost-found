<?php

namespace App\Policies;

use App\Models\SystemSetting;
use App\Models\User;

class SystemSettingPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function update(User $user, ?SystemSetting $setting = null): bool
    {
        return $user->isAdmin() || $user->hasPermission('MANAGE_SETTINGS');
    }
}
