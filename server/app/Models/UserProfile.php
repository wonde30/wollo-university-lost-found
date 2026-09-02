<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $user_id
 * @property string|null $id_card_photo
 * @property int|null    $year_of_study
 * @property string|null $gender
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $home_town
 * @property string|null $bio
 * @property \Carbon\Carbon|null $last_seen_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'id_card_photo',
        'year_of_study',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'home_town',
        'bio',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id'       => 'integer',
            'year_of_study' => 'integer',
            'last_seen_at'  => 'datetime',
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
