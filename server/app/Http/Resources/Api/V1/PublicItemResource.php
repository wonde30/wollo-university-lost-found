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
            'type' => $this->type,
            'status' => $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'brand' => $this->brand,
            'date_lost_found' => $this->date_lost_found?->toISOString(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'photos' => ItemPhotoResource::collection($this->whenLoaded('photos')),
            'views_count' => $this->views_count,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
