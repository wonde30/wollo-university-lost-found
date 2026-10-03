<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // 1. Seed preferences for every user
        foreach ($users as $user) {
            NotificationPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'email_on_report_submitted' => true,
                    'email_on_match_found' => true,
                    'email_on_claim_received' => true,
                    'email_on_claim_decided' => true,
                    'email_on_item_returned' => true,
                    'email_on_expiry_warning' => true,
                    'email_on_item_expired' => false,
                    'email_on_system_announcements' => true,
                ]
            );
        }

        $studentAlemayehu = User::where('email', 'student@wu.edu.et')->first();
        $studentBethlehem = User::where('email', 'student2@wu.edu.et')->first();
        $studentFatima = User::where('email', 'fatima.h@wu.edu.et')->first();
        $studentHelen = User::where('email', 'helen.a@wu.edu.et')->first();
        $dessieStaff = User::where('email', 'security.dessie@wu.edu.et')->first();
        $admin = User::where('email', 'admin@wu.edu.et')->first();

        // 2. Notifications for Alemayehu (student)
        if ($studentAlemayehu) {
            Notification::create([
                'user_id' => $studentAlemayehu->id,
                'type' => 'item_match',
                'channel' => 'in_app',
                'data' => [
                    'match_id' => 1,
                    'lost_item_id' => 1,
                    'found_item_id' => 2,
                    'lost_reference_code' => 'WU-L000001',
                    'found_reference_code' => 'WU-F000001',
                    'title' => 'HP Laptop',
                    'score' => 91,
                ],
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subHours(5),
            ]);

            Notification::create([
                'user_id' => $studentAlemayehu->id,
                'type' => 'claim_approved',
                'channel' => 'in_app',
                'data' => [
                    'claim_id' => 1,
                    'item_id' => 2,
                    'reference_code' => 'WU-F000002',
                    'title' => 'Calculus & Analytic Geometry Textbook',
                    'decision' => 'approved',
                ],
                'is_read' => true,
                'read_at' => now()->subDay(),
                'created_at' => now()->subDays(1),
            ]);

            Notification::create([
                'user_id' => $studentAlemayehu->id,
                'type' => 'item_returned',
                'channel' => 'in_app',
                'data' => [
                    'return_id' => 1,
                    'claim_id' => 1,
                    'item_id' => 2,
                    'reference_code' => 'WU-F000002',
                    'title' => 'Calculus & Analytic Geometry Textbook',
                ],
                'is_read' => true,
                'read_at' => now()->subHours(12),
                'created_at' => now()->subDays(1)->addHours(1),
            ]);
        }

        // 3. Notifications for Bethlehem
        if ($studentBethlehem) {
            Notification::create([
                'user_id' => $studentBethlehem->id,
                'type' => 'claim_submitted',
                'channel' => 'in_app',
                'data' => [
                    'claim_id' => 2,
                    'item_id' => 3,
                    'reference_code' => 'WU-F000003',
                    'title' => 'Samsung Galaxy A54',
                    'sub_type' => 'claimant',
                ],
                'is_read' => false,
                'created_at' => now()->subHours(4),
            ]);
        }

        // 4. Notifications for Fatima (KIoT)
        if ($studentFatima) {
            Notification::create([
                'user_id' => $studentFatima->id,
                'type' => 'claim_approved',
                'channel' => 'in_app',
                'data' => [
                    'claim_id' => 3,
                    'item_id' => 6,
                    'reference_code' => 'WU-F000006',
                    'title' => 'Apple iPad Air (Space Gray)',
                    'decision' => 'approved',
                ],
                'is_read' => false,
                'created_at' => now()->subHours(2),
            ]);
        }

        // 5. Notifications for Helen
        if ($studentHelen) {
            Notification::create([
                'user_id' => $studentHelen->id,
                'type' => 'item_returned',
                'channel' => 'in_app',
                'data' => [
                    'return_id' => 2,
                    'claim_id' => 4,
                    'item_id' => 7,
                    'reference_code' => 'WU-F000007',
                    'title' => 'Casio fx-991EX',
                    'confirmation_token' => 'test-confirm-token-wu-2026',
                ],
                'is_read' => false,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        // 6. Notifications for Staff
        if ($dessieStaff) {
            Notification::create([
                'user_id' => $dessieStaff->id,
                'type' => 'claim_submitted',
                'channel' => 'in_app',
                'data' => [
                    'claim_id' => 5,
                    'item_id' => 17,
                    'reference_code' => 'WU-F000017',
                    'title' => 'Lenovo ThinkPad Laptop',
                    'sub_type' => 'staff',
                ],
                'is_read' => false,
                'created_at' => now()->subHours(1),
            ]);

            Notification::create([
                'user_id' => $dessieStaff->id,
                'type' => 'item_expiring',
                'channel' => 'in_app',
                'data' => [
                    'item_id' => 13,
                    'reference_code' => 'WU-F000013',
                    'title' => 'Central Vault Storage Items',
                    'days_remaining' => 7,
                ],
                'is_read' => false,
                'created_at' => now()->subHours(6),
            ]);
        }

        // 7. Notifications for Admin
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'report_generated',
                'channel' => 'in_app',
                'data' => [
                    'report_id' => 1,
                    'report_type' => 'Monthly Recovery Analytics',
                    'format' => 'pdf',
                    'row_count' => 48,
                ],
                'is_read' => false,
                'created_at' => now()->subHours(3),
            ]);
        }
    }
}
