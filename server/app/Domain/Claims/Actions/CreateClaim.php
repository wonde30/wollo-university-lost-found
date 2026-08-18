<?php

namespace App\Domain\Claims\Actions;

use App\Domain\Claims\DTOs\CreateClaimData;
use App\Domain\Claims\Exceptions\DuplicateClaimException;
use App\Domain\Claims\Services\ClaimService;
use App\Models\Claim;
use App\Models\ClaimStatusHistory;
use Illuminate\Support\Facades\DB;

class CreateClaim
{
    public function __construct(protected ClaimService $claimService)
    {}

    public function execute(CreateClaimData $data): Claim
    {
        $existing = Claim::where('item_id', $data->itemId)
            ->where('user_id', $data->userId)
            ->whereIn('status', ['pending', 'under_review', 'approved'])
            ->first();

        if ($existing) {
            throw new DuplicateClaimException();
        }

        return DB::transaction(function () use ($data) {
            $claim = Claim::create([
                'claim_number' => $this->claimService->generateClaimNumber(),
                'item_id' => $data->itemId,
                'user_id' => $data->userId,
                'status' => 'pending',
                'claim_reason' => $data->claimReason,
                'verification_answers' => $data->verificationAnswers,
            ]);

            ClaimStatusHistory::create([
                'claim_id' => $claim->id,
                'changed_by_user_id' => $data->userId,
                'new_status' => 'pending',
                'comment' => 'Claim submitted',
            ]);

            return $claim;
        });
    }
}
