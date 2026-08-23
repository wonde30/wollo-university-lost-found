<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Models\Notification;

class NotificationService
{
    /**
     * Persist a notification row to the custom notifications table.
     *
     * The table uses a bigint auto-increment PK — do NOT pass an id;
     * the database generates it automatically.
     */
    public function send(NotificationData $data): Notification
    {
        return Notification::create([
            'user_id' => $data->userId,
            'type'    => $data->type,
            'channel' => 'database',
            'data'    => $data->payload,
        ]);
    }
}
