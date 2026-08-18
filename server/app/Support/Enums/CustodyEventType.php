<?php

namespace App\Support\Enums;

enum CustodyEventType: string
{
    case DEPOSITED = 'deposited';
    case TRANSFERRED = 'transferred';
    case INSPECTED = 'inspected';
    case RELEASED = 'released';
    case DISPOSED = 'disposed';
}
