<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id');

        return [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'university_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'university_id')->ignore($userId)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['sometimes', 'string'],
            'language' => ['nullable', 'string', 'in:en,am'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function passedValidation(): void
    {
        if (empty($this->full_name) && !empty($this->name)) {
            $this->merge(['full_name' => $this->name]);
        }
    }
}
