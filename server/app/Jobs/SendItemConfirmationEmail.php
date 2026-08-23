<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\Items\ItemSubmittedMail;
use App\Models\Item;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendItemConfirmationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Item $item)
    {}

    public function handle(): void
    {
        $item = $this->item->loadMissing(['reporter']);

        if ($item->reporter && $item->reporter->email) {
            Mail::to($item->reporter->email)->send(new ItemSubmittedMail($item));
        }
    }
}
