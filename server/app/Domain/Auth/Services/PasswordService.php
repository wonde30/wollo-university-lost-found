<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\DTOs\ResetPasswordData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordService
{
    public function __construct(protected OtpService $otpService)
    {}

    public function resetPassword(ResetPasswordData $data): bool
    {
        $user = User::where('email', $data->email)->firstOrFail();
        $this->otpService->verifyOtp($user, $data->otp, 'password_reset');

        $user->update([
            'password' => Hash::make($data->newPassword),
        ]);

        return true;
    }
}
