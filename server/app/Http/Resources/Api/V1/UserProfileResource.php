<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'user_id'                 => $this->user_id,
            'id_card_photo'           => $this->id_card_photo,
            'year_of_study'           => $this->year_of_study,
            'gender'                  => $this->gender,
            'emergency_contact_name'  => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'home_town'               => $this->home_town,
            'bio'                     => $this->bio,
            'last_seen_at'            => $this->last_seen_at?->toISOString(),
        ];
    }
}
