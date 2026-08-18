<?php

namespace App\Support\Enums;

enum ItemStatus: string
{
    case LOST = 'lost';
    case FOUND_UNCLAIMED = 'found_unclaimed';
    case CLAIMED = 'claimed';
    case RETURNED = 'returned';
    case WITHDRAWN = 'withdrawn';
    case CLOSED = 'closed';
    case EXPIRED = 'expired';
}
