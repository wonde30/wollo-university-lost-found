<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Rules\ValidUniversityEmail;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
        if ($this->has('university_id')) {
            $this->merge([
                'university_id' => trim((string) $this->input('university_id')),
            ]);
        }
        if ($this->has('full_name')) {
            $this->merge([
                'full_name' => trim((string) $this->input('full_name')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'university_id' => [
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'university_id')->where(function ($query) {
                    return $query->where('is_active', true)->orWhereNotNull('email_verified_at');
                }),
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,filter',
                'max:191',
                new ValidUniversityEmail(),
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->where('is_active', true)->orWhereNotNull('email_verified_at');
                }),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'organizational_unit_id' => ['nullable', 'integer', 'exists:organizational_units,id'],
        ];
    }
}
