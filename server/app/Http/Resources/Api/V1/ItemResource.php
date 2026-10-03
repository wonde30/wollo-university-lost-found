<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $canViewFullContact = $viewer && (
            $viewer->isAdmin() ||
            $viewer->isOfficer() ||
            $viewer->id === $this->reporter_id
        );

        $reporterData = $this->whenLoaded('reporter', function () use ($canViewFullContact) {
            if (!$this->reporter) {
                return null;
            }
            return $canViewFullContact ? new UserResource($this->reporter) : [
                'id' => $this->reporter->id,
                'name' => $this->reporter->full_name,
                'full_name' => $this->reporter->full_name,
            ];
        });

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
            'category' => $this->whenLoaded('category', fn() => new CategoryResource($this->category)),
            'location' => $this->whenLoaded('location', fn() => new LocationResource($this->location)),
            'campus' => $this->whenLoaded('campus', fn() => new CampusResource($this->campus)),
            'reporter' => $reporterData,
            'user' => $reporterData,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
