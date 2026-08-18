<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnRecord extends Model
{
    use HasFactory;

    protected $table = 'returns';

    const UPDATED_AT = null;

    protected $fillable = [
        'claim_id',
        'item_id',
        'returned_to',
        'handed_over_by',
        'storage_location_id',
        'return_date',
        'return_time',
        'condition_on_return',
        'notes',
        'recipient_confirmed',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'recipient_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    public function storageLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ReturnDocument::class, 'return_id');
    }
}
