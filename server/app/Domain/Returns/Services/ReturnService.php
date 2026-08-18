<?php

namespace App\Domain\Returns\Services;

class ReturnService
{
    public function generateReference(): string
    {
        return 'RET-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
}
