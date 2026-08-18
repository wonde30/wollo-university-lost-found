<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'claimant_id' => $this->claimant_id,
            'user_id' => $this->claimant_id,
            'explanation' => $this->explanation,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'reviewed_by' => $this->reviewed_by,
            'review_note' => $this->review_note,
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'auto_rejected' => (bool) $this->auto_rejected,
            'item' => new ItemResource($this->whenLoaded('item')),
            'claimant' => new UserResource($this->whenLoaded('claimant')),
            'user' => new UserResource($this->whenLoaded('claimant')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'evidence' => ClaimEvidenceResource::collection($this->whenLoaded('evidence')),
            'status_histories' => ClaimStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
