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

        $reporter = $item->reporter ?? \App\Models\User::with('notificationPreference')->find($item->reporter_id);
        $emailEnabled = $reporter?->notificationPreference ? (bool) $reporter->notificationPreference->email_on_report_submitted : true;

        if ($reporter && $reporter->email && $emailEnabled) {
            Mail::to($reporter->email)->send(new ItemSubmittedMail($item));
        }
    }
}
