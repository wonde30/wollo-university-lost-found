<?php

namespace App\Http\Requests\Api\V1\Claims;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('SUBMIT_CLAIM') ?? false;
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'explanation' => ['required', 'string', 'min:50', 'max:1000'], // FR-34 (50-1000 chars)
            'evidence' => ['nullable', 'array'],
            'evidence.*' => ['file', 'mimes:jpeg,png,webp,pdf', 'max:5120'], // FR-35 (max 5 MB)
        ];
    }
}
