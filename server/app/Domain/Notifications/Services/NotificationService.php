<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Events\NotificationCreated;
use App\Models\Notification;

class NotificationService
{
    /**
     * Persist a notification row to the custom notifications table and broadcast it in real-time.
     */
    public function send(NotificationData $data): Notification
    {
        $notification = Notification::create([
            'user_id' => $data->userId,
            'type'    => $data->type,
            'channel' => 'database',
            'data'    => $data->payload,
        ]);

        NotificationCreated::dispatch($notification);

        return $notification;
    }
}
