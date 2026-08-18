<?php

namespace App\Http\Requests\Api\V1\Returns;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'claim_id' => ['required', 'integer', 'exists:claims,id', 'unique:returns,claim_id'],
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'returned_to' => ['required', 'integer', 'exists:users,id'],
            'storage_location_id' => ['nullable', 'integer', 'exists:storage_locations,id'],
            'return_date' => ['required', 'date'],
            'return_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'condition_on_return' => ['required', 'string', 'in:good,damaged,incomplete'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
