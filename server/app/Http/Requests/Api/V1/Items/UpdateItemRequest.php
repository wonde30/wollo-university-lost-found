<?php

namespace App\Http\Requests\Api\V1\Items;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => ['sometimes', 'required', 'string', 'max:255'],
            'description'     => ['sometimes', 'required', 'string'],
            'category_id'     => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'location_id'     => ['sometimes', 'nullable', 'integer', 'exists:locations,id'],
            'location_detail' => ['nullable', 'string', 'max:500'],
            'incident_date'   => ['sometimes', 'required', 'date'],
            'incident_time'   => ['nullable', 'string', 'max:10'],
            'color'           => ['nullable', 'string', 'max:50'],
            'brand'           => ['nullable', 'string', 'max:100'],
            'serial_number'   => ['nullable', 'string', 'max:100'],
            'held_at'         => ['nullable', 'string', 'max:100'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'is_high_value'   => ['nullable', 'boolean'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['string', 'max:50'],
            // Note: 'status' is intentionally excluded.
            // Status changes must go through PATCH /items/{id}/status
            // which enforces role-based authorization via ItemPolicy::changeStatus().
        ];
    }
}

