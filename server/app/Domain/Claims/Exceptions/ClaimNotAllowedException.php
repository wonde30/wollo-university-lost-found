<?php

namespace App\Domain\Claims\Exceptions;

use Exception;

class ClaimNotAllowedException extends Exception
{
    protected $message = 'Claim cannot be created for this item in its current status.';
}
