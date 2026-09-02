<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemAnnouncement;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemAnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@wu.edu.et')->first() ?? User::first();

        if (! $admin) {
            return;
        }

        $announcements = [
            [
                'created_by' => $admin->id,
                'title' => 'Semester Final Exams: Lost Property Clearance Notice',
                'body' => 'All students and staff who lost personal items during semester examinations are advised to check the Lost & Found Portal and visit their respective campus security vault (Dessie Main Vault, KIoT Central Depot, or Tita Security Station) before semester break.',
                'type' => 'warning',
                'audience' => 'all',
                'is_active' => true,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(25),
            ],
            [
                'created_by' => $admin->id,
                'title' => 'New KIoT Campus Custody Depot Activated',
                'body' => 'The Kombolcha Institute of Technology security depot is now fully operational with dedicated electronic locker bays and engineering drawing tool bins.',
                'type' => 'info',
                'audience' => 'students',
                'is_active' => true,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(30),
            ],
            [
                'created_by' => $admin->id,
                'title' => 'Security Staff Protocol: Verification for High-Value Electronics',
                'body' => 'Please ensure all laptops, smartphones, and tablets undergo strict serial number and PIN unlock verification before completing the physical return record and generating confirmation tokens.',
                'type' => 'urgent',
                'audience' => 'staff',
                'is_active' => true,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(60),
            ],
        ];

        foreach ($announcements as $announcement) {
            SystemAnnouncement::updateOrCreate(
                ['title' => $announcement['title']],
                $announcement
            );
        }
    }
}
