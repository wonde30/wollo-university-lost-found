<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSystemReports extends Command
{
    protected $signature = 'reports:generate-system';
    protected $description = 'Generate periodic system statistics and audit reports';

    public function handle(): int
    {
        $this->info('Generating system reports...');
        return Command::SUCCESS;
    }
}
