<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\Location;
use App\Models\MatchSuggestion;
use App\Models\Notification;
use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\Report;
use App\Models\ReturnRecord;
use App\Models\Role;
use App\Models\SearchLog;
use App\Models\StorageLocation;
use App\Models\SystemAnnouncement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with comprehensive real-world Wollo University data.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CampusSeeder::class,
            OrganizationalUnitTypeSeeder::class,
            OrganizationalUnitSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            LocationSeeder::class,
            StorageLocationSeeder::class,
            ItemAndClaimsSeeder::class,
            ClaimAndEvidenceSeeder::class,
            CustodyAndReturnSeeder::class,
            MatchSuggestionSeeder::class,
            NotificationSeeder::class,
            SystemAnnouncementSeeder::class,
            AuditLogSeeder::class,
            ReportSeeder::class,
            SearchLogAndAnalyticsSeeder::class,
            SystemSettingSeeder::class,
        ]);

        Cache::forget('admin.statistics');
        Cache::forget('settings.all');

        // Output rich seeder summary information to the console
        if ($this->command) {
            $this->command->newLine();
            $this->command->info('================================================================');
            $this->command->info(' 🎉 WOLLO UNIVERSITY LOST & FOUND SEEDER COMPLETED SUCCESSFULLY');
            $this->command->info('================================================================');
            $this->command->newLine();

            $this->command->table(
                ['Entity', 'Count', 'Key Details'],
                [
                    ['Campuses', Campus::count(), 'Dessie Main (DSS), KIoT Kombolcha (KIT), Tita Health (TITA)'],
                    ['Org Units & Types', OrganizationalUnit::count(), 'Colleges, Institutes, Directorates, Departments across campuses'],
                    ['Roles', Role::count(), 'admin, staff, student, security_supervisor, department_head'],
                    ['Permissions', Permission::count(), 'Granular permissions mapped across 6 permission groups'],
                    ['User Accounts', User::count(), '1 Admin, 3 Staff, 1 Supervisor, 2 Dept Heads, 8 Students'],
                    ['Categories', Category::count(), 'Electronics, ID/Docs, Clothing, Books, Keys, Bags, Jewelry, etc.'],
                    ['Locations', Location::count(), 'Libraries, Lecture Halls, Cafeterias, Labs, Stadium, Gate booths'],
                    ['Storage Vaults', StorageLocation::count(), 'Main Vault, Locker Bays, High-Value Safes, Intake Bins'],
                    ['Items Catalog', Item::count(), Item::where('type', 'lost')->count() . ' Lost | ' . Item::where('type', 'found')->count() . ' Found (All lifecycles covered)'],
                    ['Ownership Claims', Claim::count(), 'Pending, Under Review, Approved, Rejected, Reversed'],
                    ['Custody Events', CustodyEvent::count(), 'Intake, Vault relocation, Inspection, Handover records'],
                    ['Return Handover', ReturnRecord::count(), 'Confirmed return + Active Token (test-confirm-token-wu-2026)'],
                    ['AI Match Suggestions', MatchSuggestion::count(), 'Score breakdowns (94.5%, 91%, accepted, reviewed, rejected)'],
                    ['In-App Notifications', Notification::count(), 'Item matches, Claim approvals, Expiry alerts, Handover notices'],
                    ['Announcements', SystemAnnouncement::count(), 'Clearance week, KIoT depot activation, Staff protocol'],
                    ['Audit Logs', AuditLog::count(), 'Logins, creations, approvals, handovers, settings diffs'],
                    ['Generated Reports', Report::count(), 'PDF, CSV, Excel formats (Ready & Pending)'],
                    ['Search & Views', SearchLog::count() . ' queries', 'Includes 0-result searches for 25% Search Fail Rate metric'],
                ]
            );

            $this->command->newLine();
            $this->command->info('🔑 TEST CREDENTIALS SUMMARY:');
            $this->command->table(
                ['Role', 'Email', 'University ID', 'Password'],
                [
                    ['System Admin', 'admin@wu.edu.et', 'ADMIN-001', 'Admin@Wollo2026!'],
                    ['Dessie Security Staff', 'security.dessie@wu.edu.et', 'STAFF-DESSIE-01', 'Staff@Dessie2026!'],
                    ['KIoT Security Staff', 'security.kiot@wu.edu.et', 'STAFF-KIOT-01', 'Staff@Dessie2026!'],
                    ['Tita Security Staff', 'security.tita@wu.edu.et', 'STAFF-TITA-01', 'Staff@Dessie2026!'],
                    ['Security Supervisor', 'supervisor.security@wu.edu.et', 'SUP-SEC-01', 'Staff@Dessie2026!'],
                    ['Dept Head (CS)', 'head.cs@wu.edu.et', 'FAC-CS-001', 'Admin@Wollo2026!'],
                    ['Dept Head (SE)', 'head.se@wu.edu.et', 'FAC-SE-001', 'Admin@Wollo2026!'],
                    ['Student (Alemayehu)', 'student@wu.edu.et', 'STUDENT-001', 'student@Wollo2026!'],
                    ['Student (Bethlehem)', 'student2@wu.edu.et', 'STUDENT-002', 'student@Wollo2026!'],
                    ['Student (Dawit - Med)', 'student3@wu.edu.et', 'STUDENT-003', 'student@Wollo2026!'],
                    ['Student (Fatima - ECE)', 'fatima.h@wu.edu.et', 'WU/114520/14', 'student@Wollo2026!'],
                    ['Student (Helen - IT)', 'helen.a@wu.edu.et', 'WU/117890/15', 'student@Wollo2026!'],
                ]
            );

            $this->command->newLine();
            $this->command->info('🔗 TEST RETURN CONFIRMATION LINK:');
            $this->command->line('http://localhost:5173/returns/confirm/test-confirm-token-wu-2026');
            $this->command->newLine();
        }
    }
}
