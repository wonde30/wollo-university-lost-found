<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemTag extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'tag',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
