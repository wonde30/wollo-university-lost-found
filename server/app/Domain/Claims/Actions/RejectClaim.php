<?php

namespace App\Domain\Claims\Actions;

use App\Domain\Claims\DTOs\ReviewClaimData;
use App\Models\Claim;
use App\Models\ClaimStatusHistory;
use Illuminate\Support\Facades\DB;

class RejectClaim
{
    public function execute(Claim $claim, ReviewClaimData $data): Claim
    {
        return DB::transaction(function () use ($claim, $data) {
            $prev = (string) $claim->status;
            $claim->update([
                'status' => 'rejected',
                'review_note' => $data->reviewerNotes,
                'reviewed_at' => now(),
                'reviewed_by' => $data->reviewerUserId,
            ]);

            ClaimStatusHistory::create([
                'claim_id' => $claim->id,
                'changed_by' => $data->reviewerUserId,
                'from_status' => $prev,
                'to_status' => 'rejected',
                'changed_by_role' => 'staff',
                'note' => $data->reviewerNotes,
            ]);

            return $claim;
        });
    }
}
