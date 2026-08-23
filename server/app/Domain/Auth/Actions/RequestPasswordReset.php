<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Services\OtpService;
use App\Jobs\SendPasswordResetOtp;
use App\Models\User;

class RequestPasswordReset
{
    public function __construct(protected OtpService $otpService)
    {}

    public function execute(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();
        $verification = $this->otpService->generateOtp($user, 'password_reset');

        SendPasswordResetOtp::dispatch($user, $verification->code);
    }
}
