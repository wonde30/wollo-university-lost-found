<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $item_id
 * @property int|null    $changed_by
 * @property string|null $from_status
 * @property string      $to_status
 * @property string|null $changed_by_role
 * @property string|null $note
 * @property string|null $ip_address
 * @property \Carbon\Carbon|null $created_at
 */
class ItemStatusHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'changed_by',
        'from_status',
        'to_status',
        'changed_by_role',
        'note',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'item_id'    => 'integer',
            'changed_by' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
