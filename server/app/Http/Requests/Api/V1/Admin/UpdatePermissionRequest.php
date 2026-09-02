<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $permId = $this->route('permission') ?? $this->route('id');
        if (is_object($permId)) {
            $permId = $permId->id;
        }

        return [
            'permission_group_id' => ['sometimes', 'required', 'integer', 'exists:permission_groups,id'],
            'name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/', Rule::unique('permissions', 'name')->ignore($permId)],
            'display_name' => ['sometimes', 'required', 'string', 'max:150'],
            'display_name_am' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'description_am' => ['nullable', 'string', 'max:500'],
            'category' => ['sometimes', 'required', 'string', 'in:items,claims,custody,admin'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
