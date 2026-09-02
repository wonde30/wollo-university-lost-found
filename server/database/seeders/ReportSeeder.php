<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@wu.edu.et')->first() ?? User::first();

        if (! $admin) {
            return;
        }

        $reports = [
            [
                'requested_by' => $admin->id,
                'report_type' => 'monthly_summary',
                'filters' => ['month' => now()->format('Y-m'), 'campus' => 'all'],
                'format' => 'pdf',
                'status' => 'ready',
                'file_path' => 'reports/monthly_recovery_summary_' . now()->format('Y_m') . '.pdf',
                'file_size_bytes' => 2457600,
                'row_count' => 142,
                'ready_at' => now()->subHours(5),
                'downloaded_at' => now()->subHours(2),
                'download_count' => 4,
                'expires_at' => now()->addDays(30),
            ],
            [
                'requested_by' => $admin->id,
                'report_type' => 'custody_inventory',
                'filters' => ['campus' => 'DSS', 'status' => 'in_storage'],
                'format' => 'csv',
                'status' => 'ready',
                'file_path' => 'reports/central_vault_inventory_' . now()->format('Y_m_d') . '.csv',
                'file_size_bytes' => 524288,
                'row_count' => 86,
                'ready_at' => now()->subHours(4),
                'downloaded_at' => now()->subHours(1),
                'download_count' => 2,
                'expires_at' => now()->addDays(30),
            ],
            [
                'requested_by' => $admin->id,
                'report_type' => 'recovery_rate_by_campus',
                'filters' => ['year' => 2026, 'quarter' => 'Q3'],
                'format' => 'xlsx',
                'status' => 'ready',
                'file_path' => 'reports/campus_recovery_rate_q3_2026.xlsx',
                'file_size_bytes' => 1887436,
                'row_count' => 320,
                'ready_at' => now()->subDays(1),
                'downloaded_at' => null,
                'download_count' => 0,
                'expires_at' => now()->addDays(30),
            ],
            [
                'requested_by' => $admin->id,
                'report_type' => 'claims_dispute_audit',
                'filters' => ['dispute_status' => 'all'],
                'format' => 'pdf',
                'status' => 'ready',
                'file_path' => 'reports/claims_dispute_audit_' . now()->format('Y_m') . '.pdf',
                'file_size_bytes' => 962560,
                'row_count' => 24,
                'ready_at' => now()->subDays(2),
                'downloaded_at' => now()->subDays(1),
                'download_count' => 1,
                'expires_at' => now()->addDays(28),
            ],
            [
                'requested_by' => $admin->id,
                'report_type' => 'realtime_activity_export',
                'filters' => ['date_range' => 'last_7_days'],
                'format' => 'csv',
                'status' => 'pending',
                'file_path' => null,
                'file_size_bytes' => null,
                'row_count' => 0,
                'ready_at' => null,
                'downloaded_at' => null,
                'download_count' => 0,
                'expires_at' => null,
            ],
        ];

        foreach ($reports as $repData) {
            Report::create($repData);
        }
    }
}
