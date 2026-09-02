<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model for user ↔ organizational-unit assignments.
 *
 * @property int      $id
 * @property int      $user_id
 * @property int      $organizational_unit_id
 * @property bool     $is_primary
 * @property int|null $enrolled_year
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class UserOrganizationalUnit extends Model
{
    use HasFactory;

    protected $table = 'user_organizational_units';

    protected $fillable = [
        'user_id',
        'organizational_unit_id',
        'is_primary',
        'enrolled_year',
    ];

    protected function casts(): array
    {
        return [
            'user_id'                  => 'integer',
            'organizational_unit_id'   => 'integer',
            'is_primary'               => 'boolean',
            'enrolled_year'            => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }
}
