<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\ResetPasswordData;
use App\Domain\Auth\Services\PasswordService;

class ResetPassword
{
    public function __construct(protected PasswordService $passwordService)
    {}

    public function execute(ResetPasswordData $data): bool
    {
        return $this->passwordService->resetPassword($data);
    }
}
