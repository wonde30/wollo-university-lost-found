<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'permission_group_id' => ['required', 'integer', 'exists:permission_groups,id'],
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:permissions,name'],
            'display_name' => ['required', 'string', 'max:150'],
            'display_name_am' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'description_am' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'in:items,claims,custody,admin'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
