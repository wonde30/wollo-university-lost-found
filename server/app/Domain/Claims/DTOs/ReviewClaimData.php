<?php

namespace App\Domain\Claims\DTOs;

readonly class ReviewClaimData
{
    public function __construct(
        public int $reviewerUserId,
        public string $status,
        public ?string $reviewerNotes = null
    ) {}
}
