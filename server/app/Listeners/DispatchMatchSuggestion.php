<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ItemReported;
use App\Jobs\GenerateMatchSuggestions;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchMatchSuggestion implements ShouldQueue
{
    public function handle(ItemReported $event): void
    {
        GenerateMatchSuggestions::dispatch($event->payload);
    }
}
