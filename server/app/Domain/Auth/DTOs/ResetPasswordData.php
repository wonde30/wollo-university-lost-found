<?php

namespace App\Domain\Auth\DTOs;

readonly class ResetPasswordData
{
    public function __construct(
        public string $email,
        public string $otp,
        public string $newPassword
    ) {}
}
