<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048', // 2MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.max' => 'Avatar image must not exceed 2MB.',
            'avatar.mimes' => 'Avatar must be a valid image (jpeg, jpg, png, gif, webp).',
        ];
    }
}
