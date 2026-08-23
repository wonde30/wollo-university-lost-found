<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ClaimRejected;
use App\Jobs\SendClaimDecisionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyClaimRejected implements ShouldQueue
{
    public function handle(ClaimRejected $event): void
    {
        SendClaimDecisionNotification::dispatch($event->payload, 'rejected');
    }
}
