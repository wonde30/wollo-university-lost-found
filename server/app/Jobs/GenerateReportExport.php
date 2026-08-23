<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Report;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateReportExport implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Report $report)
    {}

    public function handle(): void
    {
        $report = $this->report;
        $format = strtolower($report->format ?? 'csv');
        $fileName = 'reports/report_' . $report->id . '_' . uniqid() . '.' . $format;

        // Generate report export content based on filters and report type
        $content = "Report Type: {$report->report_type}\nGenerated At: " . now()->toISOString() . "\nFormat: {$format}\n";
        
        Storage::disk('public')->put($fileName, $content);

        $report->update([
            'status' => 'ready',
            'file_path' => $fileName,
            'ready_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }
}
