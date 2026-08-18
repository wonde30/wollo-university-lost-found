<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimEvidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'claim_id' => $this->claim_id,
            'uploaded_by' => $this->uploaded_by,
            'evidence_type' => $this->evidence_type,
            'path' => $this->path,
            'file_url' => asset('storage/' . $this->path),
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'description' => $this->description,
            'uploaded_at' => $this->uploaded_at?->toISOString() ?? $this->created_at?->toISOString(),
        ];
    }
}
