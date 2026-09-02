<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'full_name'           => $this->full_name,
            'name'                => $this->full_name,
            'university_id'       => $this->university_id,
            'email'               => $this->email,
            'email_verified_at'   => $this->email_verified_at?->toISOString(),
            'phone'               => $this->phone,
            'role'                => $this->getRoleName(),
            'role_id'             => $this->role_id,
            'language'            => $this->language,
            'is_active'           => $this->is_active,
            'profile_photo'       => $this->profile_photo,
            'permissions'         => $this->getPermissionNames(),
            'direct_permissions'  => $this->getDirectPermissionNames(),
            'role_permissions'    => $this->getRolePermissionNames(),
            'profile'             => new UserProfileResource($this->whenLoaded('profile')),
            'organizational_units' => OrganizationalUnitResource::collection($this->whenLoaded('organizationalUnits')),
        ];
    }
}
