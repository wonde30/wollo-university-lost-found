<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'name' => $this->full_name,
            'university_id' => $this->university_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role instanceof \BackedEnum ? $this->role->value : (string) $this->role,
            'language' => $this->language,
            'is_active' => $this->is_active,
            'profile_photo' => $this->profile_photo,
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
