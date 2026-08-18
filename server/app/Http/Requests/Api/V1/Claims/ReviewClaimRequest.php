<?php

namespace App\Http\Requests\Api\V1\Claims;

use Illuminate\Foundation\Http\FormRequest;

class ReviewClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected,under_review'],
            'reviewer_notes' => ['nullable', 'string'],
        ];
    }
}
