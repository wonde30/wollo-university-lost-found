<?php

namespace App\Domain\Administration\Services;

use App\Domain\Administration\DTOs\ReportFilterData;
use App\Models\Report;

class ReportExportService
{
    public function export(ReportFilterData $data, int $userId): Report
    {
        $path = 'reports/' . uniqid('export_') . '.' . ($data->format ?? 'pdf');

        return Report::create([
            'generated_by_user_id' => $userId,
            'title' => $data->title,
            'report_type' => $data->reportType,
            'filters' => (array)$data,
            'file_path' => $path,
            'format' => $data->format ?? 'pdf',
            'status' => 'completed',
            'generated_at' => now(),
        ]);
    }
}
