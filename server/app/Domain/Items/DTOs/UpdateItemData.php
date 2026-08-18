<?php

namespace App\Domain\Items\DTOs;

readonly class UpdateItemData
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?int $categoryId = null,
        public ?int $locationId = null,
        public ?string $locationDetail = null,
        public ?string $incidentDate = null,
        public ?string $incidentTime = null,
        public ?string $brand = null,
        public ?string $color = null,
        public ?string $serialNumber = null,
        public ?float $estimatedValue = null,
        public ?bool $isHighValue = null,
        public ?string $status = null,
        public array $tags = []
    ) {}
}
