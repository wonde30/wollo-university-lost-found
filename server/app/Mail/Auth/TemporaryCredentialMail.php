<?php

declare(strict_types=1);

namespace App\Mail\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemporaryCredentialMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $temporaryPassword
    ) {}

    public function build(): self
    {
        return $this->subject('Wollo University Lost & Found - Account Credentials')
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.auth.temporary-credential', [
                'user'     => $this->user,
                'password' => $this->temporaryPassword,
            ]);
    }
}
