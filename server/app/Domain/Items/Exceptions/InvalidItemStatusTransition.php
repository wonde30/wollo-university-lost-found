<?php

namespace App\Domain\Items\Exceptions;

use Exception;

class InvalidItemStatusTransition extends Exception
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Cannot change item status from '{$from}' to '{$to}'.");
    }
}
