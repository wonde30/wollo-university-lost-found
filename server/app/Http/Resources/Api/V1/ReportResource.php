<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requested_by' => $this->requested_by,
            'report_type' => $this->report_type,
            'filters' => $this->filters,
            'format' => $this->format,
            'status' => $this->status,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'file_size_bytes' => $this->file_size_bytes,
            'row_count' => $this->row_count,
            'error_message' => $this->error_message,
            'ready_at' => $this->ready_at?->toISOString(),
            'downloaded_at' => $this->downloaded_at?->toISOString(),
            'download_count' => $this->download_count,
            'expires_at' => $this->expires_at?->toISOString(),
            'requested_by_user' => new UserResource($this->whenLoaded('requestedBy')),
            'generated_by' => new UserResource($this->whenLoaded('requestedBy')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
