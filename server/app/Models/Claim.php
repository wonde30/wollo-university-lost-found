<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int         $id
 * @property int         $item_id
 * @property int         $claimant_id
 * @property string|null $explanation
 * @property string      $status
 * @property int|null    $reviewed_by
 * @property string|null $review_note
 * @property \Carbon\Carbon|null $reviewed_at
 * @property bool        $auto_rejected
 * @property string|null $ip_address
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'claimant_id',
        'explanation',
        'status',
        'reviewed_by',
        'review_note',
        'reviewed_at',
        'auto_rejected',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'item_id'       => 'integer',
            'claimant_id'   => 'integer',
            'reviewed_by'   => 'integer',
            'reviewed_at'   => 'datetime',
            'auto_rejected' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('admin.statistics'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('admin.statistics'));
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    /** Alias kept for backward compatibility. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ClaimEvidence::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ClaimStatusHistory::class);
    }

    public function returnRecord(): HasOne
    {
        return $this->hasOne(ReturnRecord::class, 'claim_id');
    }
}
