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
            'similarity_score' => $this->similarity_score,
            'match_reasons' => $this->match_reasons,
            'status' => $this->status,
            'lost_item' => new ItemResource($this->whenLoaded('lostItem')),
            'found_item' => new ItemResource($this->whenLoaded('foundItem')),
            'reviewed_by' => new UserResource($this->whenLoaded('reviewedByUser')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
