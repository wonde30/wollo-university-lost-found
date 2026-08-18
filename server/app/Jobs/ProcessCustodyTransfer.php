<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Item;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCustodyTransfer implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * The payload from the CustodyTransferred event.
     *
     * @var array
     */
    public $payload;

    /**
     * Create a new job instance.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $itemId = $this->payload['item_id'] ?? null;
        $fromUserId = $this->payload['from_user_id'] ?? null;
        $toUserId = $this->payload['to_user_id'] ?? null;

        if (!$itemId || !$fromUserId || !$toUserId) {
            Log::warning('ProcessCustodyTransfer: Incomplete payload', $this->payload);
            return;
        }

        $item = Item::find($itemId);
        $fromUser = User::find($fromUserId);
        $toUser = User::find($toUserId);

        if (!$item || !$fromUser || !$toUser) {
            Log::warning('ProcessCustodyTransfer: Model not found', $this->payload);
            return;
        }

        // Transfer ownership
        $item->owner_id = $toUser->id;
        $item->save();

        // Optionally record custody event if relationship exists
        if (method_exists($item, 'custodyEvents')) {
            $item->custodyEvents()->create([
                'from_user_id' => $fromUser->id,
                'to_user_id'   => $toUser->id,
                'notes'        => $this->payload['notes'] ?? null,
            ]);
        }

        Log::info('Custody transfer processed', $this->payload);
    }
}
