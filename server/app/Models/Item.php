<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Helpers\ReferenceCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property string      $reference_code
 * @property int         $reporter_id
 * @property int         $campus_id
 * @property string      $type
 * @property string      $title
 * @property string|null $description
 * @property int         $category_id
 * @property int|null    $location_id
 * @property string|null $location_detail
 * @property string|null $brand
 * @property string|null $color
 * @property string|null $serial_number
 * @property \Carbon\Carbon|null $incident_date
 * @property string|null $incident_time
 * @property string      $status
 * @property string|null $held_at
 * @property string|null $estimated_value
 * @property bool        $is_high_value
 * @property bool        $is_deleted
 * @property int|null    $deleted_by
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $last_activity_at
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
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
            'reporter_id'     => 'integer',
            'campus_id'       => 'integer',
            'category_id'     => 'integer',
            'location_id'     => 'integer',
            'incident_date'   => 'date',
            'estimated_value' => 'decimal:2',
            'is_high_value'   => 'boolean',
            'is_deleted'      => 'boolean',
            'deleted_by'      => 'integer',
            'deleted_at'      => 'datetime',
            'last_activity_at' => 'datetime',
            'expires_at'      => 'datetime',
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

        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('admin.statistics'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('admin.statistics'));
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** Alias kept for backward compatibility. */
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

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
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

    public function matchSuggestionsAsFound(): HasMany
    {
        return $this->hasMany(MatchSuggestion::class, 'found_item_id');
    }

    public function matchSuggestionsAsLost(): HasMany
    {
        return $this->hasMany(MatchSuggestion::class, 'lost_item_id');
    }
}
