<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $item_id
 * @property string      $tag
 * @property \Carbon\Carbon|null $created_at
 */
class ItemTag extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'tag',
    ];

    protected function casts(): array
    {
        return [
            'item_id' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
