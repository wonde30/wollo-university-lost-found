<?php

declare(strict_types=1);

namespace App\Mail\Returns;

use App\Models\ReturnRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ItemReturnedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ReturnRecord $record)
    {}

    public function build(): self
    {
        $this->record->loadMissing(['item', 'claim.claimant']);

        return $this->subject("Wollo Lost & Found - Handover Confirmed [{$this->record->return_reference}]")
            ->view('emails.returns.returned', [
                'record' => $this->record,
            ]);
    }
}
