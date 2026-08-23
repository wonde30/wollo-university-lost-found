<?php

namespace App\Jobs;

use App\Domain\Notifications\DTOs\NotificationData;
use App\Domain\Notifications\Services\NotificationService;
use App\Models\MatchSuggestion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Notify the reporter of the lost item that a potential match (found item) was identified.
 */
class SendMatchNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MatchSuggestion $match)
    {}

    public function handle(NotificationService $notificationService): void
    {
        $match     = $this->match->loadMissing(['lostItem', 'foundItem']);
        $lostItem  = $match->lostItem;
        $foundItem = $match->foundItem;

        if (! $lostItem || ! $lostItem->reporter_id) {
            return;
        }

        $score   = round((float) $match->score * 100);
        $message = "A potential match ({$score}% similarity) was found for your lost item \"{$lostItem->title}\" (Ref: {$lostItem->reference_code}). "
                 . "Found item: \"{$foundItem->title}\" (Ref: {$foundItem->reference_code}).";

        $notificationService->send(new NotificationData(
            userId:  $lostItem->reporter_id,
            type:    'item_match',
            payload: [
                'match_id'             => $match->id,
                'lost_item_id'         => $lostItem->id,
                'found_item_id'        => $foundItem->id,
                'lost_reference_code'  => $lostItem->reference_code,
                'found_reference_code' => $foundItem->reference_code,
                'score'                => $match->score,
                'message'              => $message,
            ]
        ));

        // Email notification if reporter has email
        $reporter = $lostItem->reporter ?? \App\Models\User::find($lostItem->reporter_id);
        if ($reporter && $reporter->email) {
            \Illuminate\Support\Facades\Mail::to($reporter->email)->send(new \App\Mail\Matching\MatchSuggestionMail($match));
        }

        // Mark match as notified
        $match->update(['notified_at' => now()]);
    }
}
