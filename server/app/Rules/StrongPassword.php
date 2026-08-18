<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || strlen($value) < 12) {
            $fail('The :attribute must be at least 12 characters long.');
            return;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('The :attribute must contain at least one lowercase letter.');
        }

        if (!preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one numeric digit.');
        }

        if (!preg_match('/[\W_]/', $value)) {
            $fail('The :attribute must contain at least one special symbol.');
        }
    }
}
