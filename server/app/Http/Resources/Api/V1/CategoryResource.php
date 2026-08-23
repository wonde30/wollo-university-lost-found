<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_am' => $this->name_am,
            'icon_slug' => $this->icon_slug,
            'icon' => $this->icon_slug,  // Alias for backwards compatibility
            'sort_order' => $this->sort_order,
            'is_active' => (bool)$this->is_active,
            'items_count' => $this->whenCounted('items'),
        ];
    }
}
