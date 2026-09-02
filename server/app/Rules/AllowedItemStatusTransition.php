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
        // FR-24: Seven-state lifecycle machine
        // lost → found_unclaimed → claimed → returned → withdrawn / closed / expired
        $allowed = [
            'lost'            => ['found_unclaimed', 'withdrawn', 'closed', 'expired'],
            'found_unclaimed' => ['claimed', 'withdrawn', 'closed', 'expired'],
            'claimed'         => ['returned', 'found_unclaimed', 'closed'],  // found_unclaimed = reversal
            'returned'        => ['closed'],
            'withdrawn'       => ['lost', 'found_unclaimed'],                // admin reopen
            'closed'          => ['lost', 'found_unclaimed'],                // admin reopen
            'expired'         => ['lost', 'found_unclaimed'],                // admin reopen
        ];

        if ($this->currentStatus && isset($allowed[$this->currentStatus])) {
            if (!in_array($value, $allowed[$this->currentStatus])) {
                $fail("Status transition from '{$this->currentStatus}' to '{$value}' is not allowed.");
            }
        }
    }
}
