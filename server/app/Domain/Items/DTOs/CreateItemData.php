<?php

namespace App\Domain\Items\DTOs;

readonly class CreateItemData
{
    public function __construct(
        public int $userId,
        public int $categoryId,
        public ?int $locationId,
        public string $type,
        public string $title,
        public string $description,
        public string $incidentDate,
        public ?string $incidentTime = null,
        public ?int $campusId = 1,
        public ?string $locationDetail = null,
        public ?string $brand = null,
        public ?string $color = null,
        public ?string $serialNumber = null,
        public ?float $estimatedValue = null,
        public bool $isHighValue = false,
        public ?string $heldAt = null,
        public ?int $storageLocationId = null,
        public array $tags = []
    ) {}
}
