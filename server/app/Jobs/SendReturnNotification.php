<?php

namespace App\Jobs;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Models\ReturnRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Notify the claimant (item owner) that the physical handover was recorded.
 */
class SendReturnNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ReturnRecord $returnRecord)
    {}

    public function handle(NotificationService $notificationService): void
    {
        $returnRecord = $this->returnRecord->loadMissing(['item', 'claim']);
        $item         = $returnRecord->item;
        $claim        = $returnRecord->claim;

        if (! $claim) {
            return;
        }

        $notificationService->send(new NotificationData(
            userId:  $claim->claimant_id,
            type:    'item_returned',
            payload: [
                'return_id'      => $returnRecord->id,
                'claim_id'       => $claim->id,
                'item_id'        => $item->id,
                'reference_code' => $item->reference_code,
                'title'          => $item->title,
                'return_date'    => $returnRecord->return_date,
                'message'        => "Your item \"{$item->title}\" (Ref: {$item->reference_code}) has been successfully returned to you. Return recorded on {$returnRecord->return_date}.",
            ]
        ));

        $claimant = $claim->claimant ?? \App\Models\User::find($claim->claimant_id);
        if ($claimant && $claimant->email) {
            \Illuminate\Support\Facades\Mail::to($claimant->email)->send(new \App\Mail\Returns\ItemReturnedMail($returnRecord));
        }
    }
}
