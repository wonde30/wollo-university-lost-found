<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Public landing-page statistics.
 *
 * Exposes ONLY safe, aggregate counts — no PII, no moderation data,
 * no internal audit information.  Cached for 5 minutes to keep the
 * landing page snappy without hammering the database.
 */
class PublicStatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Cache::remember('public.landing.statistics', 300, function (): array {
            $itemStats = DB::table('items')
                ->where('is_deleted', false)
                ->selectRaw("
                    COUNT(*) as items_reported,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as items_returned
                ")
                ->first();

            $communityMembers = DB::table('users')
                ->where('is_active', true)
                ->count();

            $campuses = DB::table('campuses')
                ->where('is_active', true)
                ->count();

            return [
                'items_reported'    => (int) ($itemStats->items_reported ?? 0),
                'items_returned'    => (int) ($itemStats->items_returned ?? 0),
                'community_members' => $communityMembers,
                'campuses'          => $campuses,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
