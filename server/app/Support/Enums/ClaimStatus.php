<?php

namespace App\Support\Enums;

enum ClaimStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
