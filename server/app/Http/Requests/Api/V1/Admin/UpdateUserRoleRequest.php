<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => [
                'required_without:role_id',
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! in_array($value, ['admin', 'staff', 'student'], true) && ! \App\Models\Role::where('name', $value)->exists()) {
                        $fail('The selected role is invalid.');
                    }
                },
            ],
            'role_id' => ['required_without:role', 'nullable', 'integer', 'exists:roles,id'],
        ];
    }
}
