<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campus_id' => $this->campus_id,
            'name' => $this->name,
            'building' => $this->building,
            'floor' => $this->floor,
            'room_number' => $this->room_number,
            'coordinates' => $this->coordinates,
            'is_active' => (bool)$this->is_active,
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
