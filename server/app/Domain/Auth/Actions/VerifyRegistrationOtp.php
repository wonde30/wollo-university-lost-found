<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Services\OtpService;
use App\Models\User;
use App\Support\Enums\AuthVerificationType;

class VerifyRegistrationOtp
{
    public function __construct(protected OtpService $otpService)
    {}

    public function execute(string $email, string $code): bool
    {
        $user = User::where('email', $email)->firstOrFail();
        $verified = $this->otpService->verifyOtp($user, $code, AuthVerificationType::EMAIL_VERIFICATION->value);
        if ($verified) {
            $user->update(['email_verified_at' => now()]);
        }
        return $verified;
    }
}
