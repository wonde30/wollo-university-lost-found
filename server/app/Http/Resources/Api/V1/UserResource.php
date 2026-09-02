<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'full_name'            => $this->full_name,
            'name'                 => $this->full_name,
            'university_id'        => $this->university_id,
            'email'                => $this->email,
            'email_verified_at'    => $this->email_verified_at?->toISOString(),
            'phone'                => $this->phone,
            'role'                 => $this->whenLoaded('role', fn () => $this->getRoleName(), $this->getRoleName()),
            'role_id'              => $this->role_id,
            'language'             => $this->language,
            'is_active'            => $this->is_active,
            // Only resolve permission names when the relation was eager-loaded.
            // Without this guard, each user in a paginated list triggers 3 extra
            // DB queries → N*3 queries eliminated for the /admin/users list endpoint.
            'permissions'          => $this->whenLoaded('directPermissions', fn () => $this->getPermissionNames(), []),
            'direct_permissions'   => $this->whenLoaded('directPermissions', fn () => $this->getDirectPermissionNames(), []),
            'role_permissions'     => $this->whenLoaded('directPermissions', fn () => $this->getRolePermissionNames(), []),
            'profile'              => new UserProfileResource($this->whenLoaded('profile')),
            'organizational_units' => OrganizationalUnitResource::collection($this->whenLoaded('organizationalUnits')),
            'created_at'           => $this->created_at?->toISOString(),
        ];
    }
}
