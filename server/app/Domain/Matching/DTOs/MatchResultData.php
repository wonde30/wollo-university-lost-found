<?php

namespace App\Domain\Matching\DTOs;

readonly class MatchResultData
{
    public function __construct(
        public int $lostItemId,
        public int $foundItemId,
        public float $similarityScore,
        public array $matchReasons
    ) {}
}
