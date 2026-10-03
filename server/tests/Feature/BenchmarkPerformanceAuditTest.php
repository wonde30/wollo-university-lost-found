<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Report;
use App\Models\ReturnRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BenchmarkPerformanceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_benchmark_all_required_endpoints(): void
    {
        // 1. Seed base data
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin', 'is_system' => true, 'is_active' => true]);
        $staffRole = Role::firstOrCreate(['name' => 'staff'], ['display_name' => 'Staff', 'is_system' => true, 'is_active' => true]);
        $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'is_system' => true, 'is_active' => true]);

        $group = PermissionGroup::create(['name' => 'General', 'display_name' => 'General Management', 'sort_order' => 1]);
        $perm = Permission::create([
            'permission_group_id' => $group->id,
            'name' => 'ACCESS_ADMIN_DASHBOARD',
            'display_name' => 'Access Dashboard',
            'category' => 'admin',
            'is_active' => true,
        ]);
        $adminRole->permissions()->syncWithoutDetaching([$perm->id]);

        $campus = Campus::create(['name' => 'Dessie Main Campus', 'short_code' => 'DSS', 'city' => 'Dessie', 'region' => 'Amhara']);
        $category = Category::create(['name' => 'Electronics', 'display_name' => 'Electronics', 'icon' => 'laptop']);

        $admin = User::factory()->admin()->create([
            'email' => 'admin@wollo.edu.et',
            'password' => bcrypt('AdminPassword123!'),
        ]);

        $users = User::factory()->count(10)->create(['role_id' => $studentRole->id]);

        // Seed items, claims, custody, returns, notifications, reports
        for ($i = 1; $i <= 5; $i++) {
            $item = Item::create([
                'reference_code' => "WU-ITEM-00{$i}",
                'reporter_id' => $admin->id,
                'campus_id' => $campus->id,
                'category_id' => $category->id,
                'type' => 'found',
                'status' => 'found_unclaimed',
                'title' => "Found Item {$i}",
                'description' => "Description for item {$i}",
                'incident_date' => now()->toDateString(),
            ]);

            CustodyEvent::create([
                'item_id' => $item->id,
                'actor_id' => $admin->id,
                'event_type' => 'intake',
                'notes' => 'Intake note',
            ]);

            $claim = Claim::create([
                'claim_number' => "CLM-00{$i}",
                'item_id' => $item->id,
                'claimant_id' => $users[$i]->id,
                'status' => 'approved',
                'claim_reason' => 'Proof of ownership',
            ]);

            ReturnRecord::create([
                'claim_id' => $claim->id,
                'returned_to' => $users[$i]->id,
                'handed_over_by' => $admin->id,
                'return_date' => now()->toDateString(),
            ]);

            Notification::create([
                'user_id' => $admin->id,
                'type' => 'item_reported',
                'title' => 'Item Reported',
                'data' => ['message' => 'New item reported'],
            ]);
        }

        Report::create([
            'requested_by' => $admin->id,
            'report_type' => 'items_summary',
            'status' => 'completed',
            'parameters' => ['period' => '30d'],
            'output_path' => 'reports/test.pdf',
        ]);

        $results = [];

        // Benchmark Helper
        $measure = function (string $name, callable $requestFn) use (&$results) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $start = microtime(true);
            $response = $requestFn();
            $durationMs = round((microtime(true) - $start) * 1000, 2);

            $queries = DB::getQueryLog();
            $queryCount = count($queries);
            $slowestQueryMs = 0.0;
            $slowestSql = '';

            foreach ($queries as $q) {
                if ($q['time'] > $slowestQueryMs) {
                    $slowestQueryMs = $q['time'];
                    $slowestSql = $q['query'];
                }
            }

            $responseSize = strlen($response->getContent() ?: '');

            $results[$name] = [
                'status' => $response->getStatusCode(),
                'query_count' => $queryCount,
                'slowest_query_ms' => $slowestQueryMs,
                'slowest_sql' => substr($slowestSql, 0, 60),
                'response_time_ms' => $durationMs,
                'response_size_bytes' => $responseSize,
            ];

            return $response;
        };

        // 1. Login
        $measure('login', fn () => $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@wollo.edu.et',
            'password' => 'AdminPassword123!',
        ]));

        // 2. Registration
        $measure('registration', fn () => $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Benchmark Student',
            'university_id' => 'WU/99999/16',
            'email' => 'benchmark.student@wollo.edu.et',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]));

        // Authenticate admin for remaining endpoints
        $this->actingAs($admin);

        // 3. Dashboard Statistics
        $measure('dashboard', fn () => $this->getJson('/api/v1/admin/dashboard/statistics?period=90d'));

        // 4. Users List
        $measure('users_list', fn () => $this->getJson('/api/v1/admin/users?page=1&per_page=10'));

        // 5. User Detail
        $measure('user_detail', fn () => $this->getJson("/api/v1/admin/users/{$admin->id}"));

        // 6. Roles List
        $measure('roles', fn () => $this->getJson('/api/v1/admin/roles'));

        // 7. Permissions List
        $measure('permissions', fn () => $this->getJson('/api/v1/admin/permissions'));

        // 8. Permission Groups List
        $measure('permission_groups', fn () => $this->getJson('/api/v1/admin/permission-groups'));

        // 9. Items List
        $measure('items', fn () => $this->getJson('/api/v1/items?page=1&per_page=10'));

        // 10. Claims List
        $measure('claims', fn () => $this->getJson('/api/v1/claims?page=1&per_page=10'));

        // 11. Custody History
        $measure('custody', fn () => $this->getJson('/api/v1/custody'));

        // 12. Returns List
        $measure('returns', fn () => $this->getJson('/api/v1/returns'));

        // 13. Notifications List
        $measure('notifications', fn () => $this->getJson('/api/v1/notifications?per_page=10'));

        // 14. Reports List
        $measure('reports', fn () => $this->getJson('/api/v1/admin/reports'));

        file_put_contents(base_path('benchmark_results.json'), json_encode($results, JSON_PRETTY_PRINT));

        $this->assertTrue(true);
    }
}
