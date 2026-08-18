<?php

namespace App\Models;

use App\Support\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
            'status' => ClaimStatus::class,
            'reviewed_at' => 'datetime',
            'auto_rejected' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

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
