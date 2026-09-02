<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_code' => $this->reference_code,
            'type' => (string) $this->type,
            'status' => (string) $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'brand' => $this->brand,
            'color' => $this->color,
            'incident_date' => $this->incident_date?->format('Y-m-d'),
            'date_lost_found' => $this->incident_date?->format('Y-m-d'),
            'is_high_value' => (bool) $this->is_high_value,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'primary_photo' => $this->whenLoaded('photos', fn () => new ItemPhotoResource($this->photos->firstWhere('is_primary', true) ?? $this->photos->first())),
            'photos' => ItemPhotoResource::collection($this->whenLoaded('photos')),
            'views_count' => $this->views_count,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
