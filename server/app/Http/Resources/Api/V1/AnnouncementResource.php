<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'audience' => $this->audience,
            'is_active' => (bool) $this->is_active,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'creator' => new UserResource($this->whenLoaded('createdByUser')),
            'created_by_user' => new UserResource($this->whenLoaded('createdByUser')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
