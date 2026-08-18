<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidImageMimeType implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (is_object($value) && method_exists($value, 'getMimeType')) {
            if (!in_array($value->getMimeType(), $allowed)) {
                $fail('The :attribute must be a valid image file (jpeg, png, webp).');
            }
        }
    }
}
