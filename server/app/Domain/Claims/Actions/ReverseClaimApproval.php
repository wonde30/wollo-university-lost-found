<?php

namespace App\Domain\Claims\Actions;

use App\Models\Claim;
use App\Models\ClaimStatusHistory;
use Illuminate\Support\Facades\DB;

class ReverseClaimApproval
{
    public function execute(Claim $claim, int $officerUserId, string $reason): Claim
    {
        return DB::transaction(function () use ($claim, $officerUserId, $reason) {
            $prev = (string) $claim->status;
            $claim->update([
                'status' => 'rejected',
                'review_note' => $reason,
                'reviewed_by' => $officerUserId,
                'reviewed_at' => now(),
            ]);

            ClaimStatusHistory::create([
                'claim_id' => $claim->id,
                'changed_by' => $officerUserId,
                'from_status' => $prev,
                'to_status' => 'rejected',
                'changed_by_role' => 'admin',
                'note' => $reason,
            ]);

            if ($claim->item) {
                $itemPrev = (string) $claim->item->status;
                $claim->item->update([
                    'status' => 'found_unclaimed',
                    'last_activity_at' => now(),
                ]);
                ItemStatusHistory::create([
                    'item_id' => $claim->item->id,
                    'changed_by' => $officerUserId,
                    'from_status' => $itemPrev,
                    'to_status' => 'found_unclaimed',
                    'changed_by_role' => 'admin',
                    'note' => 'Approval reversed: ' . $reason,
                ]);
            }

            return $claim;
        });
    }
}
