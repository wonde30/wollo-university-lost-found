<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'display_name_am',
        'description',
        'description_am',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            User::flushPermissionCache();
        });
        static::deleted(function () {
            User::flushPermissionCache();
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function assignPermission(Permission|string $permission): void
    {
        $permId = is_string($permission)
            ? Permission::where('name', $permission)->value('id')
            : $permission->id;

        if ($permId && ! $this->permissions()->where('permission_id', $permId)->exists()) {
            $this->permissions()->attach($permId);
            User::flushPermissionCache();
        }
    }

    public function removePermission(Permission|string $permission): void
    {
        $permId = is_string($permission)
            ? Permission::where('name', $permission)->value('id')
            : $permission->id;

        if ($permId) {
            $this->permissions()->detach($permId);
            User::flushPermissionCache();
        }
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
        User::flushPermissionCache();
    }
}
