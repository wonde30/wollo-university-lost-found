<?php

namespace App\Http\Requests\Api\V1\Items;

use Illuminate\Foundation\Http\FormRequest;

class StoreLostItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('REPORT_LOST') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'], // FR-14 (5-150 chars)
            'description' => ['required', 'string', 'min:20', 'max:2000'], // FR-14 (20-2000 chars)
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'location_detail' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:60'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'], // FR-14 (not future)
            'incident_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'is_high_value' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'array', 'max:3'], // FR-14 (up to 3 photos)
            'photos.*' => ['file', 'mimes:jpeg,png,webp', 'max:5120'], // FR-14 (max 5 MB each)
        ];
    }
}
