<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidReferenceCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^[A-Z]{3}-\d{8}-[A-Z0-9]{5}$/', (string)$value)) {
            $fail('The :attribute does not match the valid reference code format (e.g. LST-20260814-ABCDE).');
        }
    }
}
