<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Claim;
use App\Models\Item;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@wu.edu.et')->first();
        $dessieStaff = User::where('email', 'security.dessie@wu.edu.et')->first() ?? $admin;
        $kiotStaff = User::where('email', 'security.kiot@wu.edu.et')->first() ?? $dessieStaff;
        $studentAlemayehu = User::where('email', 'student@wu.edu.et')->first();
        $studentBethlehem = User::where('email', 'student2@wu.edu.et')->first();
        $studentFatima = User::where('email', 'fatima.h@wu.edu.et')->first();

        $itemF01 = Item::where('reference_code', 'WU-F000001')->first();
        $itemF02 = Item::where('reference_code', 'WU-F000002')->first();
        $itemF03 = Item::where('reference_code', 'WU-F000003')->first();
        $itemL01 = Item::where('reference_code', 'WU-L000001')->first();
        $claim1 = Claim::where('item_id', $itemF02?->id)->first();
        $return1 = ReturnRecord::where('claim_id', $claim1?->id)->first();

        $logs = [
            // Admin Authentication
            [
                'actor_id' => $admin?->id,
                'actor_role' => 'admin',
                'action' => 'user.login',
                'auditable_type' => User::class,
                'auditable_id' => $admin?->id,
                'old_values' => null,
                'new_values' => ['ip' => '192.168.10.15', 'method' => 'sanctum_session'],
                'ip_address' => '192.168.10.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                'created_at' => now()->subHours(6),
            ],
            // Staff Login
            [
                'actor_id' => $dessieStaff?->id,
                'actor_role' => 'staff',
                'action' => 'user.login',
                'auditable_type' => User::class,
                'auditable_id' => $dessieStaff?->id,
                'old_values' => null,
                'new_values' => ['ip' => '192.168.10.22', 'device' => 'Security Station PC 1'],
                'ip_address' => '192.168.10.22',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                'created_at' => now()->subHours(8),
            ],
            // Student Lost Item Report
            [
                'actor_id' => $studentAlemayehu?->id,
                'actor_role' => 'student',
                'action' => 'item.created',
                'auditable_type' => Item::class,
                'auditable_id' => $itemL01?->id,
                'old_values' => null,
                'new_values' => ['reference_code' => 'WU-L000001', 'type' => 'lost', 'title' => 'Lost HP Pavilion 15 Core-i7 Laptop'],
                'ip_address' => '10.20.4.55',
                'user_agent' => 'Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36',
                'created_at' => now()->subDays(3),
            ],
            // Staff Found Item Cataloging
            [
                'actor_id' => $dessieStaff?->id,
                'actor_role' => 'staff',
                'action' => 'item.created',
                'auditable_type' => Item::class,
                'auditable_id' => $itemF01?->id,
                'old_values' => null,
                'new_values' => ['reference_code' => 'WU-F000001', 'type' => 'found', 'status' => 'found_unclaimed', 'held_at' => 'security_office'],
                'ip_address' => '192.168.10.22',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(2),
            ],
            // Custody Intake
            [
                'actor_id' => $dessieStaff?->id,
                'actor_role' => 'staff',
                'action' => 'custody.intake',
                'auditable_type' => Item::class,
                'auditable_id' => $itemF01?->id,
                'old_values' => ['held_at' => null],
                'new_values' => ['held_at' => 'security_office', 'storage_location' => 'DSS-SEC-CAB1', 'condition' => 'good'],
                'ip_address' => '192.168.10.22',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(2)->addMinutes(15),
            ],
            // Claim Submitted
            [
                'actor_id' => $studentAlemayehu?->id,
                'actor_role' => 'student',
                'action' => 'claim.created',
                'auditable_type' => Claim::class,
                'auditable_id' => $claim1?->id,
                'old_values' => null,
                'new_values' => ['item_id' => $itemF02?->id, 'claimant_id' => $studentAlemayehu?->id, 'status' => 'pending'],
                'ip_address' => '10.20.4.55',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(2),
            ],
            // Claim Approved
            [
                'actor_id' => $dessieStaff?->id,
                'actor_role' => 'staff',
                'action' => 'claim.approved',
                'auditable_type' => Claim::class,
                'auditable_id' => $claim1?->id,
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'approved', 'reviewer' => 'Abebe Bekele', 'notes' => 'Registration slip ID matched.'],
                'ip_address' => '192.168.10.22',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(1),
            ],
            // Physical Handover Return Record
            [
                'actor_id' => $dessieStaff?->id,
                'actor_role' => 'staff',
                'action' => 'return.processed',
                'auditable_type' => ReturnRecord::class,
                'auditable_id' => $return1?->id,
                'old_values' => null,
                'new_values' => ['claim_id' => $claim1?->id, 'returned_to' => $studentAlemayehu?->id, 'condition' => 'good'],
                'ip_address' => '192.168.10.22',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(1)->addHours(1),
            ],
            // Recipient Return Confirmation
            [
                'actor_id' => $studentAlemayehu?->id,
                'actor_role' => 'student',
                'action' => 'return.confirmed_by_token',
                'auditable_type' => ReturnRecord::class,
                'auditable_id' => $return1?->id,
                'old_values' => ['recipient_confirmed' => false],
                'new_values' => ['recipient_confirmed' => true, 'confirmed_at' => now()->subDays(1)->addHours(1)->toDateTimeString()],
                'ip_address' => '10.20.4.55',
                'user_agent' => 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36',
                'created_at' => now()->subDays(1)->addHours(1),
            ],
            // System Setting Updated
            [
                'actor_id' => $admin?->id,
                'actor_role' => 'admin',
                'action' => 'system_setting.updated',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => ['match_score_threshold' => '40.00'],
                'new_values' => ['match_score_threshold' => '35.00'],
                'ip_address' => '192.168.10.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subHours(12),
            ],
            // Reports Export Download
            [
                'actor_id' => $admin?->id,
                'actor_role' => 'admin',
                'action' => 'report.downloaded',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => ['report_type' => 'monthly_summary', 'format' => 'pdf', 'rows' => 142],
                'ip_address' => '192.168.10.15',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}
