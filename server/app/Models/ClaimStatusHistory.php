<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $claim_id
 * @property int|null    $changed_by
 * @property string|null $from_status
 * @property string      $to_status
 * @property string|null $changed_by_role
 * @property bool        $was_auto_rejected
 * @property string|null $note
 * @property string|null $ip_address
 * @property \Carbon\Carbon|null $created_at
 */
class ClaimStatusHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'claim_id',
        'changed_by',
        'from_status',
        'to_status',
        'changed_by_role',
        'was_auto_rejected',
        'note',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'claim_id'          => 'integer',
            'changed_by'        => 'integer',
            'was_auto_rejected' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
