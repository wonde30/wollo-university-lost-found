<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class AccountLockedException extends Exception
{
    protected $message = 'Account is temporarily locked due to multiple failed login attempts.';
}
