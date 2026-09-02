<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'permission_group_id' => $this->permission_group_id,
            'name' => $this->name,
            'key' => $this->name, // for frontend compatibility
            'display_name' => $this->display_name,
            'label' => $this->display_name, // for frontend compatibility
            'display_name_am' => $this->display_name_am,
            'description' => $this->description,
            'description_am' => $this->description_am,
            'category' => $this->category,
            'is_system' => $this->is_system,
            'is_active' => $this->is_active,
            'permission_group' => new PermissionGroupResource($this->whenLoaded('permissionGroup')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
