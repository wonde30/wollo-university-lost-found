<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'photo_url' => asset('storage/' . $this->path),
            'original_name' => $this->original_name,
            'is_primary' => (bool)$this->is_primary,
            'size_bytes' => $this->size_bytes,
            'mime_type' => $this->mime_type,
        ];
    }
}
