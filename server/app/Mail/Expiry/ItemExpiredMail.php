<?php

namespace App\Mail\Expiry;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ItemExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('Wollo Lost & Found Notification')->html('Notification Email');
    }
}
