<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustodyEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'event_type' => $this->event_type instanceof \BackedEnum ? $this->event_type->value : (string) $this->event_type,
            'condition' => $this->condition,
            'notes' => $this->notes,
            'reference_photo' => $this->reference_photo ? asset('storage/' . $this->reference_photo) : null,
            'item' => new ItemResource($this->whenLoaded('item')),
            'actor' => new UserResource($this->whenLoaded('actor')),
            'performed_by' => new UserResource($this->whenLoaded('actor')),
            'storage_location' => new StorageLocationResource($this->whenLoaded('storageLocation')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
