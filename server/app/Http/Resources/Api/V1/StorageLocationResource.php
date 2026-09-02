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
            'code' => $this->code ?? $this->shelf_cabinet_code ?? '',
            'description' => $this->description,
            'building' => $this->building,
            'room_number' => $this->room_number,
            'shelf_cabinet_code' => $this->shelf_cabinet_code ?? $this->code,
            'capacity' => $this->capacity,
            'current_occupancy' => $this->current_occupancy ?? 0,
            'status' => $this->status ?? ($this->is_active ? 'active' : 'inactive'),
            'is_active' => (bool) ($this->is_active ?? ($this->status !== 'maintenance')),
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
