<?php

namespace App\Http\Requests\Api\V1\Claims;

use Illuminate\Foundation\Http\FormRequest;

class UploadClaimEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'file_type' => ['required', 'string', 'in:receipt,photo,id_card,serial_number,student_id,other_document'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
