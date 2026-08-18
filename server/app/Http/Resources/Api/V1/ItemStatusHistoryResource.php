<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'changed_by' => new UserResource($this->whenLoaded('changedByUser')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
