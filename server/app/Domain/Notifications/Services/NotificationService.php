<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public function send(NotificationData $data): Notification
    {
        return Notification::create([
            'id' => (string) Str::uuid(),
            'user_id' => $data->userId,
            'type' => $data->type,
            'data' => $data->payload,
        ]);
    }
}
