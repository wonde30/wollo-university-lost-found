<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ClaimApproved;
use App\Jobs\SendClaimDecisionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyClaimApproved implements ShouldQueue
{
    public function handle(ClaimApproved $event): void
    {
        SendClaimDecisionNotification::dispatch($event->payload, 'approved');
    }
}
