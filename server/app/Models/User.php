<?php

namespace App\Models;

use App\Support\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'university_id',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'role',
        'language',
        'is_active',
        'failed_login_attempts', // required for brute-force lockout (FR-03)
        'locked_until',          // required for brute-force lockout (FR-03)
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_departments')
            ->withPivot(['is_primary', 'enrolled_year'])
            ->withTimestamps();
    }

    public function reportedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'reporter_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class, 'claimant_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isOfficer(): bool
    {
        return in_array($this->role, [UserRole::ADMIN, UserRole::STAFF], true);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [UserRole::ADMIN, UserRole::STAFF], true);
    }
}
