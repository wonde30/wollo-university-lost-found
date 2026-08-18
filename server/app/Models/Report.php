<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'report_type',
        'filters',
        'format',
        'status',
        'file_path',
        'file_size_bytes',
        'row_count',
        'error_message',
        'ready_at',
        'downloaded_at',
        'download_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'file_size_bytes' => 'integer',
            'row_count' => 'integer',
            'download_count' => 'integer',
            'ready_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
    