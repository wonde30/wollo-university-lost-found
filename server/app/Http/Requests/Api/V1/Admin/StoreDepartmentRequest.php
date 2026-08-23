<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campus_id' => ['required', 'integer', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_code' => ['nullable', 'string', 'max:50'],
            'code' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'in:college,school,institute,department,office'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function passedValidation(): void
    {
        if (empty($this->short_code) && !empty($this->code)) {
            $this->merge(['short_code' => $this->code]);
        }
    }
}
