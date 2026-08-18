<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicItemResource;
use App\Models\Item;
use App\Models\SearchLog;
use App\Support\Enums\ItemStatus;
use App\Support\Enums\ItemType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Item::with(['category', 'location', 'photos'])
            ->where('type', ItemType::FOUND)
            ->where('status', ItemStatus::FOUND_UNCLAIMED)
            ->where('is_deleted', false);

        if ($search = $request->query('query')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });

            SearchLog::create([
                'user_id' => auth()->id(),
                'query' => $search,
                'category_id' => $request->integer('category_id') ?: null,
                'campus_id' => $request->integer('campus_id') ?: null,
                'results_count' => $query->count(),
                'ip_address' => $request->ip(),
            ]);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($locationId = $request->query('location_id')) {
            $query->where('location_id', $locationId);
        }

        $items = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => PublicItemResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $item = Item::with(['category', 'location', 'photos'])
            ->where('type', ItemType::FOUND)
            ->where('status', ItemStatus::FOUND_UNCLAIMED)
            ->where('is_deleted', false)
            ->findOrFail($id);

        return response()->json([
            'data' => new PublicItemResource($item),
        ]);
    }
}
