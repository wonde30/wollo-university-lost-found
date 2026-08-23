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
            'email_on_report_submitted' => (bool) $this->email_on_report_submitted,
            'email_on_match_found' => (bool) $this->email_on_match_found,
            'email_on_claim_received' => (bool) $this->email_on_claim_received,
            'email_on_claim_decided' => (bool) $this->email_on_claim_decided,
            'email_on_item_returned' => (bool) $this->email_on_item_returned,
            'email_on_expiry_warning' => (bool) $this->email_on_expiry_warning,
            'email_on_item_expired' => (bool) $this->email_on_item_expired,
            'email_on_system_announcements' => (bool) $this->email_on_system_announcements,
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
