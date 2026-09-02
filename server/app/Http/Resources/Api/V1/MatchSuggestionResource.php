<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchSuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lost_item_id' => $this->lost_item_id,
            'found_item_id' => $this->found_item_id,
            'score' => (float) $this->score,
            'category_score' => (float) $this->category_score,
            'text_score' => (float) $this->text_score,
            'location_score' => (float) $this->location_score,
            'algorithm_version' => $this->algorithm_version ?? 'v1.0',
            'status' => $this->status,
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'lost_item' => new ItemResource($this->whenLoaded('lostItem')),
            'found_item' => new ItemResource($this->whenLoaded('foundItem')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
