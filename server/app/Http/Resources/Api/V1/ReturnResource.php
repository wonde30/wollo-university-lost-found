<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'claim_id' => $this->claim_id,
            'item_id' => $this->item_id,
            'returned_to' => $this->returned_to,
            'handed_over_by' => $this->handed_over_by,
            'storage_location_id' => $this->storage_location_id,
            'return_date' => $this->return_date?->format('Y-m-d'),
            'return_time' => $this->return_time,
            'condition_on_return' => $this->condition_on_return,
            'notes' => $this->notes,
            'recipient_confirmed' => $this->recipient_confirmed,
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'confirmation_token' => $this->confirmation_token,
            'confirmation_token_expires_at' => $this->confirmation_token_expires_at?->toISOString(),
            'item' => new ItemResource($this->whenLoaded('item')),
            'claim' => new ClaimResource($this->whenLoaded('claim')),
            'recipient' => new UserResource($this->whenLoaded('recipient')),
            'staff' => new UserResource($this->whenLoaded('staff')),
            'storage_location' => new StorageLocationResource($this->whenLoaded('storageLocation')),
            'documents' => ReturnDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
