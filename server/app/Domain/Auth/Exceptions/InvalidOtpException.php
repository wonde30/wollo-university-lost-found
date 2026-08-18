<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class InvalidOtpException extends Exception
{
    protected $message = 'The OTP code entered is invalid or has expired.';
}
