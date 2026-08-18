<?php

namespace App\Domain\Administration\DTOs;

readonly class ReportFilterData
{
    public function __construct(
        public string $title,
        public string $reportType,
        public ?string $format = 'pdf',
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?int $campusId = null,
        public ?int $categoryId = null
    ) {}
}
