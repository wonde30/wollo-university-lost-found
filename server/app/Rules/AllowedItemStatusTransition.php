<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedItemStatusTransition implements ValidationRule
{
    public function __construct(protected ?string $currentStatus = null)
    {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowed = [
            'open' => ['in_storage', 'claim_pending', 'withdrawn', 'expired'],
            'reported' => ['in_storage', 'claim_pending', 'withdrawn', 'expired'],
            'in_storage' => ['claim_pending', 'claim_approved', 'returned', 'disposed', 'withdrawn'],
            'claim_pending' => ['in_storage', 'claim_approved', 'open'],
            'claim_approved' => ['returned', 'in_storage', 'reversed'],
            'returned' => [],
            'expired' => ['open', 'disposed'],
            'withdrawn' => ['open'],
            'disposed' => [],
        ];

        if ($this->currentStatus && isset($allowed[$this->currentStatus])) {
            if (!in_array($value, $allowed[$this->currentStatus])) {
                $fail("Status transition from '{$this->currentStatus}' to '{$value}' is not allowed.");
            }
        }
    }
}
