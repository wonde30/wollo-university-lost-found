<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'channel' => $this->channel,
            'notification_type' => $this->notification_type,
            'is_enabled' => (bool)$this->is_enabled,
        ];
    }
}
