<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Services\OtpService;
use App\Models\User;

class VerifyRegistrationOtp
{
    public function __construct(protected OtpService $otpService)
    {}

    public function execute(string $email, string $code): bool
    {
        $user = User::where('email', $email)->firstOrFail();
        $verified = $this->otpService->verifyOtp($user, $code, 'email_verification');
        if ($verified) {
            $user->update(['email_verified_at' => now()]);
        }
        return $verified;
    }
}
