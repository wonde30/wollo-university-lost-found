<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'data' => $this->data,
            'action_url' => $this->resolveActionUrl(),
            'is_read' => (bool) $this->is_read,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    protected function resolveActionUrl(): ?string
    {
        $data = $this->data ?? [];

        // If explicit link or action_url is provided in data payload, respect it
        if (!empty($data['action_url']) && is_string($data['action_url'])) {
            return $data['action_url'];
        }
        if (!empty($data['link']) && is_string($data['link'])) {
            return $data['link'];
        }

        return match ($this->type) {
            'claim_submitted', 'new_claim_submitted' => match ($data['sub_type'] ?? null) {
                'staff' => '/staff/review-claims',
                'reporter' => isset($data['item_id']) ? "/items/{$data['item_id']}" : '/student/my-items',
                default => '/student/my-claims',
            },
            'claim_approved', 'claim_rejected', 'claim_under_review' => '/student/my-claims',
            'item_match', 'item_matched' => isset($data['lost_item_id'])
                ? "/items/{$data['lost_item_id']}"
                : (isset($data['item_id']) ? "/items/{$data['item_id']}" : (isset($data['found_item_id']) ? "/items/{$data['found_item_id']}" : null)),
            'item_returned', 'return_confirmation_request' => isset($data['confirmation_token'])
                ? "/confirm-return/{$data['confirmation_token']}"
                : (isset($data['token']) ? "/confirm-return/{$data['token']}" : '/student/my-claims'),
            'return_confirmed' => isset($data['item_id'])
                ? "/items/{$data['item_id']}"
                : '/staff/manage-custody',
            'item_reported', 'item_status_changed', 'item_expiring', 'custody_expiry_warning', 'item_expired' => isset($data['item_id'])
                ? "/items/{$data['item_id']}"
                : (isset($data['matched_item_code']) ? '/student/my-items' : null),
            'report_generated', 'report_failed', 'system_report_ready' => '/admin/reports',
            'custody_transferred' => '/staff/manage-custody',
            default => isset($data['item_id']) ? "/items/{$data['item_id']}" : null,
        };
    }
}
