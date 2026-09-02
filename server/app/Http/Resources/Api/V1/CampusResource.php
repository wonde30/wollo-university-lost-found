<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'short_code'           => $this->short_code,
            'city'                 => $this->city,
            'region'               => $this->region,
            'address'              => $this->address,
            'phone'                => $this->phone,
            'email'                => $this->email,
            'is_active'            => (bool) $this->is_active,
            'organizational_units' => OrganizationalUnitResource::collection($this->whenLoaded('organizationalUnits')),
            'locations'            => LocationResource::collection($this->whenLoaded('locations')),
            'created_at'           => $this->created_at?->toISOString(),
            'updated_at'           => $this->updated_at?->toISOString(),
        ];
    }
}
