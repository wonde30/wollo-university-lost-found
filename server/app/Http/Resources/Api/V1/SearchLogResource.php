<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'search_query' => $this->search_query,
            'filters_applied' => $this->filters_applied,
            'results_count' => $this->results_count,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
