<?php

namespace App\Domain\Administration\Services;

use App\Models\Claim;
use App\Models\Item;
use App\Models\ReturnRecord;
use App\Models\User;

class DashboardStatisticsService
{
    public function getStatistics(): array
    {
        $totalItems = Item::count();
        $lostItems = Item::where('type', 'lost')->count();
        $foundItems = Item::where('type', 'found')->count();
        $returnedItems = Item::where('status', 'returned')->count();

        return [
            'total_items' => $totalItems,
            'lost_items' => $lostItems,
            'found_items' => $foundItems,
            'in_storage' => Item::where('status', 'in_storage')->count(),
            'returned_items' => $returnedItems,
            'pending_claims' => Claim::where('status', 'pending')->count(),
            'total_users' => User::count(),
            'recovery_rate' => $foundItems > 0 ? round(($returnedItems / $foundItems) * 100, 2) : 0,
        ];
    }
}
