<?php

namespace App\Http\Requests\Api\V1\Claims;

use Illuminate\Foundation\Http\FormRequest;

class ClaimReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected'],
            'review_note' => ['required', 'string', 'min:10', 'max:500'], // FR-38 (10-500 chars)
        ];
    }
}
