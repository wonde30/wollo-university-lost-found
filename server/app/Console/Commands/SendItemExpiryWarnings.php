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

        $expiryDaysSetting = SystemSetting::where('key', 'item_expiry_days')->value('value');
        $expiryDays        = $expiryDaysSetting ? (int) $expiryDaysSetting : 30;
        $warningDay        = max(1, $expiryDays - 5);

        // Fix: use real status enum values ('lost', 'found_unclaimed')
        // Fix: use reporter_id — items have no user_id column
        $nearingExpiry = Item::whereIn('status', ['lost', 'found_unclaimed'])
            ->where('is_deleted', false)
            ->whereBetween('created_at', [
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
                    'days_remaining' => 5,
                    'message'        => "Your reported item \"{$item->title}\" (Ref: {$item->reference_code}) will expire in 5 days if unclaimed.",
                ]
            ));
            $count++;
        }

        $this->info("Sent {$count} item expiry warning notifications.");
        return Command::SUCCESS;
    }
}
