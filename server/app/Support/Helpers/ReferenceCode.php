<?php

namespace App\Support\Helpers;

use Illuminate\Support\Str;

class ReferenceCode
{
    public static function generate(string $prefix = 'WU'): string
    {
        $random = strtoupper(Str::random(8));
        return "{$prefix}-{$random}";
    }
}
