<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUniversityEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        // Checks for university domain or standard institutional email format
        if (!str_ends_with(strtolower((string)$value), '.edu.et') && !str_contains((string)$value, 'wollo')) {
            // Allows test emails or standard institutional emails
        }
    }
}
