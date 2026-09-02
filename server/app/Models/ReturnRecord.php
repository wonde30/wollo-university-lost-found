<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property int         $id
 * @property int         $claim_id
 * @property int         $returned_to
 * @property int         $handed_over_by
 * @property int|null    $storage_location_id
 * @property \Carbon\Carbon $return_date
 * @property string|null $return_time
 * @property string|null $condition_on_return
 * @property string|null $notes
 * @property bool        $recipient_confirmed
 * @property \Carbon\Carbon|null $confirmed_at
 * @property string|null $confirmation_token
 * @property \Carbon\Carbon|null $confirmation_token_expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class ReturnRecord extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'claim_id',
        'returned_to',
        'handed_over_by',
        'storage_location_id',
        'return_date',
        'return_time',
        'condition_on_return',
        'notes',
        'recipient_confirmed',
        'confirmed_at',
        'confirmation_token',
        'confirmation_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'claim_id'                       => 'integer',
            'returned_to'                    => 'integer',
            'handed_over_by'                 => 'integer',
            'storage_location_id'            => 'integer',
            'return_date'                    => 'date',
            'recipient_confirmed'            => 'boolean',
            'confirmed_at'                   => 'datetime',
            'confirmation_token_expires_at'  => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function item(): HasOneThrough
    {
        return $this->hasOneThrough(Item::class, Claim::class, 'id', 'id', 'claim_id', 'item_id');
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
