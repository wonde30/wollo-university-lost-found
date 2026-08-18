<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimEvidence extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'claim_id',
        'uploaded_by',
        'evidence_type',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'description',
        'uploaded_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClaimEvidence $evidence) {
            if (empty($evidence->uploaded_at)) {
                $evidence->uploaded_at = now();
            }
        });
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
