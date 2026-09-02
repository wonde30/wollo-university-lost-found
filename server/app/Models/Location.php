<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property int         $campus_id
 * @property string      $name
 * @property string|null $name_am
 * @property string      $code
 * @property string|null $building
 * @property string|null $zone
 * @property bool        $is_active
 * @property int         $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'name',
        'name_am',
        'code',
        'building',
        'zone',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'campus_id'  => 'integer',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
