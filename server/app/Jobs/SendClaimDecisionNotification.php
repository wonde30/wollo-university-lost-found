<?php

namespace App\Jobs;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Models\Claim;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Notify the claimant about the staff decision (approved or rejected) on their claim.
 */
class SendClaimDecisionNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Claim  $claim,
        public readonly string $decision  // 'approved' | 'rejected'
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $claim = $this->claim->loadMissing(['item']);
        $item  = $claim->item;

        $type = $this->decision === 'approved' ? 'claim_approved' : 'claim_rejected';

        $notificationService->send(new NotificationData(
            userId:  $claim->claimant_id,
            type:    $type,
            payload: [
                'claim_id'       => $claim->id,
                'item_id'        => $item->id,
                'reference_code' => $item->reference_code,
                'title'          => $item->title,
                'decision'       => $this->decision,
                'review_note'    => $claim->review_note,
            ]
        ));

        // Email notification if claimant has email and preference is enabled
        $claimant = $claim->claimant ?? \App\Models\User::with('notificationPreference')->find($claim->claimant_id);
        $emailEnabled = $claimant?->notificationPreference ? (bool) $claimant->notificationPreference->email_on_claim_decided : true;

        if ($claimant && $claimant->email && $emailEnabled) {
            $mailable = $this->decision === 'approved'
                ? new \App\Mail\Claims\ClaimApprovedMail($claim)
                : new \App\Mail\Claims\ClaimRejectedMail($claim);
            \Illuminate\Support\Facades\Mail::to($claimant->email)->send($mailable);
        }
    }
}
