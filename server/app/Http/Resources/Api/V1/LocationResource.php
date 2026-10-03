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
            'name_am' => $this->name_am,
            'code' => $this->code,
            'building' => $this->building,
            'zone' => $this->zone,
            'floor' => $this->zone, // backward compat alias for frontend table
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'campus' => $this->whenLoaded('campus', fn() => new CampusResource($this->campus)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
