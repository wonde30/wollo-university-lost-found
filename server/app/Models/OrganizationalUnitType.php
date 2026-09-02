<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Configurable organizational hierarchy type (e.g. college, faculty, department, program).
 *
 * @property int         $id
 * @property string      $code
 * @property string      $name
 * @property string|null $name_am
 * @property string|null $description
 * @property bool        $is_root
 * @property bool        $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class OrganizationalUnitType extends Model
{
    use HasFactory;

    protected $table = 'organizational_unit_types';

    protected $fillable = [
        'code',
        'name',
        'name_am',
        'description',
        'is_root',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_root'   => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Type relations where this type acts as the parent.
     */
    public function childTypeRelations(): HasMany
    {
        return $this->hasMany(OrganizationalUnitTypeRelation::class, 'parent_type_id');
    }

    /**
     * Type relations where this type acts as the child.
     */
    public function parentTypeRelations(): HasMany
    {
        return $this->hasMany(OrganizationalUnitTypeRelation::class, 'child_type_id');
    }

    /**
     * All organizational units of this type.
     */
    public function organizationalUnits(): HasMany
    {
        return $this->hasMany(OrganizationalUnit::class, 'type_id');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->where('is_root', true);
    }
}
