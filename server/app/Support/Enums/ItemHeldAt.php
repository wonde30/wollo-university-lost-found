<?php

namespace App\Support\Enums;

enum ItemHeldAt: string
{
    case SECURITY_OFFICE = 'security_office';
    case WITH_FINDER = 'with_finder';
    case UNKNOWN = 'unknown';
}
