<?php

namespace App\Support\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case STUDENT = 'student';
}
