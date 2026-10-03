<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\Location;
use App\Models\OrganizationalUnit;
use App\Models\OrganizationalUnitType;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Report;
use App\Models\ReturnRecord;
use App\Models\Role;
use App\Models\StorageLocation;
use App\Models\SystemAnnouncement;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\CampusPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ClaimPolicy;
use App\Policies\CustodyEventPolicy;
use App\Policies\ItemPolicy;
use App\Policies\LocationPolicy;
use App\Policies\OrganizationalUnitPolicy;
use App\Policies\OrganizationalUnitTypePolicy;
use App\Policies\PermissionGroupPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ReturnPolicy;
use App\Policies\RolePolicy;
use App\Policies\StorageLocationPolicy;
use App\Policies\SystemAnnouncementPolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Explicit policy mappings for all 18 system models
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Claim::class, ClaimPolicy::class);
        Gate::policy(CustodyEvent::class, CustodyEventPolicy::class);
        Gate::policy(ReturnRecord::class, ReturnPolicy::class);
        Gate::policy(Campus::class, CampusPolicy::class);
        Gate::policy(OrganizationalUnit::class, OrganizationalUnitPolicy::class);
        Gate::policy(OrganizationalUnitType::class, OrganizationalUnitTypePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(StorageLocation::class, StorageLocationPolicy::class);
        Gate::policy(SystemAnnouncement::class, SystemAnnouncementPolicy::class);
        Gate::policy(SystemSetting::class, SystemSettingPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(PermissionGroup::class, PermissionGroupPolicy::class);
        Gate::policy(\App\Models\UniversityDomain::class, \App\Policies\UniversityDomainPolicy::class);

        // Public Authentication Rate Limiters (Security / P0)
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by($email.'|'.$ip)->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 60;
                    return response()->json([
                        'success' => false,
                        'message' => "Too many login attempts. Please try again in {$retryAfter} seconds.",
                        'retry_after' => (int) $retryAfter,
                    ], 429, $headers);
                }),
                Limit::perMinute(10)->by($ip)->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? 60;
                    return response()->json([
                        'success' => false,
                        'message' => "Too many attempts from this network. Please try again in {$retryAfter} seconds.",
                        'retry_after' => (int) $retryAfter,
                    ], 429, $headers);
                }),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by((string) $request->ip())->response(function (Request $request, array $headers) {
                $retryAfter = $headers['Retry-After'] ?? 60;
                return response()->json([
                    'success' => false,
                    'message' => "Too many registration attempts. Please try again in {$retryAfter} seconds.",
                    'retry_after' => (int) $retryAfter,
                ], 429, $headers);
            });
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $ip = (string) $request->ip();

            return Limit::perMinute(3)->by($email.'|'.$ip)->response(function (Request $request, array $headers) {
                $retryAfter = $headers['Retry-After'] ?? 60;
                return response()->json([
                    'success' => false,
                    'message' => "Too many password reset requests. Please try again in {$retryAfter} seconds.",
                    'retry_after' => (int) $retryAfter,
                ], 429, $headers);
            });
        });

        RateLimiter::for('otp-verify', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $ip = (string) $request->ip();

            return Limit::perMinute(10)->by($email.'|'.$ip)->response(function (Request $request, array $headers) {
                $retryAfter = $headers['Retry-After'] ?? 60;
                return response()->json([
                    'success' => false,
                    'message' => "Too many verification attempts. Please try again in {$retryAfter} seconds.",
                    'retry_after' => (int) $retryAfter,
                ], 429, $headers);
            });
        });

        RateLimiter::for('otp-resend', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $ip = (string) $request->ip();

            return Limit::perMinute(2)->by($email.'|'.$ip)->response(function (Request $request, array $headers) {
                $retryAfter = $headers['Retry-After'] ?? 60;
                return response()->json([
                    'success' => false,
                    'message' => "Too many OTP requests. Please wait before requesting another verification code.",
                    'retry_after' => (int) $retryAfter,
                ], 429, $headers);
            });
        });
    }
}
