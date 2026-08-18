<?php

namespace App\Models;

use App\Support\Enums\AuthVerificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthVerification extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // append-only table: no updated_at column in migration

    protected $fillable = [
        'user_id',
        'email',
        'type',
        'token',
        'code',          // 6-digit OTP — authoritative column used by OtpService
        // 'plain_code' is a duplicate from an early migration; code is used everywhere
        'attempts',
        'last_sent_at',
        'expires_at',
        'used_at',
        'verified_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'type' => AuthVerificationType::class,
            'attempts' => 'integer',
            'last_sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
