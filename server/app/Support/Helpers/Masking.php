<?php

namespace App\Support\Helpers;

class Masking
{
    public static function maskEmail(?string $email): string
    {
        if (empty($email)) {
            return '';
        }
        $parts = explode('@', $email);
        $name = $parts[0];
        $len = strlen($name);
        $visible = min(2, $len);
        $maskedName = substr($name, 0, $visible) . str_repeat('*', max(1, $len - $visible));
        return $maskedName . '@' . ($parts[1] ?? '');
    }

    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        $len = strlen($phone);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return substr($phone, 0, 3) . str_repeat('*', max(1, $len - 6)) . substr($phone, -3);
    }
}
