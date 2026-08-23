<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:campuses,name'],
            'short_code' => ['nullable', 'string', 'max:10', 'unique:campuses,short_code'],
            'code' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:80'],
            'region' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
