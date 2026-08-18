<?php

namespace App\Http\Requests\Api\V1\Custody;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustodyEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'storage_location_id' => ['nullable', 'integer', 'exists:storage_locations,id'],
            'event_type' => ['required', 'string', 'in:checked_in,moved,checked_out,transferred,audited'],
            'notes' => ['nullable', 'string'],
            'custody_proof_url' => ['nullable', 'string'],
        ];
    }
}
