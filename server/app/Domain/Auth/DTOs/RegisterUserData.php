<?php

namespace App\Domain\Auth\DTOs;

readonly class RegisterUserData
{
    public function __construct(
        public string $fullName,
        public string $universityId,
        public string $email,
        public string $password,
        public ?string $phone = null,
        public ?int $departmentId = null
    ) {}
}
