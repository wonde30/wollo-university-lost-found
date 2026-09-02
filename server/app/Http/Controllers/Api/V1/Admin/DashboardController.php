<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Item;
use App\Models\MatchSuggestion;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard statistics endpoint with 60-second caching.
     */
    public function statistics(): JsonResponse
    {
        $data = Cache::remember('admin.statistics', 60, function () {
            // 1. Consolidated item statistics — 8 separate COUNT queries → 1 aggregate query
            $itemStats = DB::table('items')
                ->where('is_deleted', false)
                ->selectRaw("
                    COUNT(*) as total_items,
                    SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) as lost_items,
                    SUM(CASE WHEN type = 'lost' AND status IN ('reported','lost') THEN 1 ELSE 0 END) as active_lost,
                    SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) as found_items,
                    SUM(CASE WHEN type = 'found' AND status IN ('reported','received','stored','found_unclaimed') THEN 1 ELSE 0 END) as found_unclaimed,
                    SUM(CASE WHEN held_at = 'security_office' AND status IN ('found_unclaimed','stored','received') THEN 1 ELSE 0 END) as in_storage,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_items,
                    SUM(CASE WHEN status = 'found_unclaimed' AND expires_at IS NOT NULL AND expires_at <= ? THEN 1 ELSE 0 END) as expiring_items
                ", [now()->addDays(7)->toDateTimeString()])
                ->first();

            $totalItems     = (int) ($itemStats->total_items ?? 0);
            $lostItems      = (int) ($itemStats->lost_items ?? 0);
            $activeLost     = (int) ($itemStats->active_lost ?? 0);
            $foundItems     = (int) ($itemStats->found_items ?? 0);
            $foundUnclaimed = (int) ($itemStats->found_unclaimed ?? 0);
            $inStorage      = (int) ($itemStats->in_storage ?? 0);
            $returnedItems  = (int) ($itemStats->returned_items ?? 0);
            $expiringItems  = (int) ($itemStats->expiring_items ?? 0);

            // 2. Consolidated return record statistics — 2 queries → 1 (portable across SQLite and MySQL)
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth   = now()->endOfMonth()->toDateString();

            $returnStats = DB::table('returns')
                ->selectRaw("
                    SUM(CASE WHEN return_date >= ? AND return_date <= ? THEN 1 ELSE 0 END) as returned_this_month,
                    SUM(CASE WHEN recipient_confirmed = 0 THEN 1 ELSE 0 END) as unconfirmed_returns
                ", [$startOfMonth, $endOfMonth])
                ->first();

            $returnedThisMonth  = (int) ($returnStats->returned_this_month ?? 0);
            $unconfirmedReturns = (int) ($returnStats->unconfirmed_returns ?? 0);

            // 3. Other counts (separate tables — kept as individual fast queries)
            $pendingClaims  = Claim::where('status', 'pending')->count();
            $pendingMatches = MatchSuggestion::where('status', 'pending')->count();
            $totalUsers     = User::count();

            // 4. Recovery rate
            $returnRate = $foundItems > 0 ? round(($returnedItems / $foundItems) * 100, 2) : 0;

            // 5. Avg resolution days — SQL aggregate (driver-portable)
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

            // 6. Top 3 categories
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
                ]);

            // 7. Search fail rate — 2 queries → 1
            $searchStats = DB::table('search_logs')
                ->selectRaw("
                    COUNT(*) as total_searches,
                    SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) as failed_searches
                ")
                ->first();

            $totalSearches = (int) ($searchStats->total_searches ?? 0);
            $failedSearches = (int) ($searchStats->failed_searches ?? 0);
            $searchFailRate = $totalSearches > 0 ? round(($failedSearches / $totalSearches) * 100, 2) : 0;

            return [
                'summary' => [
                    'total_items'               => $totalItems,
                    'lost_items'                => $lostItems,
                    'active_lost'               => $activeLost,
                    'found_items'               => $foundItems,
                    'found_unclaimed'           => $foundUnclaimed,
                    'in_storage'                => $inStorage,
                    'returned_items'            => $returnedItems,
                    'returned_this_month'       => $returnedThisMonth,
                    'pending_claims'            => $pendingClaims,
                    'pending_matches'           => $pendingMatches,
                    'expiring_items'            => $expiringItems,
                    'unconfirmed_returns'       => $unconfirmedReturns,
                    'total_users'               => $totalUsers,
                    'recovery_rate_percentage'  => $returnRate,
                    'avg_resolution_days'       => $avgResolutionDays !== null ? (float) $avgResolutionDays : null,
                    'top_3_categories'          => $topCategories,
                    'search_fail_rate_percentage' => $searchFailRate,
                ],
                'recent_activity' => [
                    'recent_items'   => Item::where('is_deleted', false)->latest()->take(5)->get(['id', 'reference_code', 'title', 'type', 'status', 'created_at']),
                    'recent_claims'  => Claim::with('item:id,title,reference_code')->latest()->take(5)->get(['id', 'item_id', 'claimant_id', 'status', 'created_at']),
                    'recent_returns' => ReturnRecord::with(['item' => fn ($q) => $q->select('items.id', 'items.title', 'items.reference_code')])->latest()->take(5)->get(['id', 'claim_id', 'returned_to', 'handed_over_by', 'return_date', 'created_at']),
                ],
            ];
        });

        return response()->json($data);
    }
}


