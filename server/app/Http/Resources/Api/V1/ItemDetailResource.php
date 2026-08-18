<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $canViewFullContact = $viewer && (
            $viewer->isAdmin() ||
            $viewer->isOfficer() ||
            $viewer->id === $this->reporter_id ||
            $this->claims()->where('claimant_id', $viewer->id)->where('status', 'approved')->exists()
        );

        $reporterUser = $this->reporter ?? $this->user;

        return [
            'id' => $this->id,
            'reference_code' => $this->reference_code,
            'reporter_id' => $this->reporter_id,
            'campus_id' => $this->campus_id,
            'type' => $this->type instanceof \BackedEnum ? $this->type->value : (string) $this->type,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'held_at' => $this->held_at instanceof \BackedEnum ? $this->held_at->value : (string) $this->held_at,
            'title' => $this->title,
            'description' => $this->description,
            'brand' => $this->brand,
            'color' => $this->color,
            'serial_number' => $this->serial_number,
            'incident_date' => $this->incident_date?->format('Y-m-d'),
            'incident_time' => $this->incident_time,
            'estimated_value' => $this->estimated_value,
            'is_high_value' => $this->is_high_value,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'campus' => new CampusResource($this->whenLoaded('campus')),
            'reporter' => $reporterUser ? ($canViewFullContact ? new UserResource($reporterUser) : [
                'id' => $reporterUser->id,
                'name' => $reporterUser->full_name,
                'full_name' => $reporterUser->full_name,
            ]) : null,
            'photos' => ItemPhotoResource::collection($this->whenLoaded('photos')),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->pluck('tag')),
            'status_histories' => ItemStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'custody_events' => $this->when(
                $viewer && ($viewer->isAdmin() || $viewer->isOfficer()),
                CustodyEventResource::collection($this->whenLoaded('custodyEvents'))
            ),
            'claims_count' => $this->whenCounted('claims'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
