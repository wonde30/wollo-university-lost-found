<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'display_name_am' => $this->display_name_am,
            'description' => $this->description,
            'description_am' => $this->description_am,
            'is_system' => $this->is_system,
            'is_active' => $this->is_active,
            'permissions_count' => $this->permissions_count ?? $this->permissions?->count() ?? 0,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'active_permissions' => PermissionResource::collection($this->whenLoaded('activePermissions')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
