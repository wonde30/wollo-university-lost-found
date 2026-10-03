<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'must_change_password'=> (bool) $this->must_change_password,
            'profile_photo'       => $this->profile_photo,
            'profile_photo_url'   => $this->profile_photo ? Storage::disk('public')->url($this->profile_photo) : null,
            'permissions'         => $this->getPermissionNames(),
            'direct_permissions'  => $this->getDirectPermissionNames(),
            'role_permissions'    => $this->getRolePermissionNames(),
            'profile'             => new UserProfileResource($this->whenLoaded('profile')),
            'organizational_units' => OrganizationalUnitResource::collection($this->whenLoaded('organizationalUnits')),
        ];
    }
}
