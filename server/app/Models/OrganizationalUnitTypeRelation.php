<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Defines which organizational-unit types may be nested under which parent types.
 *
 * @property int  $id
 * @property int  $child_type_id
 * @property int  $parent_type_id
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class OrganizationalUnitTypeRelation extends Model
{
    use HasFactory;

    protected $table = 'organizational_unit_type_relations';

    protected $fillable = [
        'child_type_id',
        'parent_type_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'child_type_id'  => 'integer',
            'parent_type_id' => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function childType(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnitType::class, 'child_type_id');
    }

    public function parentType(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnitType::class, 'parent_type_id');
    }
}
