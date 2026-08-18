<?php

namespace App\Console\Commands;

use App\Models\AuthVerification;
use Illuminate\Console\Command;

class CleanupExpiredAuthVerifications extends Command
{
    protected $signature = 'auth:cleanup-expired';
    protected $description = 'Clean up expired authentication verification tokens and OTPs';

    public function handle(): int
    {
        $this->info('Cleaning up expired auth verifications...');
        $deleted = AuthVerification::where('expires_at', '<', now())->delete();
        $this->info('Cleaned up ' . $deleted . ' expired auth verifications.');
        return Command::SUCCESS;
    }
}
