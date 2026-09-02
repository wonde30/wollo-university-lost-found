<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Mail\Expiry\ItemExpiryWarningMail;
use App\Models\Item;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendExpiryWarning implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Item $item,
        public readonly int $daysRemaining = 5
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $item = $this->item->loadMissing(['reporter']);

        if (! $item->reporter_id) {
            return;
        }

        // Database notification
        $notificationService->send(new NotificationData(
            userId:  $item->reporter_id,
            type:    'item_expiring',
            payload: [
                'item_id'        => $item->id,
                'reference_code' => $item->reference_code,
                'title'          => $item->title,
                'days_remaining' => $this->daysRemaining,
                'message'        => "Your reported item \"{$item->title}\" (Ref: {$item->reference_code}) will expire in {$this->daysRemaining} days if unclaimed.",
            ]
        ));

        // Email notification if user has an email and preference is enabled
        $reporter = $item->reporter ?? \App\Models\User::with('notificationPreference')->find($item->reporter_id);
        $emailEnabled = $reporter?->notificationPreference ? (bool) $reporter->notificationPreference->email_on_expiry_warning : true;

        if ($reporter && $reporter->email && $emailEnabled) {
            Mail::to($reporter->email)->send(new ItemExpiryWarningMail($item, $this->daysRemaining));
        }
    }
}
