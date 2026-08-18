<?php

namespace App\Domain\Claims\Services;

use App\Models\Claim;

class ClaimService
{
    public function generateClaimNumber(): string
    {
        return 'CLM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
}
