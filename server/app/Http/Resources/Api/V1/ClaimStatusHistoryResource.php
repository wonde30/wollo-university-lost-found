<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'claim_id' => $this->claim_id,
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'comment' => $this->comment,
            'changed_by' => new UserResource($this->whenLoaded('changedByUser')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
