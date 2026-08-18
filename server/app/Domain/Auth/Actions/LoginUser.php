<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\LoginData;
use App\Domain\Auth\Services\AuthenticationService;

class LoginUser
{
    public function __construct(protected AuthenticationService $authService)
    {}

    public function execute(LoginData $data): array
    {
        return $this->authService->authenticate($data);
    }
}
