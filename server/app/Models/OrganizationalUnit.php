<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An organizational unit within a campus (college, faculty, department, program, etc.).
 * Supports self-referencing hierarchy via parent_id.
 *
 * @property int         $id
 * @property int         $campus_id
 * @property int|null    $parent_id
 * @property int         $type_id
 * @property string      $name
 * @property string|null $name_am
 * @property string      $short_code
 * @property string|null $description
 * @property bool        $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class OrganizationalUnit extends Model
{
    use HasFactory;

    protected $table = 'organizational_units';

    protected $fillable = [
        'campus_id',
        'parent_id',
        'type_id',
        'name',
        'name_am',
        'short_code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'campus_id' => 'integer',
            'parent_id' => 'integer',
            'type_id'   => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Self-referencing: the parent unit (nullable for root units).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Self-referencing: all direct children of this unit.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The configurable type of this organizational unit.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnitType::class, 'type_id');
    }

    /**
     * Pivot records linking users to this unit.
     */
    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserOrganizationalUnit::class, 'organizational_unit_id');
    }

    /**
     * Users assigned to this organizational unit (many-to-many through pivot).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organizational_units', 'organizational_unit_id', 'user_id')
            ->withPivot(['is_primary', 'enrolled_year'])
            ->withTimestamps();
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForCampus($query, int $campusId)
    {
        return $query->where('campus_id', $campusId);
    }
}
