<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $claim_id
 * @property int         $uploaded_by
 * @property string      $evidence_type
 * @property string      $path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int|null    $size_bytes
 * @property string|null $description
 * @property \Carbon\Carbon $uploaded_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class ClaimEvidence extends Model
{
    use HasFactory;

    protected $table = 'claim_evidence';

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

    protected function casts(): array
    {
        return [
            'claim_id'    => 'integer',
            'uploaded_by' => 'integer',
            'size_bytes'  => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ClaimEvidence $evidence) {
            if (empty($evidence->uploaded_at)) {
                $evidence->uploaded_at = now();
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
