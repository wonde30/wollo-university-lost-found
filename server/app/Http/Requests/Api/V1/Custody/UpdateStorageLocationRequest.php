<?php

namespace App\Http\Requests\Api\V1\Custody;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorageLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('storage_location') ?? $this->route('id');

        return [
            'campus_id' => ['sometimes', 'required', 'integer', 'exists:campuses,id'],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:storage_locations,code,' . $id],
            'shelf_cabinet_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function passedValidation(): void
    {
        if (empty($this->code) && !empty($this->shelf_cabinet_code)) {
            $this->merge(['code' => $this->shelf_cabinet_code]);
        }
    }
}
