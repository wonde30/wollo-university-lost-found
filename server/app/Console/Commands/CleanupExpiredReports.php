<?php

namespace App\Console\Commands;

use App\Models\Report;
use IlluminateConsoleCommand;

class CleanupExpiredReports extends Command
{
    protected $signature = 'reports:cleanup-expired';
    protected $description = 'Clean up expired generated system reports and export files';

    public function handle(): int
    {
        $this->info('Cleaning up expired reports...');
        $deleted = Report::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
        $this->info('Cleaned up ' . $deleted . ' expired reports.');
        return Command::SUCCESS;
    }
}
