<?php

namespace App\Console\Commands;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Models\Item;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class SendItemExpiryWarnings extends Command
{
    protected $signature = 'items:send-expiry-warnings';
    protected $description = 'Send warning notifications to users for items nearing their expiry threshold';

    public function handle(NotificationService $notificationService): int
    {
        $this->info('Sending item expiry warnings...');

        $expiryDays  = (int) SystemSetting::get('item_expiry_days', 90);
        $warningDays = (int) SystemSetting::get('expiry_warning_days', 7);
        $warningDay  = max(1, $expiryDays - $warningDays);

        $nearingExpiry = Item::whereIn('status', ['lost', 'found_unclaimed'])
            ->where('is_deleted', false)
            ->whereBetween('last_activity_at', [
                now()->subDays($warningDay)->startOfDay(),
                now()->subDays($warningDay)->endOfDay(),
            ])
            ->get();

        $count = 0;
        foreach ($nearingExpiry as $item) {
            if (! $item->reporter_id) {
                continue;
            }

            $notificationService->send(new NotificationData(
                userId:  $item->reporter_id,
                type:    'item_expiring',
                payload: [
                    'item_id'        => $item->id,
                    'reference_code' => $item->reference_code,
                    'title'          => $item->title,
                    'days_remaining' => 7,
                    'message'        => "Your reported item \"{$item->title}\" (Ref: {$item->reference_code}) will expire in 7 days if unclaimed.",
                ]
            ));
            $count++;
        }

        $this->info("Sent {$count} item expiry warning notifications.");
        return Command::SUCCESS;
    }
}

