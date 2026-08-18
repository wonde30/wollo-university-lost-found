<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorageLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campus_id' => $this->campus_id,
            'name' => $this->name,
            'building' => $this->building,
            'room_number' => $this->room_number,
            'shelf_cabinet_code' => $this->shelf_cabinet_code,
            'capacity' => $this->capacity,
            'current_occupancy' => $this->current_occupancy,
            'status' => $this->status,
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
