<?php

namespace App\Http\Requests\Api\V1\Items;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoundItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'location_detail' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:60'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'incident_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'held_at' => ['nullable', 'string', 'in:security_office,with_finder,unknown'], // FR-19, FR-20
            'storage_location_id' => ['nullable', 'integer', 'exists:storage_locations,id'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['file', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }
}
