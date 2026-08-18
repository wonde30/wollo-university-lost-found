<?php

namespace App\Models;

use App\Support\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'from_status' => ClaimStatus::class,
        'to_status' => ClaimStatus::class,
        'was_auto_rejected' => 'boolean',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
