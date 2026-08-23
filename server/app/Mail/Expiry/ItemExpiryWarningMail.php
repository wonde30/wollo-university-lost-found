<?php

declare(strict_types=1);

namespace App\Mail\Expiry;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ItemExpiryWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Item $item,
        public readonly int $daysRemaining = 5
    ) {}

    public function build(): self
    {
        $this->item->loadMissing(['reporter']);

        return $this->subject("Wollo Lost & Found - Expiry Warning [{$this->item->reference_code}]")
            ->view('emails.expiry.warning', [
                'item' => $this->item,
                'daysRemaining' => $this->daysRemaining,
            ]);
    }
}
