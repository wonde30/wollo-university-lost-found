<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\UniversityDomain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUniversityEmail implements ValidationRule
{
    /**
     * Common public consumer email providers to reject with dedicated guidance.
     */
    protected const PUBLIC_PROVIDERS = [
        'gmail.com',
        'yahoo.com',
        'ymail.com',
        'outlook.com',
        'hotmail.com',
        'live.com',
        'msn.com',
        'icloud.com',
        'me.com',
        'mac.com',
        'proton.me',
        'protonmail.com',
        'mail.com',
        'zoho.com',
        'aol.com',
        'gmx.com',
        'gmx.net',
        'fastmail.com',
        'tutanota.com',
        'tutamail.com',
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail(__('validation.required', ['attribute' => $attribute]));
            return;
        }

        $email = trim($value);

        // 1. Validate standard RFC email structure
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191) {
            $fail(__('validation.email', ['attribute' => $attribute]));
            return;
        }

        // 2. Ensure exactly one '@' symbol
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            $fail(__('validation.email', ['attribute' => $attribute]));
            return;
        }

        [$localPart, $rawDomain] = $parts;

        if (trim($localPart) === '' || trim($rawDomain) === '') {
            $fail(__('validation.email', ['attribute' => $attribute]));
            return;
        }

        // 3. Normalize domain (lowercase, trimmed, strip trailing dots)
        $domain = UniversityDomain::normalizeDomainString($rawDomain);

        if ($domain === '') {
            $fail(__('validation.email', ['attribute' => $attribute]));
            return;
        }

        // 4. Reject consumer public email providers
        if (in_array($domain, static::PUBLIC_PROVIDERS, true)) {
            $fail(__('auth.public_email_rejected'));
            return;
        }

        // 5. Authoritative check: Domain MUST exactly match an active configured university domain
        $activeDomains = UniversityDomain::getActiveDomains();

        if (! in_array($domain, $activeDomains, true)) {
            $fail(__('auth.invalid_university_domain', ['domain' => $domain]));
            return;
        }
    }
}
