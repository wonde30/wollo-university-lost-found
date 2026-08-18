<?php

namespace App\Domain\Claims\Exceptions;

use Exception;

class DuplicateClaimException extends Exception
{
    protected $message = 'You have already submitted an active claim for this item.';
}
