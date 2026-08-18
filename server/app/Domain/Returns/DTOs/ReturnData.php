<?php

namespace App\Domain\Returns\DTOs;

readonly class ReturnData
{
    public function __construct(
        public int $itemId,
        public int $userId,
        public int $processedByUserId,
        public string $verificationMethod,
        public ?int $claimId = null,
        public ?string $notes = null,
        public ?string $signaturePath = null
    ) {}
}
