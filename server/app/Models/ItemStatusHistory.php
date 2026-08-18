<?php

namespace App\Models;

use App\Support\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'from_status' => ItemStatus::class,
        'to_status' => ItemStatus::class,
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
