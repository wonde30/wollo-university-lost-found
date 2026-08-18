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
        $expiryDays = $expiryDaysSetting ? (int) $expiryDaysSetting : 30;
        $warningDay = max(1, $expiryDays - 5);

        $nearingExpiry = Item::with('user')
            ->whereIn('status', ['open', 'in_storage'])
            ->whereBetween('created_at', [
                now()->subDays($warningDay)->startOfDay(),
                now()->subDays($warningDay)->endOfDay()
            ])
            ->get();

        $count = 0;
        foreach ($nearingExpiry as $item) {
            $notificationService->send(new NotificationData(
                userId: $item->user_id,
                type: 'item_expiring',
                payload: [
                    'item_id' => $item->id,
                    'reference_code' => $item->reference_code,
                    'title' => $item->title,
                    'days_remaining' => 5,
                    'message' => "Your reported item '{$item->title}' will expire in 5 days if unclaimed.",
                ]
            ));
            $count++;
        }

        $this->info("Sent {$count} item expiry warning notifications.");
        return Command::SUCCESS;
    }
}
