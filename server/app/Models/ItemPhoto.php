<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $item_id
 * @property string      $path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int|null    $size_bytes
 * @property int|null    $width_px
 * @property int|null    $height_px
 * @property bool        $is_primary
 * @property \Carbon\Carbon $uploaded_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class ItemPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'width_px',
        'height_px',
        'is_primary',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'item_id'     => 'integer',
            'size_bytes'  => 'integer',
            'width_px'    => 'integer',
            'height_px'   => 'integer',
            'is_primary'  => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
