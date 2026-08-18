<?php

namespace App\Support\Enums;

enum AuthVerificationType: string
{
    case EMAIL_VERIFICATION = 'email_verification';
    case PASSWORD_RESET = 'password_reset';
    case ADMIN_PASSWORD_RESET = 'admin_password_reset';
}
