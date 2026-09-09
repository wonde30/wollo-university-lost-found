<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Administration\Services\DashboardStatisticsService;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_dashboard_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/admin/dashboard/statistics?period=90d');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary' => [
                    'total_items',
                    'lost_items',
                    'active_lost',
                    'found_items',
                    'found_unclaimed',
                    'in_storage',
                    'returned_items',
                    'claimed_items',
                    'recovery_rate_percentage',
                ],
                'sparklines',
                'analytics' => [
                    'period',
                    'date_from',
                    'date_to',
                    'timeline' => [
                        'labels',
                        'lost',
                        'found',
                        'returned',
                    ],
                    'lifecycle_distribution',
                ],
            ]);
    }

    public function test_lifecycle_distribution_sums_to_period_total_items(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $campus = Campus::factory()->create();
        $category = Category::factory()->create();
        $reporter = User::factory()->create();

        // Create items across distinct lifecycle states
        // 1. Returned item
        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'found',
            'status' => 'returned',
            'is_deleted' => false,
            'created_at' => now()->subDays(10),
        ]);

        // 2. Claimed item
        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'found',
            'status' => 'claimed',
            'is_deleted' => false,
            'created_at' => now()->subDays(15),
        ]);

        // 3. Closed / Withdrawn / Expired items
        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'lost',
            'status' => 'withdrawn',
            'is_deleted' => false,
            'created_at' => now()->subDays(20),
        ]);

        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'found',
            'status' => 'expired',
            'is_deleted' => false,
            'created_at' => now()->subDays(25),
        ]);

        // 4. Lost Active (matched / lost / reported)
        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'lost',
            'status' => 'lost',
            'is_deleted' => false,
            'created_at' => now()->subDays(5),
        ]);

        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'lost',
            'status' => 'matched',
            'is_deleted' => false,
            'created_at' => now()->subDays(3),
        ]);

        // 5. Found in Custody / Unclaimed (in_custody / stored / found_unclaimed)
        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'found',
            'status' => 'in_custody',
            'is_deleted' => false,
            'created_at' => now()->subDays(8),
        ]);

        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'is_deleted' => false,
            'created_at' => now()->subDays(2),
        ]);

        $service = app(DashboardStatisticsService::class);
        $data = $service->getStatistics('90d');

        $totalItems = Item::where('is_deleted', false)->whereBetween('created_at', [now()->subDays(89)->startOfDay(), now()])->count();
        $this->assertEquals(8, $totalItems);

        $lifecycle = $data['analytics']['lifecycle_distribution'];
        $sumLifecycle = array_sum(array_column($lifecycle, 'count'));

        $this->assertEquals(
            $totalItems,
            $sumLifecycle,
            "Lifecycle distribution sum ({$sumLifecycle}) must equal total period items ({$totalItems})."
        );
    }

    public function test_all_time_trend_does_not_generate_future_dates(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $campus = Campus::factory()->create();
        $category = Category::factory()->create();
        $reporter = User::factory()->create();

        // Create item 18 months ago to verify uncapped All Time behavior
        Item::factory()->create([
            'campus_id' => $campus->id,
            'category_id' => $category->id,
            'reporter_id' => $reporter->id,
            'type' => 'found',
            'status' => 'found_unclaimed',
            'created_at' => now()->subMonths(18),
        ]);

        $service = app(DashboardStatisticsService::class);
        $data = $service->getStatistics('all');

        $this->assertNotEmpty($data['analytics']['timeline']['labels']);

        // Check that all labels or timeline data are within now()
        $nowMonth = now()->format('M Y');
        $labels = $data['analytics']['timeline']['labels'];
        $lastLabel = end($labels);

        $this->assertEquals($nowMonth, $lastLabel, "Last timeline label must be current month ({$nowMonth}), got: {$lastLabel}");
    }
}
