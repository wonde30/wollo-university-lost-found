<?php

declare(strict_types=1);

namespace App\Mail\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $code
    ) {}

    public function build(): self
    {
        return $this->subject('Wollo University Lost & Found - Email Verification Code')
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.auth.registration-otp', [
                'user' => $this->user,
                'code' => $this->code,
            ]);
    }
}
