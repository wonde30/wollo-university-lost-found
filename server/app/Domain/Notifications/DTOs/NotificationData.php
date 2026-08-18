<?php

namespace App\Domain\Notifications\DTOs;

readonly class NotificationData
{
    public function __construct(
        public int $userId,
        public string $type,
        public array $payload
    ) {}
}
