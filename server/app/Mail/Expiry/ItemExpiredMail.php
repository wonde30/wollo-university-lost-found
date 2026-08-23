<?php

declare(strict_types=1);

namespace App\Mail\Expiry;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ItemExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Item $item)
    {}

    public function build(): self
    {
        $this->item->loadMissing(['reporter']);

        return $this->subject("Wollo Lost & Found - Item Listing Expired [{$this->item->reference_code}]")
            ->view('emails.expiry.expired', [
                'item' => $this->item,
            ]);
    }
}
