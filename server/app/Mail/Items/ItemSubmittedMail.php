<?php

declare(strict_types=1);

namespace App\Mail\Items;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ItemSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Item $item)
    {}

    public function build(): self
    {
        $this->item->loadMissing(['reporter', 'category', 'location', 'campus']);

        return $this->subject("Wollo Lost & Found - Report Registered [{$this->item->reference_code}]")
            ->view('emails.items.submitted', [
                'item' => $this->item,
            ]);
    }
}
