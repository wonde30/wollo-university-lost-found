<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $item_id
 * @property int         $actor_id
 * @property int|null    $storage_location_id
 * @property string      $event_type
 * @property string|null $condition
 * @property string|null $notes
 * @property string|null $reference_photo
 * @property \Carbon\Carbon|null $created_at
 */
class CustodyEvent extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'actor_id',
        'storage_location_id',
        'event_type',
        'condition',
        'notes',
        'reference_photo',
    ];

    protected function casts(): array
    {
        return [
            'item_id'             => 'integer',
            'actor_id'            => 'integer',
            'storage_location_id' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function storageLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class);
    }
}
