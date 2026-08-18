<?php

namespace App\Support\Enums;

enum AuditAction: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case STATUS_CHANGE = 'status_change';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case CUSTODY_TRANSFER = 'custody_transfer';
}
