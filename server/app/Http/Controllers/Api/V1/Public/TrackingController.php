<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    public function track(string $referenceCode): JsonResponse
    {
        $item = Item::with(['category', 'campus'])
            ->where('reference_code', $referenceCode)
            ->where('is_deleted', false)
            ->firstOrFail();

        return response()->json([
            'reference_code' => $item->reference_code,
            'title' => $item->title,
            'category' => $item->category?->name,
            'campus' => $item->campus?->name,
            'status' => (string) $item->status,
            'incident_date' => $item->incident_date?->format('Y-m-d'),
        ]);
    }
}
