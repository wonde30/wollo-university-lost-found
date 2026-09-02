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
 * @property string      $code
 * @property string|null $description
 * @property int|null    $capacity
 * @property bool        $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class StorageLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'name',
        'code',
        'description',
        'capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'capacity'  => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function custodyEvents(): HasMany
    {
        return $this->hasMany(CustodyEvent::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnRecord::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
