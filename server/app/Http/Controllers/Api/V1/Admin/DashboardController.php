<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function statistics(): JsonResponse
    {
        $totalItems = Item::where('is_deleted', false)->count();
        $lostItems = Item::where('type', 'lost')->where('is_deleted', false)->count();
        $foundItems = Item::where('type', 'found')->where('is_deleted', false)->count();
        $inStorage = Item::where('held_at', 'security_office')->where('status', 'found_unclaimed')->where('is_deleted', false)->count();
        $returnedItems = Item::where('status', 'returned')->where('is_deleted', false)->count();
        $pendingClaims = Claim::where('status', 'pending')->count();
        $totalUsers = User::count();
        $returnRate = $foundItems > 0 ? round(($returnedItems / $foundItems) * 100, 2) : 0;

        return response()->json([
            'summary' => [
                'total_items' => $totalItems,
                'lost_items' => $lostItems,
                'found_items' => $foundItems,
                'in_storage' => $inStorage,
                'returned_items' => $returnedItems,
                'pending_claims' => $pendingClaims,
                'total_users' => $totalUsers,
                'recovery_rate_percentage' => $returnRate,
            ],
            'recent_activity' => [
                'recent_items' => Item::where('is_deleted', false)->latest()->take(5)->get(['id', 'reference_code', 'title', 'type', 'status', 'created_at']),
                'recent_claims' => Claim::latest()->take(5)->get(['id', 'item_id', 'claimant_id', 'status', 'created_at']),
                'recent_returns' => ReturnRecord::latest()->take(5)->get(['id', 'claim_id', 'item_id', 'returned_to', 'handed_over_by', 'return_date', 'created_at']),
            ],
        ]);
    }
}
