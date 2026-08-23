<?php

declare(strict_types=1);

namespace App\Mail\Claims;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Claim $claim)
    {}

    public function build(): self
    {
        $this->claim->loadMissing(['item.location', 'item.campus', 'claimant']);

        return $this->subject('Wollo Lost & Found - Claim Approved!')
            ->view('emails.claims.approved', [
                'claim' => $this->claim,
            ]);
    }
}
