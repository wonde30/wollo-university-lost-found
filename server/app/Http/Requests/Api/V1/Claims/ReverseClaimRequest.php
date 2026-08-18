<?php

namespace App\Http\Requests\Api\V1\Claims;

use Illuminate\Foundation\Http\FormRequest;

class ReverseClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5'],
        ];
    }
}
