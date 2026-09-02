<?php

namespace App\Jobs;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Models\Claim;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Notify the claimant that their claim was received and is under review.
 * Also notifies the item reporter (found item owner) that a new claim arrived.
 */
class SendClaimSubmittedNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Claim $claim)
    {}

    public function handle(NotificationService $notificationService): void
    {
        $claim = $this->claim->loadMissing(['item', 'claimant']);
        $item  = $claim->item;

        // 1. Notify the claimant — confirm submission
        $notificationService->send(new NotificationData(
            userId:  $claim->claimant_id,
            type:    'claim_submitted',
            payload: [
                'claim_id'       => $claim->id,
                'item_id'        => $item->id,
                'reference_code' => $item->reference_code,
                'title'          => $item->title,
                'message'        => "Your claim for \"{$item->title}\" (Ref: {$item->reference_code}) has been submitted and is under review.",
            ]
        ));

        // Email notification to claimant if enabled
        $claimant = $claim->claimant ?? \App\Models\User::with('notificationPreference')->find($claim->claimant_id);
        $emailEnabled = $claimant?->notificationPreference ? (bool) $claimant->notificationPreference->email_on_claim_received : true;

        if ($claimant && $claimant->email && $emailEnabled) {
            \Illuminate\Support\Facades\Mail::to($claimant->email)->send(new \App\Mail\Claims\ClaimSubmittedMail($claim));
        }

        // 2. Notify the item reporter (finder) that someone claimed their report
        if ($item->reporter_id && $item->reporter_id !== $claim->claimant_id) {
            $notificationService->send(new NotificationData(
                userId:  $item->reporter_id,
                type:    'claim_submitted',
                payload: [
                    'claim_id'       => $claim->id,
                    'item_id'        => $item->id,
                    'reference_code' => $item->reference_code,
                    'title'          => $item->title,
                    'message'        => "A new claim has been submitted for your reported item \"{$item->title}\" (Ref: {$item->reference_code}).",
                ]
            ));
        }

        // 3. Notify Staff and Custodians about the incoming claim for review
        $staffUsers = \App\Models\User::whereHas('role', fn ($q) => $q->whereIn('name', ['staff', 'admin']))->get();
        foreach ($staffUsers as $staff) {
            if ($staff->id !== $claim->claimant_id) {
                $notificationService->send(new NotificationData(
                    userId:  $staff->id,
                    type:    'claim_submitted',
                    payload: [
                        'claim_id'       => $claim->id,
                        'item_id'        => $item->id,
                        'reference_code' => $item->reference_code,
                        'title'          => $item->title,
                        'message'        => "New ownership claim submitted for item \"{$item->title}\" (Ref: {$item->reference_code}).",
                    ]
                ));
            }
        }
    }
}
