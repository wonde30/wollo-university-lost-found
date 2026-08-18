<?php

namespace App\Models;

use App\Support\Enums\ItemHeldAt;
use App\Support\Enums\ItemStatus;
use App\Support\Enums\ItemType;
use App\Support\Helpers\ReferenceCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code',
        'reporter_id',
        'campus_id',
        'type',
        'title',
        'description',
        'category_id',
        'location_id',
        'location_detail',
        'brand',
        'color',
        'serial_number',
        'incident_date',
        'incident_time',
        'status',
        'held_at',
        'estimated_value',
        'is_high_value',
        'is_deleted',
        'deleted_by',
        'deleted_at',
        'last_activity_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ItemType::class,
            'status' => ItemStatus::class,
            'held_at' => ItemHeldAt::class,
            'incident_date' => 'date',
            'is_high_value' => 'boolean',
            'is_deleted' => 'boolean',
            'deleted_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'expires_at' => 'datetime',
            'estimated_value' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Item $item) {
            if (empty($item->reference_code)) {
                $item->reference_code = ReferenceCode::generate('WU');
            }
            if (empty($item->last_activity_at)) {
                $item->last_activity_at = now();
            }
        });
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ItemPhoto::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ItemTag::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ItemStatusHistory::class);
    }

    public function custodyEvents(): HasMany
    {
        return $this->hasMany(CustodyEvent::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ItemView::class);
    }
}
