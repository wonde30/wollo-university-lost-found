<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CustodyTransferred;
use App\Jobs\ProcessCustodyTransfer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleCustodyTransferred implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CustodyTransferred $event): void
    {
        // Dispatch the job to process the custody transfer asynchronously
        ProcessCustodyTransfer::dispatch($event->payload);
    }
}
