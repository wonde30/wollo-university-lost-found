<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchSuggestion extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'found_item_id',
        'lost_item_id',
        'score',
        'category_score',
        'text_score',
        'location_score',
        'algorithm_version',
        'status',
        'notified_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'category_score' => 'decimal:2',
        'text_score' => 'decimal:2',
        'location_score' => 'decimal:2',
        'notified_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'found_item_id');
    }

    public function lostItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'lost_item_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
