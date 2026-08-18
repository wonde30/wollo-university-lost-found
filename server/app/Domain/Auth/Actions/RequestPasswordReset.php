<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Services\OtpService;
use App\Models\User;

class RequestPasswordReset
{
    public function __construct(protected OtpService $otpService)
    {}

    public function execute(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();
        $this->otpService->generateOtp($user, 'password_reset');
    }
}
