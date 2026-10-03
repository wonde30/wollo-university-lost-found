<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locationId = $this->route('location');
        
        return [
            'campus_id' => ['sometimes', 'required', 'integer', 'exists:campuses,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'name_am' => ['nullable', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50', 'unique:locations,code,' . $locationId],
            'building' => ['nullable', 'string', 'max:255'],
            'zone' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
