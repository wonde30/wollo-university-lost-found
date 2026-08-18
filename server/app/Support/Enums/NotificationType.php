<?php

namespace App\Support\Enums;

enum NotificationType: string
{
    case ITEM_MATCH = 'item_match';
    case CLAIM_SUBMITTED = 'claim_submitted';
    case CLAIM_APPROVED = 'claim_approved';
    case CLAIM_REJECTED = 'claim_rejected';
    case ITEM_EXPIRING = 'item_expiring';
    case ITEM_EXPIRED = 'item_expired';
    case ITEM_RETURNED = 'item_returned';
    case ANNOUNCEMENT = 'announcement';
}
