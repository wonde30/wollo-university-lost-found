<?php

namespace App\Http\Requests\Api\V1\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_on_report_submitted' => ['sometimes', 'boolean'],
            'email_on_match_found' => ['sometimes', 'boolean'],
            'email_on_claim_received' => ['sometimes', 'boolean'],
            'email_on_claim_decided' => ['sometimes', 'boolean'],
            'email_on_item_returned' => ['sometimes', 'boolean'],
            'email_on_expiry_warning' => ['sometimes', 'boolean'],
            'email_on_item_expired' => ['sometimes', 'boolean'],
            'email_on_system_announcements' => ['sometimes', 'boolean'],
        ];
    }
}
