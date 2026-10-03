<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\UniversityDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUniversityDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', UniversityDomain::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('domain')) {
            $this->merge([
                'domain' => UniversityDomain::normalizeDomainString((string) $this->input('domain')),
            ]);
        }
        if ($this->has('institution_name')) {
            $this->merge([
                'institution_name' => trim((string) $this->input('institution_name')),
            ]);
        }
    }

    public function rules(): array
    {
        $domainId = $this->route('university_domain') ?? $this->route('id');

        return [
            'domain'           => [
                'sometimes',
                'required',
                'string',
                'max:191',
                Rule::unique('university_domains', 'domain')->ignore($domainId),
                'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/',
            ],
            'institution_name' => ['sometimes', 'required', 'string', 'max:191'],
            'campus_id'        => ['nullable', 'integer', 'exists:campuses,id'],
            'is_active'        => ['sometimes', 'boolean'],
            'description'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
