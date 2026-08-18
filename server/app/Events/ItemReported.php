<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemReported
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $payload = null)
    {}
}
