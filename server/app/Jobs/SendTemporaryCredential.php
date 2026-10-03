<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\Auth\TemporaryCredentialMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendTemporaryCredential implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly string $temporaryPassword
    ) {}

    public function handle(): void
    {
        if ($this->user->email) {
            Mail::to($this->user->email)->send(new TemporaryCredentialMail($this->user, $this->temporaryPassword));
        }
    }
}
