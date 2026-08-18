<?php

namespace App\Http\Requests\Api\V1\Custody;

use Illuminate\Foundation\Http\FormRequest;

class MoveStorageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'storage_location_id' => ['required', 'integer', 'exists:storage_locations,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
