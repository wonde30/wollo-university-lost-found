<?php

namespace App\Domain\Claims\Actions;

use App\Domain\Claims\DTOs\ReviewClaimData;
use App\Models\Claim;
use App\Models\ClaimStatusHistory;
use App\Models\ItemStatusHistory;
use Illuminate\Support\Facades\DB;

class ApproveClaim
{
    public function execute(Claim $claim, ReviewClaimData $data): Claim
    {
        return DB::transaction(function () use ($claim, $data) {
            $prev = (string) $claim->status;
            $claim->update([
                'status' => 'approved',
                'review_note' => $data->reviewerNotes,
                'reviewed_at' => now(),
                'reviewed_by' => $data->reviewerUserId,
            ]);

            ClaimStatusHistory::create([
                'claim_id' => $claim->id,
                'changed_by' => $data->reviewerUserId,
                'from_status' => $prev,
                'to_status' => 'approved',
                'changed_by_role' => 'staff',
                'note' => $data->reviewerNotes,
            ]);

            if ($claim->item) {
                $itemPrev = (string) $claim->item->status;
                $claim->item->update([
                    'status' => 'claimed',
                    'last_activity_at' => now(),
                ]);
                ItemStatusHistory::create([
                    'item_id' => $claim->item->id,
                    'changed_by' => $data->reviewerUserId,
                    'from_status' => $itemPrev,
                    'to_status' => 'claimed',
                    'changed_by_role' => 'staff',
                    'note' => 'Claim #' . $claim->id . ' approved',
                ]);
            }

            return $claim;
        });
    }
}
