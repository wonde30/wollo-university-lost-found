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
                'type' => 'item_matched',
                'channel' => 'in_app',
                'data' => [
                    'title' => 'Potential Match Found for Your Lost HP Laptop',
                    'message' => 'A silver HP laptop matching your report (WU-L000001) has been turned in at Dessie Main Library (Match Score: 91%).',
                    'reference_code' => 'WU-L000001',
                    'matched_item_code' => 'WU-F000001',
                    'link' => '/student/items',
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
                    'title' => 'Claim Approved — Calculus & Analytic Geometry Textbook',
                    'message' => 'Your ownership claim for item WU-F000002 has been verified by Dessie Campus Security. Please collect it from Central Vault Desk.',
                    'reference_code' => 'WU-F000002',
                    'link' => '/student/claims',
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
                    'title' => 'Property Handover Completed',
                    'message' => 'Item WU-F000002 has been marked as returned to you. Thank you for using Wollo University Lost & Found!',
                    'reference_code' => 'WU-F000002',
                    'link' => '/student/claims',
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
                'type' => 'claim_under_review',
                'channel' => 'in_app',
                'data' => [
                    'title' => 'Claim Under Review — Samsung Galaxy A54',
                    'message' => 'Your claim for item WU-F000003 is currently being reviewed by Campus Security. You are invited for in-person PIN unlock.',
                    'reference_code' => 'WU-F000003',
                    'link' => '/student/claims',
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
                    'title' => 'Claim Approved — Apple iPad Air (Space Gray)',
                    'message' => 'Your claim for iPad Air (WU-F000006) has been approved by KIoT Security. Ready for collection at KIoT Depot Locker Bay 1.',
                    'reference_code' => 'WU-F000006',
                    'link' => '/student/claims',
                ],
                'is_read' => false,
                'created_at' => now()->subHours(2),
            ]);
        }

        // 5. Notifications for Helen
        if ($studentHelen) {
            Notification::create([
                'user_id' => $studentHelen->id,
                'type' => 'return_confirmation_request',
                'channel' => 'in_app',
                'data' => [
                    'title' => 'Please Confirm Handover Receipt — Casio Calculator',
                    'message' => 'Campus Security handed over your Casio fx-991EX (WU-F000007). Click here to submit your recipient confirmation.',
                    'reference_code' => 'WU-F000007',
                    'token' => 'test-confirm-token-wu-2026',
                    'link' => '/returns/confirm/test-confirm-token-wu-2026',
                ],
                'is_read' => false,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        // 6. Notifications for Staff
        if ($dessieStaff) {
            Notification::create([
                'user_id' => $dessieStaff->id,
                'type' => 'new_claim_submitted',
                'channel' => 'in_app',
                'data' => [
                    'title' => 'New Ownership Claim Submitted',
                    'message' => 'Student Helen Assefa submitted a claim with warranty proof for Lenovo ThinkPad Laptop (WU-F000017).',
                    'reference_code' => 'WU-F000017',
                    'link' => '/staff/claims',
                ],
                'is_read' => false,
                'created_at' => now()->subHours(1),
            ]);

            Notification::create([
                'user_id' => $dessieStaff->id,
                'type' => 'custody_expiry_warning',
                'channel' => 'in_app',
                'data' => [
                    'title' => 'Storage Expiry Warning (2 Items)',
                    'message' => '2 items in Central Vault (WU-F000013 Umbrella, WU-F000014 Fossil Watch) will reach the 90-day statutory limit in less than 7 days.',
                    'link' => '/staff/custody',
                ],
                'is_read' => false,
                'created_at' => now()->subHours(6),
            ]);
        }

        // 7. Notifications for Admin
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'system_report_ready',
                'channel' => 'in_app',
                'data' => [
                    'title' => 'Monthly Recovery Analytics Report Ready',
                    'message' => 'The monthly cross-campus recovery summary report (PDF) has finished generating and is ready for download.',
                    'link' => '/admin/reports',
                ],
                'is_read' => false,
                'created_at' => now()->subHours(3),
            ]);
        }
    }
}
