<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $groupId = $this->route('permission_group') ?? $this->route('id');
        if (is_object($groupId)) {
            $groupId = $groupId->id;
        }

        return [
            'name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/', Rule::unique('permission_groups', 'name')->ignore($groupId)],
            'display_name' => ['sometimes', 'required', 'string', 'max:150'],
            'display_name_am' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'description_am' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
