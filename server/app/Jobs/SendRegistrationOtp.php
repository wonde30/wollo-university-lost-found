<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\Auth\RegistrationOtpMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendRegistrationOtp implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly string $code
    ) {}

    public function handle(): void
    {
        if ($this->user->email) {
            Mail::to($this->user->email)->send(new RegistrationOtpMail($this->user, $this->code));
        }
    }
}
