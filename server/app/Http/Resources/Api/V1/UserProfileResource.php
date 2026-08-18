<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'avatar_url' => $this->avatar_url,
            'bio' => $this->bio,
            'phone_number' => $this->phone_number,
            'student_staff_id' => $this->student_staff_id,
            'gender' => $this->gender,
            'address' => $this->address,
            'preferences' => $this->preferences,
        ];
    }
}
