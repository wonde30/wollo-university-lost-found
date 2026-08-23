<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ClaimSubmitted;
use App\Jobs\SendClaimSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyClaimSubmitted implements ShouldQueue
{
    public function handle(ClaimSubmitted $event): void
    {
        SendClaimSubmittedNotification::dispatch($event->payload);
    }
}
