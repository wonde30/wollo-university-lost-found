<?php

namespace App\Console\Commands;

use App\Domain\Items\Actions\ChangeItemStatus;
use App\Jobs\SendExpiryNotification;
use App\Models\Item;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class ExpireInactiveItems extends Command
{
    protected $signature = 'items:expire-inactive';
    protected $description = 'Expire inactive items that have exceeded the university listing threshold';

    public function handle(ChangeItemStatus $statusAction): int
    {
        $this->info('Checking for expired items...');

        $expiryDays = (int) SystemSetting::get('item_expiry_days', 90);

        $expiredItems = Item::whereIn('status', ['lost', 'found_unclaimed'])
            ->where('is_deleted', false)
            ->where('last_activity_at', '<', now()->subDays($expiryDays)) // FR-25: use last_activity_at
            ->get();

        $count = 0;
        foreach ($expiredItems as $item) {
            $statusAction->execute(
                $item,
                'expired',
                "Item automatically expired after {$expiryDays} days of inactivity."
            );

            // Notify the reporter their item has expired
            SendExpiryNotification::dispatch($item);

            $this->info("Expired item [{$item->reference_code}]: {$item->title}");
            $count++;
        }

        $this->info("Successfully processed {$count} expired items.");
        return Command::SUCCESS;
    }
}

