<?php

namespace App\Jobs;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Models\Item;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Notify the item reporter that their item has expired (removed from active listings).
 */
class SendExpiryNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Item $item)
    {}

    public function handle(NotificationService $notificationService): void
    {
        $item = $this->item;

        if (! $item->reporter_id) {
            return;
        }

        $notificationService->send(new NotificationData(
            userId:  $item->reporter_id,
            type:    'item_expired',
            payload: [
                'item_id'        => $item->id,
                'reference_code' => $item->reference_code,
                'title'          => $item->title,
                'message'        => "Your reported item \"{$item->title}\" (Ref: {$item->reference_code}) has expired and is no longer listed. Please contact the security office if it is still relevant.",
            ]
        ));

        $reporter = $item->reporter ?? \App\Models\User::find($item->reporter_id);
        if ($reporter && $reporter->email) {
            \Illuminate\Support\Facades\Mail::to($reporter->email)->send(new \App\Mail\Expiry\ItemExpiredMail($item));
        }
    }
}
