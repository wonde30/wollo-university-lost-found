<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use IlluminateFoundationQueueQueueable;

class GenerateReportExport implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {}

    public function handle(): void
    {}
}
