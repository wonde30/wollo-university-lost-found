<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $found_item_id
 * @property int         $lost_item_id
 * @property string      $score
 * @property string|null $category_score
 * @property string|null $text_score
 * @property string|null $location_score
 * @property string|null $algorithm_version
 * @property string      $status
 * @property \Carbon\Carbon|null $notified_at
 * @property \Carbon\Carbon|null $reviewed_at
 * @property int|null    $reviewed_by
 * @property \Carbon\Carbon|null $created_at
 */
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

    protected function casts(): array
    {
        return [
            'found_item_id'  => 'integer',
            'lost_item_id'   => 'integer',
            'reviewed_by'    => 'integer',
            'score'          => 'decimal:2',
            'category_score' => 'decimal:2',
            'text_score'     => 'decimal:2',
            'location_score' => 'decimal:2',
            'notified_at'    => 'datetime',
            'reviewed_at'    => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

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
