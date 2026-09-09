<?php

declare(strict_types=1);

namespace App\Domain\Administration\Services;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\Item;
use App\Models\MatchSuggestion;
use App\Models\ReturnRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatisticsService
{
    /**
     * Generate complete institutional dashboard statistics with multi-dimensional analytics.
     *
     * @param string      $period   'today' | '7d' | '30d' | '90d' | '12m' | 'all'
     * @param string|null $dateFrom Optional custom start date (Y-m-d)
     * @param string|null $dateTo   Optional custom end date (Y-m-d)
     * @return array<string, mixed>
     */
    public function getStatistics(string $period = '90d', ?string $dateFrom = null, ?string $dateTo = null): array
    {
        // 1. Resolve date boundaries
        [$startDate, $endDate, $intervalCount, $intervalUnit] = $this->resolveDateRange($period, $dateFrom, $dateTo);

        // 2. All-Time Registry Total (Reflects all registered items regardless of period)
        $totalItems = DB::table('items')->where('is_deleted', false)->count();

        // Consolidated Item KPI Metrics (Filtered by active period boundaries)
        $itemStats = DB::table('items')
            ->where('is_deleted', false)
            ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
            ->selectRaw("
                SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) as lost_items,
                SUM(CASE WHEN type = 'lost' AND status IN ('reported','lost','matched','pending_verification') THEN 1 ELSE 0 END) as active_lost,
                SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) as found_items,
                SUM(CASE WHEN type = 'found' AND status IN ('reported','received','stored','found_unclaimed','in_custody','pending_surrender') THEN 1 ELSE 0 END) as found_unclaimed,
                SUM(CASE WHEN (held_at = 'security_office' OR status IN ('found_unclaimed','stored','received','in_custody')) AND status != 'returned' THEN 1 ELSE 0 END) as in_storage,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_items,
                SUM(CASE WHEN status IN ('claimed','found_claimed') THEN 1 ELSE 0 END) as claimed_items,
                SUM(CASE WHEN status = 'found_unclaimed' AND expires_at IS NOT NULL AND expires_at <= ? THEN 1 ELSE 0 END) as expiring_items
            ", [now()->addDays(7)->toDateTimeString()])
            ->first();

        $lostItems      = (int) ($itemStats->lost_items ?? 0);
        $activeLost     = (int) ($itemStats->active_lost ?? 0);
        $foundItems     = (int) ($itemStats->found_items ?? 0);
        $foundUnclaimed = (int) ($itemStats->found_unclaimed ?? 0);
        $inStorage      = (int) ($itemStats->in_storage ?? 0);
        $returnedItems  = (int) ($itemStats->returned_items ?? 0);
        $claimedItems   = (int) ($itemStats->claimed_items ?? 0);
        $expiringItems  = (int) ($itemStats->expiring_items ?? 0);

        // 3. Consolidated Return Metrics
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $returnStats = DB::table('returns')
            ->whereBetween('return_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("
                SUM(CASE WHEN return_date >= ? AND return_date <= ? THEN 1 ELSE 0 END) as returned_this_month,
                SUM(CASE WHEN recipient_confirmed = 0 THEN 1 ELSE 0 END) as unconfirmed_returns
            ", [$startOfMonth, $endOfMonth])
            ->first();

        $returnedThisMonth  = (int) ($returnStats->returned_this_month ?? 0);
        $unconfirmedReturns = (int) ($returnStats->unconfirmed_returns ?? 0);

        // 4. Other Operational Counts (Respected period date range)
        $pendingClaims  = Claim::where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $pendingMatches = MatchSuggestion::where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalUsers     = User::count();

        // 5. Recovery Rate
        $recoveryRate = $foundItems > 0 ? round(($returnedItems / $foundItems) * 100, 2) : 0.0;

        // 6. Turnaround Efficiency (Driver Portable: MySQL vs SQLite)
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $diffExpr = $isSqlite
            ? 'ROUND(AVG(ABS(julianday(last_activity_at) - julianday(created_at))), 1)'
            : 'ROUND(AVG(ABS(DATEDIFF(last_activity_at, created_at))), 1)';

        $avgResolutionDays = DB::table('items')
            ->where('status', 'returned')
            ->where('is_deleted', false)
            ->whereNotNull('last_activity_at')
            ->selectRaw("{$diffExpr} as avg_days")
            ->value('avg_days');

        // 7. Search Fail Rate
        $searchStats = DB::table('search_logs')
            ->selectRaw("
                COUNT(*) as total_searches,
                SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) as failed_searches
            ")
            ->first();

        $totalSearches = (int) ($searchStats->total_searches ?? 0);
        $failedSearches = (int) ($searchStats->failed_searches ?? 0);
        $searchFailRate = $totalSearches > 0 ? round(($failedSearches / $totalSearches) * 100, 2) : 0.0;

        // 8. Top 3 Categories (for summary backward compatibility)
        $topCategories = Item::where('is_deleted', false)
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(3)
            ->with('category:id,name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->category_id,
                'name' => $item->category->name ?? 'Unknown',
                'total' => $item->total,
            ])
            ->values()
            ->all();

        // 9. Time-Series Timeline Buckets
        $intervals = $this->generateIntervals($startDate, $endDate, $intervalCount, $intervalUnit);
        $timelineData = $this->buildTimelineAggregation($intervals);

        // 10. Sparkline Data Points for Top 5 KPI Cards (Last 7 daily bins)
        $sparklineIntervals = $this->generateIntervals(now()->subDays(6)->startOfDay(), now()->endOfDay(), 7, 'day');
        $sparklines = $this->buildSparklineAggregation($sparklineIntervals);

        // 11. Campus-by-Campus Breakdown
        $byCampus = $this->buildCampusAggregation($startDate, $endDate);

        // 12. Full Category Breakdown (with percentages)
        $byCategory = $this->buildCategoryAggregation($totalItems, $startDate, $endDate);

        // 13. Lifecycle Status Funnel
        $statusFunnel = $this->buildStatusFunnelAggregation($totalItems, $startDate, $endDate);

        // 14. Activity Feeds (Indexed & optimized selects)
        $recentItems = Item::where('is_deleted', false)
            ->latest()
            ->take(5)
            ->get(['id', 'reference_code', 'title', 'type', 'status', 'created_at']);

        $recentClaims = Claim::with('item:id,title,reference_code')
            ->latest()
            ->take(5)
            ->get(['id', 'item_id', 'claimant_id', 'status', 'created_at']);

        $recentReturns = ReturnRecord::with(['item' => fn ($q) => $q->select('items.id', 'items.title', 'items.reference_code')])
            ->latest()
            ->take(5)
            ->get(['id', 'claim_id', 'returned_to', 'handed_over_by', 'return_date', 'created_at']);

        return [
            'summary' => [
                'total_items'                 => $totalItems,
                'lost_items'                  => $lostItems,
                'active_lost'                 => $activeLost,
                'found_items'                 => $foundItems,
                'found_unclaimed'             => $foundUnclaimed,
                'in_storage'                  => $inStorage,
                'returned_items'              => $returnedItems,
                'claimed_items'               => $claimedItems,
                'returned_this_month'         => $returnedThisMonth,
                'pending_claims'              => $pendingClaims,
                'pending_matches'             => $pendingMatches,
                'expiring_items'              => $expiringItems,
                'unconfirmed_returns'         => $unconfirmedReturns,
                'total_users'                 => $totalUsers,
                'recovery_rate_percentage'    => $recoveryRate,
                'avg_resolution_days'         => $avgResolutionDays !== null ? (float) $avgResolutionDays : null,
                'top_3_categories'            => $topCategories,
                'search_fail_rate_percentage' => $searchFailRate,
            ],
            'sparklines' => $sparklines,
            'analytics' => [
                'period'                 => $period,
                'date_from'              => $startDate->toDateString(),
                'date_to'                => $endDate->toDateString(),
                'timeline'               => $timelineData,
                'by_category'            => $byCategory,
                'by_campus'              => $byCampus,
                'status_funnel'          => $statusFunnel,
                'lifecycle_distribution' => $this->buildLifecycleDistribution($startDate, $endDate),
            ],
            'recent_activity' => [
                'recent_items'   => $recentItems,
                'recent_claims'  => $recentClaims,
                'recent_returns' => $recentReturns,
            ],
        ];
    }

    /**
     * Resolve date range parameters into start, end, interval count, and unit.
     */
    protected function resolveDateRange(string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $now = now();

        if ($dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end = Carbon::parse($dateTo)->endOfDay();
            $diffDays = $start->diffInDays($end);

            if ($diffDays <= 1) {
                return [$start, $end, 6, 'hour'];
            } elseif ($diffDays <= 14) {
                return [$start, $end, (int) max($diffDays, 1), 'day'];
            } elseif ($diffDays <= 90) {
                return [$start, $end, 6, 'interval'];
            } else {
                return [$start, $end, (int) min(max($start->diffInMonths($end), 2), 12), 'month'];
            }
        }

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy(),
                6,
                'hour',
            ],
            '7d' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy(),
                7,
                'day',
            ],
            '30d' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy(),
                6,
                'interval',
            ],
            '90d' => [
                $now->copy()->subDays(89)->startOfDay(),
                $now->copy(),
                6,
                'interval',
            ],
            '12m' => [
                $now->copy()->subMonths(11)->startOfMonth(),
                $now->copy(),
                12,
                'month',
            ],
            'all' => (function () use ($now) {
                $minCreatedAt = Item::where('is_deleted', false)->min('created_at');
                $start = $minCreatedAt
                    ? Carbon::parse($minCreatedAt)->startOfMonth()
                    : $now->copy()->subMonths(5)->startOfMonth();
                $end = $now->copy();
                $monthCount = max(1, (int) $start->diffInMonths($end) + 1);

                return [$start, $end, $monthCount, 'month'];
            })(),
            default => [
                $now->copy()->subDays(89)->startOfDay(),
                $now->copy(),
                6,
                'interval',
            ],
        };
    }

    /**
     * Generate discrete timeline intervals.
     */
    protected function generateIntervals(Carbon $start, Carbon $end, int $count, string $unit): array
    {
        $intervals = [];
        $now = now();

        if ($unit === 'month') {
            $current = $start->copy()->startOfMonth();

            for ($i = 0; $i < $count; $i++) {
                $currentStart = $current->copy();
                $currentEnd = $current->copy()->endOfMonth();

                // Never project beyond $end or now()
                if ($currentStart->gt($end) || $currentStart->gt($now)) {
                    break;
                }
                if ($currentEnd->gt($end)) {
                    $currentEnd = $end->copy();
                }
                if ($currentEnd->gt($now)) {
                    $currentEnd = $now->copy();
                }

                $intervals[] = [
                    'label' => $currentStart->format('M Y'),
                    'start' => $currentStart->toDateTimeString(),
                    'end'   => $currentEnd->toDateTimeString(),
                ];

                $current->addMonth()->startOfMonth();
                if ($current->gt($end) || $current->gt($now)) {
                    break;
                }
            }

            return $intervals;
        }

        $totalSeconds = max(1, abs($end->timestamp - $start->timestamp));
        $stepSeconds = max(1, (int) round($totalSeconds / max($count, 1)));

        for ($i = 0; $i < $count; $i++) {
            $currentStart = $start->copy()->addSeconds($i * $stepSeconds);
            $currentEnd = ($i === $count - 1) ? $end->copy() : $start->copy()->addSeconds(($i + 1) * $stepSeconds);

            if ($currentStart->gt($now) || $currentStart->gt($end)) {
                break;
            }
            if ($currentEnd->gt($now)) {
                $currentEnd = $now->copy();
            }

            // Format clean label
            $label = match ($unit) {
                'hour' => $currentStart->format('H:i'),
                'day' => $currentStart->format('M d'),
                'month' => $currentStart->format('M Y'),
                default => $currentStart->format('M d'),
            };

            $intervals[] = [
                'label' => $label,
                'start' => $currentStart->toDateTimeString(),
                'end'   => $currentEnd->toDateTimeString(),
            ];
        }

        return $intervals;
    }

    /**
     * Build single-query timeline aggregation for lost, found, and returned items.
     */
    protected function buildTimelineAggregation(array $intervals): array
    {
        if (empty($intervals)) {
            return ['labels' => [], 'lost' => [], 'found' => [], 'returned' => []];
        }

        $labels = [];
        $itemSelects = [];
        $itemBindings = [];
        $returnSelects = [];
        $returnBindings = [];

        foreach ($intervals as $i => $interval) {
            $labels[] = $interval['label'];

            $itemSelects[] = "SUM(CASE WHEN type = 'lost' AND created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END) as lost_{$i}";
            $itemSelects[] = "SUM(CASE WHEN type = 'found' AND created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END) as found_{$i}";
            $itemBindings[] = $interval['start'];
            $itemBindings[] = $interval['end'];
            $itemBindings[] = $interval['start'];
            $itemBindings[] = $interval['end'];

            $returnSelects[] = "SUM(CASE WHEN return_date >= ? AND return_date <= ? THEN 1 ELSE 0 END) as return_{$i}";
            $returnBindings[] = substr($interval['start'], 0, 10);
            $returnBindings[] = substr($interval['end'], 0, 10);
        }

        $itemQuery = DB::table('items')
            ->where('is_deleted', false)
            ->selectRaw(implode(', ', $itemSelects), $itemBindings)
            ->first();

        $returnQuery = DB::table('returns')
            ->selectRaw(implode(', ', $returnSelects), $returnBindings)
            ->first();

        $lostData = [];
        $foundData = [];
        $returnedData = [];

        foreach ($intervals as $i => $interval) {
            $lostKey = "lost_{$i}";
            $foundKey = "found_{$i}";
            $returnKey = "return_{$i}";

            $lostData[] = (int) ($itemQuery->$lostKey ?? 0);
            $foundData[] = (int) ($itemQuery->$foundKey ?? 0);
            $returnedData[] = (int) ($returnQuery->$returnKey ?? 0);
        }

        return [
            'labels'   => $labels,
            'lost'     => $lostData,
            'found'    => $foundData,
            'returned' => $returnedData,
        ];
    }

    /**
     * Build sparkline 7-point data series for top KPI widgets.
     */
    protected function buildSparklineAggregation(array $intervals): array
    {
        $timeline = $this->buildTimelineAggregation($intervals);

        $totalSeries = [];
        $inStorageSeries = [];

        $storageSelects = [];
        $storageBindings = [];
        foreach ($intervals as $i => $interval) {
            $storageSelects[] = "SUM(CASE WHEN held_at = 'security_office' AND created_at <= ? THEN 1 ELSE 0 END) as storage_{$i}";
            $storageBindings[] = $interval['end'];
        }

        $storageQuery = DB::table('items')
            ->where('is_deleted', false)
            ->selectRaw(implode(', ', $storageSelects), $storageBindings)
            ->first();

        foreach ($intervals as $i => $interval) {
            $storageKey = "storage_{$i}";
            $lostVal = $timeline['lost'][$i] ?? 0;
            $foundVal = $timeline['found'][$i] ?? 0;
            $totalSeries[] = $lostVal + $foundVal;
            $inStorageSeries[] = (int) ($storageQuery->$storageKey ?? 0);
        }

        return [
            'total_items'    => $totalSeries,
            'lost_items'     => $timeline['lost'],
            'found_items'    => $timeline['found'],
            'returned_items' => $timeline['returned'],
            'in_storage'     => $inStorageSeries,
        ];
    }

    /**
     * Group items & recovery performance by active campus.
     */
    protected function buildCampusAggregation(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $campuses = Campus::where('is_active', true)->get(['id', 'name', 'short_code']);

        $campusStats = DB::table('items')
            ->where('is_deleted', false)
            ->whereNotNull('campus_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                campus_id,
                COUNT(*) as total_items,
                SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) as found_items,
                SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) as lost_items,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_items
            ")
            ->groupBy('campus_id')
            ->get()
            ->keyBy('campus_id');

        return $campuses->map(function (Campus $campus) use ($campusStats) {
            $stat = $campusStats->get($campus->id);
            $total = (int) ($stat->total_items ?? 0);
            $found = (int) ($stat->found_items ?? 0);
            $lost = (int) ($stat->lost_items ?? 0);
            $returned = (int) ($stat->returned_items ?? 0);
            $rate = $found > 0 ? round(($returned / $found) * 100, 1) : 0.0;

            return [
                'id'            => $campus->id,
                'name'          => $campus->name,
                'code'          => $campus->short_code ?? '',
                'total_items'   => $total,
                'found_items'   => $found,
                'lost_items'    => $lost,
                'returned_items'=> $returned,
                'recovery_rate' => $rate,
            ];
        })->values()->all();
    }

    /**
     * Group items by category with percentages of total inventory.
     */
    protected function buildCategoryAggregation(int $totalItems, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $categories = Category::where('is_active', true)
            ->get(['id', 'name', 'icon_slug']);

        $catStats = DB::table('items')
            ->where('is_deleted', false)
            ->whereNotNull('category_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                category_id,
                COUNT(*) as total_items,
                SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) as found_items,
                SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) as lost_items,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_items
            ")
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $periodTotal = $catStats->sum('total_items');

        return $categories->map(function (Category $category) use ($catStats, $periodTotal) {
            $stat = $catStats->get($category->id);
            $total = (int) ($stat->total_items ?? 0);
            $found = (int) ($stat->found_items ?? 0);
            $lost = (int) ($stat->lost_items ?? 0);
            $returned = (int) ($stat->returned_items ?? 0);
            $pct = $periodTotal > 0 ? round(($total / $periodTotal) * 100, 1) : 0.0;

            return [
                'id'            => $category->id,
                'name'          => $category->name,
                'icon'          => $category->icon_slug ?? '',
                'total_items'   => $total,
                'found_items'   => $found,
                'lost_items'    => $lost,
                'returned_items'=> $returned,
                'percentage'    => $pct,
            ];
        })
        ->filter(fn ($c) => $c['total_items'] > 0)
        ->sortByDesc('total_items')
        ->values()
        ->all();
    }

    /**
     * Build item lifecycle funnel pipeline stages.
     */
    protected function buildStatusFunnelAggregation(int $totalItems, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $funnelStats = DB::table('items')
            ->where('is_deleted', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                COUNT(*) as reported,
                SUM(CASE WHEN status IN ('in_storage','stored','received','found_unclaimed') OR held_at = 'security_office' THEN 1 ELSE 0 END) as in_custody,
                SUM(CASE WHEN status IN ('claimed','returned') THEN 1 ELSE 0 END) as claimed,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned
            ")
            ->first();

        $reported = (int) ($funnelStats->reported ?? 0);
        $custody  = (int) ($funnelStats->in_custody ?? 0);
        $claimed  = (int) ($funnelStats->claimed ?? 0);
        $returned = (int) ($funnelStats->returned ?? 0);

        return [
            [
                'stage'      => 'reported',
                'label'      => 'Items Reported',
                'count'      => $reported,
                'percentage' => 100.0,
            ],
            [
                'stage'      => 'in_custody',
                'label'      => 'In Custody / Stored',
                'count'      => $custody,
                'percentage' => $reported > 0 ? round(($custody / $reported) * 100, 1) : 0.0,
            ],
            [
                'stage'      => 'claimed',
                'label'      => 'Claimed & Verified',
                'count'      => $claimed,
                'percentage' => $reported > 0 ? round(($claimed / $reported) * 100, 1) : 0.0,
            ],
            [
                'stage'      => 'returned',
                'label'      => 'Returned to Owner',
                'count'      => $returned,
                'percentage' => $reported > 0 ? round(($returned / $reported) * 100, 1) : 0.0,
            ],
        ];
    }

    /**
     * Build discrete, non-overlapping lifecycle distribution segments where sum equals period items.
     *
     * @return array<int, array{id: string, label: string, count: int, color: string}>
     */
    protected function buildLifecycleDistribution(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $rawDistribution = DB::query()->fromSub(function ($query) use ($startDate, $endDate) {
            $query->from('items')
                ->where('is_deleted', false)
                ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->selectRaw("
                    CASE
                        WHEN status = 'returned' THEN 'returned'
                        WHEN status IN ('claimed', 'found_claimed') THEN 'claimed'
                        WHEN status IN ('closed', 'withdrawn', 'expired', 'disposed', 'cancelled') THEN 'closed'
                        WHEN type = 'lost' THEN 'lost'
                        ELSE 'found_unclaimed'
                    END as lifecycle_stage
                ");
        }, 'classified_items')
        ->selectRaw("
            lifecycle_stage,
            COUNT(*) as stage_count
        ")
        ->groupBy('lifecycle_stage')
        ->pluck('stage_count', 'lifecycle_stage');

        $distribution = [
            [
                'id'    => 'lost',
                'label' => 'Lost (Active)',
                'count' => (int) ($rawDistribution->get('lost') ?? 0),
                'color' => 'var(--wu-danger-500, #f43f5e)',
            ],
            [
                'id'    => 'found_unclaimed',
                'label' => 'Found (Unclaimed)',
                'count' => (int) ($rawDistribution->get('found_unclaimed') ?? 0),
                'color' => 'var(--wu-info-500, #3b82f6)',
            ],
            [
                'id'    => 'claimed',
                'label' => 'Claimed & Verifying',
                'count' => (int) ($rawDistribution->get('claimed') ?? 0),
                'color' => 'var(--wu-warning-500, #f59e0b)',
            ],
            [
                'id'    => 'returned',
                'label' => 'Returned to Owner',
                'count' => (int) ($rawDistribution->get('returned') ?? 0),
                'color' => 'var(--wu-success-500, #10b981)',
            ],
        ];

        $closedCount = (int) ($rawDistribution->get('closed') ?? 0);
        if ($closedCount > 0) {
            $distribution[] = [
                'id'    => 'closed',
                'label' => 'Closed / Other',
                'count' => $closedCount,
                'color' => 'var(--wu-slate-400, #94a3b8)',
            ];
        }

        return $distribution;
    }
}
