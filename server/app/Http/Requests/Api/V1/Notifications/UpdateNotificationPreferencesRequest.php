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
            'preferences' => ['required', 'array'],
            'preferences.*.channel' => ['required', 'string', 'in:email,database,sms,push'],
            'preferences.*.notification_type' => ['required', 'string'],
            'preferences.*.is_enabled' => ['required', 'boolean'],
        ];
    }
}
