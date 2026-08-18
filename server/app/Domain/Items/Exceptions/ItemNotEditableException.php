<?php

namespace App\Domain\Items\Exceptions;

use Exception;

class ItemNotEditableException extends Exception
{
    protected $message = 'This item has already been claimed or returned and cannot be edited.';
}
