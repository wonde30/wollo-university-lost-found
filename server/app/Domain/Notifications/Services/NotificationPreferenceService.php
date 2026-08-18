<?php

namespace App\Domain\Notifications\Services;

use App\Models\NotificationPreference;

class NotificationPreferenceService
{
    public function isEnabled(int $userId, string $type, string $channel = 'email'): bool
    {
        $pref = NotificationPreference::where('user_id', $userId)
            ->where('channel', $channel)
            ->where('notification_type', $type)
            ->first();

        return $pref ? (bool)$pref->is_enabled : true;
    }
}
