<?php

namespace App\Domain\Items\Services;

class ItemReferenceCodeService
{
    public function generate(string $type = 'lost'): string
    {
        $prefix = strtoupper($type === 'found' ? 'FND' : 'LST');
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
}
