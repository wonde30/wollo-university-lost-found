<?php

namespace App\Domain\Claims\DTOs;

readonly class CreateClaimData
{
    public function __construct(
        public int $userId,
        public int $itemId,
        public string $claimReason,
        public ?array $verificationAnswers = null
    ) {}
}
