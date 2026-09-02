<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $query
 * @property int|null    $category_id
 * @property int|null    $campus_id
 * @property int         $results_count
 * @property bool        $found_match
 * @property string|null $ip_address
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class SearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query',
        'category_id',
        'campus_id',
        'results_count',
        'found_match',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'user_id'       => 'integer',
            'category_id'   => 'integer',
            'campus_id'     => 'integer',
            'results_count' => 'integer',
            'found_match'   => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
