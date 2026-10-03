<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UniversityDomainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'domain'           => $this->domain,
            'institution_name' => $this->institution_name,
            'campus_id'        => $this->campus_id,
            'campus'           => $this->whenLoaded('campus', fn () => [
                'id'   => $this->campus->id,
                'name' => $this->campus->name,
                'code' => $this->campus->code,
            ]),
            'is_active'        => (bool) $this->is_active,
            'description'      => $this->description,
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
