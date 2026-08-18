<?php

namespace App\Domain\Claims\Exceptions;

use Exception;

class ClaimAlreadyReviewedException extends Exception
{
    protected $message = 'This claim has already been approved or rejected.';
}
