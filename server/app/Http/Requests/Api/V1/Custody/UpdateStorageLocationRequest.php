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
        return [
            'campus_id' => ['sometimes', 'required', 'integer', 'exists:campuses,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'shelf_cabinet_code' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'current_occupancy' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:active,full,maintenance'],
        ];
    }
}
