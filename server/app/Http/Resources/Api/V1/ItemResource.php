<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_code' => $this->reference_code,
            'reporter_id' => $this->reporter_id,
            'user_id' => $this->reporter_id,
            'campus_id' => $this->campus_id,
            'category_id' => $this->category_id,
            'location_id' => $this->location_id,
            'location_detail' => $this->location_detail,
            'type' => (string) $this->type,
            'status' => (string) $this->status,
            'held_at' => $this->held_at,
            'title' => $this->title,
            'description' => $this->description,
            'brand' => $this->brand,
            'color' => $this->color,
            'serial_number' => $this->serial_number,
            'incident_date' => $this->incident_date?->format('Y-m-d'),
            'incident_time' => $this->incident_time,
            'estimated_value' => $this->estimated_value,
            'is_high_value' => (bool) $this->is_high_value,
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'views_count' => $this->whenCounted('views'),
            'primary_photo' => $this->whenLoaded('photos', fn () => new ItemPhotoResource($this->photos->firstWhere('is_primary', true) ?? $this->photos->first())),
            'photos' => ItemPhotoResource::collection($this->whenLoaded('photos')),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->pluck('tag')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'user' => new UserResource($this->whenLoaded('reporter')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
