<?php

namespace App\Models;

use App\Support\Enums\CustodyEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'event_type' => CustodyEventType::class,
    ];

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
