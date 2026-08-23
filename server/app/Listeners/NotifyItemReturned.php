<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ItemReturned;
use App\Jobs\SendReturnNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyItemReturned implements ShouldQueue
{
    public function handle(ItemReturned $event): void
    {
        SendReturnNotification::dispatch($event->payload);
    }
}
