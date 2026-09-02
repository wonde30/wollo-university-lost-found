<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationalUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'campus_id'   => $this->campus_id,
            'parent_id'   => $this->parent_id,
            'type_id'     => $this->type_id,
            'name'        => $this->name,
            'name_am'     => $this->name_am,
            'short_code'  => $this->short_code,
            'description' => $this->description,
            'is_active'   => (bool) $this->is_active,
            'campus'      => new CampusResource($this->whenLoaded('campus')),
            'type'        => $this->whenLoaded('type', fn () => [
                'id'   => $this->type->id,
                'code' => $this->type->code,
                'name' => $this->type->name,
            ]),
            'parent'      => new self($this->whenLoaded('parent')),
            'is_primary'  => $this->whenPivotLoaded('user_organizational_units', fn () => (bool) $this->pivot->is_primary),
            'enrolled_year' => $this->whenPivotLoaded('user_organizational_units', fn () => $this->pivot->enrolled_year),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
