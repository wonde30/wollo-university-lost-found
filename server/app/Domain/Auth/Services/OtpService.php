<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Exceptions\InvalidOtpException;
use App\Models\AuthVerification;
use App\Models\User;

class OtpService
{
    public function generateOtp(User $user, string $type = 'email_verification', int $expiryMinutes = 15): AuthVerification
    {
        $code = (string) rand(100000, 999999);

        return AuthVerification::create([
            'user_id' => $user->id,
            'type' => $type,
            'code' => $code,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }

    public function verifyOtp(User $user, string $code, string $type = 'email_verification'): bool
    {
        $verification = AuthVerification::where('user_id', $user->id)
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();

        if (!$verification) {
            throw new InvalidOtpException();
        }

        $verification->update(['verified_at' => now()]);
        return true;
    }
}
