<?php

namespace App\Domain\Custody\DTOs;

readonly class CustodyEventData
{
    public function __construct(
        public int $itemId,
        public int $performedByUserId,
        public string $eventType,
        public ?int $storageLocationId = null,
        public ?string $notes = null,
        public ?string $custodyProofUrl = null
    ) {}
}
