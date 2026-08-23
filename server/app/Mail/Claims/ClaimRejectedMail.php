<?php

declare(strict_types=1);

namespace App\Mail\Claims;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Claim $claim)
    {}

    public function build(): self
    {
        $this->claim->loadMissing(['item', 'claimant']);

        return $this->subject('Wollo Lost & Found - Claim Update')
            ->view('emails.claims.rejected', [
                'claim' => $this->claim,
            ]);
    }
}
