<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int         $id
 * @property int         $role_id
 * @property string      $full_name
 * @property string      $university_id
 * @property string      $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string      $password
 * @property string|null $phone
 * @property string      $language
 * @property bool        $is_active
 * @property string|null $profile_photo
 * @property int         $failed_login_attempts
 * @property \Carbon\Carbon|null $locked_until
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read Role $role
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'full_name',
        'university_id',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'language',
        'is_active',
        'profile_photo',
        'failed_login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role_id'              => 'integer',
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'failed_login_attempts' => 'integer',
            'locked_until'         => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Convenience accessor                                               */
    /* ------------------------------------------------------------------ */

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * The role assigned to this user (FK: role_id → roles.id).
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Organizational units this user belongs to (many-to-many through pivot).
     */
    public function organizationalUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganizationalUnit::class,
            'user_organizational_units',
            'user_id',
            'organizational_unit_id'
        )
            ->withPivot(['is_primary', 'enrolled_year'])
            ->withTimestamps();
    }

    /**
     * Pivot records for user ↔ organizational-unit assignments.
     */
    public function userOrganizationalUnits(): HasMany
    {
        return $this->hasMany(UserOrganizationalUnit::class);
    }

    public function reportedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'reporter_id');
    }

    public function items(): HasMany
    {
        return $this->reportedItems();
    }

    public function deletedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'deleted_by');
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

    /**
     * Direct (per-user) permission overrides, independent of the role.
     */
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withTimestamps();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function authVerifications(): HasMany
    {
        return $this->hasMany(AuthVerification::class);
    }

    public function searchLogs(): HasMany
    {
        return $this->hasMany(SearchLog::class);
    }

    public function itemViews(): HasMany
    {
        return $this->hasMany(ItemView::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'requested_by');
    }

    public function matchSuggestionsReviewed(): HasMany
    {
        return $this->hasMany(MatchSuggestion::class, 'reviewed_by');
    }

    public function systemAnnouncements(): HasMany
    {
        return $this->hasMany(SystemAnnouncement::class, 'created_by');
    }

    /* ------------------------------------------------------------------ */
    /*  RBAC — Permission resolution                                       */
    /* ------------------------------------------------------------------ */

    protected static array $rolePermissionsCache = [];

    /**
     * Get permission names inherited from the user's role.
     */
    public function getRolePermissionNames(): array
    {
        $roleId = $this->role_id;

        // Use eager-loaded relation when available
        if ($this->relationLoaded('role') && $this->role?->relationLoaded('permissions')) {
            return $this->role->permissions
                ->where('is_active', true)
                ->pluck('name')
                ->values()
                ->all();
        }

        if (! app()->runningUnitTests() && isset(static::$rolePermissionsCache[$roleId])) {
            return static::$rolePermissionsCache[$roleId];
        }

        $role = $this->relationLoaded('role') && $this->role ? $this->role : Role::with('permissions')->find($roleId);

        if ($role) {
            $names = $role->permissions
                ->where('is_active', true)
                ->pluck('name')
                ->values()
                ->all();
            if (! empty($names)) {
                static::$rolePermissionsCache[$roleId] = $names;
                return $names;
            }

            // Fallback for standard system roles when pivot is unseeded (e.g. unit/feature testing)
            $roleName = $role->name;
            if ($roleName === 'admin') {
                $names = Permission::where('is_active', true)->pluck('name')->all();
                if (empty($names)) {
                    $names = [
                        'REPORT_LOST', 'REPORT_FOUND', 'EDIT_OWN_ITEM', 'DELETE_OWN_ITEM',
                        'MANAGE_ALL_ITEMS', 'CHANGE_ITEM_STATUS', 'SUBMIT_CLAIM',
                        'REVIEW_CLAIMS', 'REVERSE_CLAIMS', 'MANAGE_CUSTODY',
                        'MOVE_ITEM_CUSTODY', 'PROCESS_RETURNS', 'MANAGE_USERS',
                        'MANAGE_CAMPUSES', 'MANAGE_CATEGORIES', 'MANAGE_LOCATIONS',
                        'GENERATE_REPORTS', 'MANAGE_SETTINGS', 'VIEW_AUDIT_LOGS',
                        'MANAGE_PERMISSIONS',
                    ];
                }
                static::$rolePermissionsCache[$roleId] = $names;
                return $names;
            }
            if ($roleName === 'staff') {
                $names = [
                    'REPORT_LOST', 'REPORT_FOUND', 'EDIT_OWN_ITEM', 'DELETE_OWN_ITEM',
                    'MANAGE_ALL_ITEMS', 'CHANGE_ITEM_STATUS', 'SUBMIT_CLAIM',
                    'REVIEW_CLAIMS', 'REVERSE_CLAIMS', 'MANAGE_CUSTODY',
                    'MOVE_ITEM_CUSTODY', 'PROCESS_RETURNS',
                ];
                static::$rolePermissionsCache[$roleId] = $names;
                return $names;
            }
            if ($roleName === 'student') {
                $names = [
                    'REPORT_LOST', 'REPORT_FOUND', 'EDIT_OWN_ITEM', 'DELETE_OWN_ITEM', 'SUBMIT_CLAIM',
                ];
                static::$rolePermissionsCache[$roleId] = $names;
                return $names;
            }
        }

        static::$rolePermissionsCache[$roleId] = [];
        return [];
    }

    /**
     * Get directly assigned permission names (user-level overrides).
     */
    public function getDirectPermissionNames(): array
    {
        if ($this->relationLoaded('directPermissions')) {
            return $this->directPermissions
                ->where('is_active', true)
                ->pluck('name')
                ->values()
                ->all();
        }

        return $this->directPermissions()
            ->where('is_active', true)
            ->pluck('name')
            ->values()
            ->all();
    }

    /**
     * Merged set of role + direct permission names.
     */
    public function getPermissionNames(): array
    {
        $rolePerms = $this->getRolePermissionNames();
        $directPerms = $this->getDirectPermissionNames();

        return array_values(array_unique(array_merge($rolePerms, $directPerms)));
    }

    public static function flushPermissionCache(): void
    {
        static::$rolePermissionsCache = [];
    }

    /* ------------------------------------------------------------------ */
    /*  RBAC — Direct permission management                                */
    /* ------------------------------------------------------------------ */

    public function assignPermission(Permission|int|string $permission): void
    {
        $permissionId = $permission instanceof Permission ? $permission->id : (
            is_numeric($permission) ? (int) $permission : Permission::where('name', $permission)->value('id')
        );

        if ($permissionId) {
            $this->directPermissions()->syncWithoutDetaching([$permissionId]);
        }
    }

    public function revokePermission(Permission|int|string $permission): void
    {
        $permissionId = $permission instanceof Permission ? $permission->id : (
            is_numeric($permission) ? (int) $permission : Permission::where('name', $permission)->value('id')
        );

        if ($permissionId) {
            $this->directPermissions()->detach($permissionId);
        }
    }

    public function syncDirectPermissions(array $permissionIds): void
    {
        $this->directPermissions()->sync($permissionIds);
    }

    /* ------------------------------------------------------------------ */
    /*  RBAC — Authorization helpers                                       */
    /* ------------------------------------------------------------------ */

    public function hasPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->getPermissionNames(), true);
    }

    /**
     * Resolve the role name from the loaded relation or a single query.
     */
    public function getRoleName(): string
    {
        if ($this->relationLoaded('role') && $this->role) {
            return $this->role->name;
        }

        return Role::where('id', $this->role_id)->value('name') ?? '';
    }

    public function isAdmin(): bool
    {
        return $this->getRoleName() === 'admin';
    }

    public function isOfficer(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->getRoleName() === 'staff') {
            return true;
        }

        return $this->hasPermission('REVIEW_CLAIMS')
            || $this->hasPermission('MANAGE_CUSTODY')
            || $this->hasPermission('PROCESS_RETURNS')
            || $this->hasPermission('MANAGE_ALL_ITEMS');
    }

    public function isStaff(): bool
    {
        $roleName = $this->getRoleName();
        return in_array($roleName, ['admin', 'staff'], true) || $this->isOfficer();
    }

    public function isStudent(): bool
    {
        return $this->getRoleName() === 'student';
    }
}
