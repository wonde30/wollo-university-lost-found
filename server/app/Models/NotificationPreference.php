<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_on_report_submitted',
        'email_on_match_found',
        'email_on_claim_received',
        'email_on_claim_decided',
        'email_on_item_returned',
        'email_on_expiry_warning',
        'email_on_item_expired',
        'email_on_system_announcements',
    ];

    protected function casts(): array
    {
        return [
            'email_on_report_submitted' => 'boolean',
            'email_on_match_found' => 'boolean',
            'email_on_claim_received' => 'boolean',
            'email_on_claim_decided' => 'boolean',
            'email_on_item_returned' => 'boolean',
            'email_on_expiry_warning' => 'boolean',
            'email_on_item_expired' => 'boolean',
            'email_on_system_announcements' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
