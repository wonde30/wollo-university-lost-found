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
                'confirmation_token' => $returnRecord->confirmation_token,
                'confirmation_url'   => config('app.frontend_url') . '/confirm-return/' . $returnRecord->confirmation_token,
                'message'        => "Your item \"{$item->title}\" (Ref: {$item->reference_code}) has been handed over to you. Please confirm receipt.",
            ]
        ));

        // Email notification if claimant has email and preference is enabled
        $claimant = $claim->claimant ?? \App\Models\User::with('notificationPreference')->find($claim->claimant_id);
        $emailEnabled = $claimant?->notificationPreference ? (bool) $claimant->notificationPreference->email_on_item_returned : true;

        if ($claimant && $claimant->email && $emailEnabled) {
            \Illuminate\Support\Facades\Mail::to($claimant->email)->send(new \App\Mail\Returns\ItemReturnedMail($returnRecord));
        }
    }
}
