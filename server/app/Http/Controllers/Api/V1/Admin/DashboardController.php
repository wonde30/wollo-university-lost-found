<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Administration\Services\DashboardStatisticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardStatisticsService $statisticsService
    ) {}

    /**
     * Dashboard statistics & multi-dimensional analytics endpoint.
     * Cached with 60-second TTL keyed by period parameters.
     */
    public function statistics(Request $request): JsonResponse
    {
        $period   = (string) $request->query('period', '90d');
        $dateFrom = $request->query('date_from') ? (string) $request->query('date_from') : null;
        $dateTo   = $request->query('date_to') ? (string) $request->query('date_to') : null;
        $force    = $request->boolean('force');

        $cacheKey = 'admin.statistics.' . md5($period . ($dateFrom ?? '') . ($dateTo ?? ''));

        if ($force) {
            Cache::forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, 60, function () use ($period, $dateFrom, $dateTo) {
            return $this->statisticsService->getStatistics($period, $dateFrom, $dateTo);
        });

        return response()->json($data);
    }
}
