<?php

namespace App\Domain\Notifications\Services;

use App\Models\NotificationPreference;

class NotificationPreferenceService
{
    public function isEnabled(int $userId, string $type, string $channel = 'email'): bool
    {
        $pref = NotificationPreference::where('user_id', $userId)->first();
        if (! $pref) {
            return true;
        }

        $column = 'email_on_' . $type;
        if (isset($pref->$column)) {
            return (bool) $pref->$column;
        }

        return true;
    }
}
