<?php

namespace App\Domain\Items\Services;

use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemSearchService
{
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Item::with(['category', 'location', 'photos'])->whereIn('status', ['open', 'in_storage', 'reported']);

        if (!empty($filters['query'])) {
            $q = $filters['query'];
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('reference_code', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }
}
