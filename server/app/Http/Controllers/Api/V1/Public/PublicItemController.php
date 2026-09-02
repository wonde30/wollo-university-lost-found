<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PublicItemResource;
use App\Models\Item;
use App\Models\ItemView;
use App\Models\SearchLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, $request->integer('per_page', 10)));
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? ''));

        $items = Item::with(['category', 'location', 'photos'])
            ->where('is_deleted', false)
            ->where('status', '!=', 'withdrawn')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('campus_id'), fn ($q) => $q->where('campus_id', $request->integer('campus_id')))
            ->when($request->filled('location_id'), fn ($q) => $q->where('location_id', $request->integer('location_id')))
            ->when(strlen($search) >= 3, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('reference_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('tag'), function ($q) use ($request) {
                $tag = strtolower(trim($request->string('tag')->toString()));
                $q->whereHas('tags', fn ($sub) => $sub->where('tag', $tag));
            })
            ->when($request->query('sort') === 'oldest', fn ($q) => $q->orderBy('created_at', 'asc'))
            ->when($request->query('sort') === 'category_az', fn ($q) => $q->join('categories', 'items.category_id', '=', 'categories.id')->orderBy('categories.name', 'asc')->select('items.*'))
            ->when(! in_array($request->query('sort'), ['oldest', 'category_az'], true), fn ($q) => $q->orderByDesc('created_at'))
            ->paginate($perPage);

        // Record search log if a search query was performed
        if (strlen($search) >= 3) {
            SearchLog::create([
                'user_id'       => auth()->id(),
                'query'         => $search,
                'category_id'   => $request->integer('category_id') ?: null,
                'campus_id'     => $request->integer('campus_id') ?: null,
                'results_count' => $items->total(),
                'ip_address'    => $request->ip(),
            ]);
        }

        return response()->json([
            'data' => PublicItemResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'from'         => $items->firstItem(),
                'to'           => $items->lastItem(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $item = Item::with(['category', 'location', 'photos', 'tags'])
            ->where('is_deleted', false)
            ->where('status', '!=', 'withdrawn')
            ->findOrFail($id);

        // FR-66: Record view
        ItemView::create([
            'item_id' => $item->id,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'viewed_at' => now(),
        ]);

        return response()->json([
            'data' => new PublicItemResource($item),
        ]);
    }
}
