<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $email
 * @property string      $type
 * @property string|null $token
 * @property string|null $code
 * @property int         $attempts
 * @property \Carbon\Carbon|null $last_sent_at
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $verified_at
 * @property \Carbon\Carbon|null $used_at
 * @property string|null $ip_address
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class AuthVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'type',
        'token',
        'code',
        'attempts',
        'last_sent_at',
        'expires_at',
        'verified_at',
        'used_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'user_id'      => 'integer',
            'attempts'     => 'integer',
            'last_sent_at' => 'datetime',
            'expires_at'   => 'datetime',
            'verified_at'  => 'datetime',
            'used_at'      => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
