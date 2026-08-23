<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\Auth\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetOtp implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly string $code
    ) {}

    public function handle(): void
    {
        if ($this->user->email) {
            Mail::to($this->user->email)->send(new PasswordResetOtpMail($this->user, $this->code));
        }
    }
}
