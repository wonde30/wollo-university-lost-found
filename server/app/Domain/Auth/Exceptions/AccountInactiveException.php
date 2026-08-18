<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class AccountInactiveException extends Exception
{
    protected $message = 'Your university account has been deactivated. Please contact campus security/admin.';
}
