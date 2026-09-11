<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\VerificationController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\LocationController as PublicLocationController;
use App\Http\Controllers\Api\V1\Public\AnnouncementController as PublicAnnouncementController;
use App\Http\Controllers\Api\V1\Public\PublicItemController;
use App\Http\Controllers\Api\V1\Public\PublicStatisticsController;
use App\Http\Controllers\Api\V1\Public\TrackingController;
use App\Http\Controllers\Api\V1\Public\SettingController as PublicSettingController;
use App\Http\Controllers\Api\V1\Items\ItemController;
use App\Http\Controllers\Api\V1\Items\ItemPhotoController;
use App\Http\Controllers\Api\V1\Items\ItemStatusController;
use App\Http\Controllers\Api\V1\Claims\ClaimController;
use App\Http\Controllers\Api\V1\Claims\ClaimReviewController;
use App\Http\Controllers\Api\V1\Custody\CustodyController;
use App\Http\Controllers\Api\V1\Custody\StorageLocationController as CustodyStorageLocationController;
use App\Http\Controllers\Api\V1\Returns\ReturnController;
use App\Http\Controllers\Api\V1\Notifications\NotificationController;
use App\Http\Controllers\Api\V1\Notifications\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\Notifications\RealtimeNotificationController;
use App\Http\Controllers\Api\V1\Admin\CampusController as AdminCampusController;
use App\Http\Controllers\Api\V1\Admin\OrganizationalUnitController as AdminOrganizationalUnitController;
use App\Http\Controllers\Api\V1\Admin\OrganizationalUnitTypeController as AdminOrganizationalUnitTypeController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Api\V1\Admin\StorageLocationController as AdminStorageLocationController;
use App\Http\Controllers\Api\V1\Admin\UserManagementController;
use App\Http\Controllers\Api\V1\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Api\V1\Admin\SystemSettingController as AdminSystemSettingController;
use App\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\V1\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Guest & Public Discovery Routes (FR-10)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/login', [LoginController::class, 'login'])->name('login');
        Route::post('/verify-email', [VerificationController::class, 'verify']);
        Route::post('/resend-verification', [VerificationController::class, 'resend']);
        Route::post('/forgot-password', [PasswordResetController::class, 'request']);
        Route::post('/verify-password-reset', [PasswordResetController::class, 'verifyOtp']);
        Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    });

    Route::prefix('public')->group(function (): void {
        Route::get('/items', [PublicItemController::class, 'index']);
        Route::get('/items/{id}', [PublicItemController::class, 'show']);
        Route::get('/categories', [PublicCategoryController::class, 'index']);
        Route::get('/locations', [PublicLocationController::class, 'index']);
        Route::get('/announcements', [PublicAnnouncementController::class, 'index']);
        Route::get('/track/{reference_code}', [TrackingController::class, 'track'])->middleware('throttle:20,1');
        Route::get('/settings', [PublicSettingController::class, 'index']);
        Route::get('/statistics', [PublicStatisticsController::class, 'index']);
    });

    // FR-44: Secure token-based recipient return confirmation (single-use, link-based confirmation)
    Route::prefix('returns/confirm-token')->group(function (): void {
        Route::get('/{token}', [ReturnController::class, 'getByToken']);
        Route::post('/{token}', [ReturnController::class, 'confirmByToken']);
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated User Routes (Sanctum SPA Session-Cookie)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function (): void {

        // Auth info
        Route::prefix('auth')->group(function (): void {
            Route::get('/me', [LoginController::class, 'me']);
            Route::post('/logout', [LogoutController::class, 'logout']);
            Route::put('/password', [PasswordController::class, 'change']);
        });

        // Active Announcements Feed (Role-filtered)
        Route::get('/announcements/active', [PublicAnnouncementController::class, 'index']);

        // Profile
        Route::get('/profile/summary', [ProfileController::class, 'summary']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);

        // Student / User Items (FR-10)
        Route::prefix('items')->group(function (): void {
            Route::post('/check-duplicate', [ItemController::class, 'checkDuplicate']);
            Route::get('/', [ItemController::class, 'index']);
            Route::post('/lost', [ItemController::class, 'storeLost']);
            Route::post('/found', [ItemController::class, 'storeFound']);
            Route::get('/{id}', [ItemController::class, 'show']);
            Route::put('/{id}', [ItemController::class, 'update']);
            Route::delete('/{id}', [ItemController::class, 'destroy']);
            Route::patch('/{id}/status', [ItemStatusController::class, 'update']);
            Route::patch('/{id}/withdraw', [ItemController::class, 'withdraw']); // FR-18
            Route::post('/{id}/photos', [ItemPhotoController::class, 'store']);
            Route::delete('/{id}/photos/{photoId}', [ItemPhotoController::class, 'destroy']);
        });

        // Student / User Claims (FR-10)
        Route::prefix('claims')->group(function (): void {
            Route::get('/', [ClaimController::class, 'index']);
            Route::post('/', [ClaimController::class, 'store']);
            Route::get('/{id}', [ClaimController::class, 'show']);
            
            // Staff / Admin Claim Decision Gate (FR-11)
            Route::middleware('role:staff,admin')->group(function (): void {
                Route::post('/{id}/review', [ClaimReviewController::class, 'review']);
                Route::post('/{id}/reverse', [ClaimReviewController::class, 'reverse']);
            });
        });

        // Notifications (FR-10)
        Route::prefix('notifications')->group(function (): void {
            Route::get('/stream', [RealtimeNotificationController::class, 'stream']);
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::get('/preferences', [NotificationPreferenceController::class, 'index']);
            Route::put('/preferences', [NotificationPreferenceController::class, 'update']);
        });

        // Active Announcements — served by the public route above (line 89)

        // Physical Returns (FR-11, FR-44, FR-46)
        Route::prefix('returns')->group(function (): void {
            Route::get('/{id}', [ReturnController::class, 'show']);
            Route::post('/{id}/confirm', [ReturnController::class, 'confirm']); // FR-44
        });

        /*
        |--------------------------------------------------------------------------
        | Staff & Security Office Gate (FR-11, FR-13)
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:staff,admin')->group(function (): void {
            // Custody Events & Physical Storage Management (FR-11)
            Route::prefix('custody')->group(function (): void {
                Route::get('/', [CustodyController::class, 'index']);
                Route::post('/', [CustodyController::class, 'store']);
                Route::post('/items/{itemId}/move', [CustodyController::class, 'move']);
                Route::apiResource('storage-locations', CustodyStorageLocationController::class);
            });

            // Physical Returns Management & Export (FR-11, FR-46)
            Route::prefix('returns')->group(function (): void {
                Route::get('/', [ReturnController::class, 'index']);
                Route::post('/', [ReturnController::class, 'store']);
                Route::get('/export/csv', [ReturnController::class, 'exportCsv']); // FR-46
            });

            // Match Suggestions Review (FR-51)
            Route::prefix('match-suggestions')->group(function (): void {
                Route::get('/', [\App\Http\Controllers\Api\V1\Admin\MatchSuggestionController::class, 'index']);
                Route::patch('/{id}', [\App\Http\Controllers\Api\V1\Admin\MatchSuggestionController::class, 'update']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | ICT Administrator Gate (FR-12, FR-13)
        |--------------------------------------------------------------------------
        */
        Route::prefix('admin')->middleware('role:admin')->group(function (): void {
            Route::get('/dashboard/statistics', [DashboardController::class, 'statistics']);
            Route::apiResource('campuses', AdminCampusController::class);
            Route::patch('/campuses/{id}/restore', [AdminCampusController::class, 'restore']);
            Route::apiResource('organizational-units', AdminOrganizationalUnitController::class);
            Route::apiResource('organizational-unit-types', AdminOrganizationalUnitTypeController::class);
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('locations', AdminLocationController::class);
            Route::apiResource('storage-locations', AdminStorageLocationController::class);

            // User & Role Management (FR-09, FR-12)
            Route::prefix('users')->group(function (): void {
                Route::get('/', [UserManagementController::class, 'index']);
                Route::post('/', [UserManagementController::class, 'store']);
                Route::get('/{id}', [UserManagementController::class, 'show']);
                Route::put('/{id}', [UserManagementController::class, 'update']);
                Route::patch('/{id}/role', [UserManagementController::class, 'updateRole']);
                Route::patch('/{id}/toggle-active', [UserManagementController::class, 'toggleActive']);
                Route::get('/{id}/permissions', [UserManagementController::class, 'getPermissions']);
                Route::post('/{id}/permissions', [UserManagementController::class, 'syncPermissions']);
            });

            // Roles & Permissions Management (Dynamic RBAC)
            Route::apiResource('roles', \App\Http\Controllers\Api\V1\Admin\RoleController::class);
            Route::post('/roles/{role}/permissions', [\App\Http\Controllers\Api\V1\Admin\RoleController::class, 'syncPermissions']);
            Route::apiResource('permission-groups', \App\Http\Controllers\Api\V1\Admin\PermissionGroupController::class);
            Route::patch('/permission-groups/{permissionGroup}/toggle-active', [\App\Http\Controllers\Api\V1\Admin\PermissionGroupController::class, 'toggleActive']);
            Route::apiResource('permissions', \App\Http\Controllers\Api\V1\Admin\PermissionController::class);
            Route::patch('/permissions/{permission}/toggle-active', [\App\Http\Controllers\Api\V1\Admin\PermissionController::class, 'toggleActive']);

            // Announcements (FR-12)
            Route::post('/announcements/bulk-toggle', [AdminAnnouncementController::class, 'bulkToggle']);
            Route::post('/announcements/bulk-delete', [AdminAnnouncementController::class, 'bulkDelete']);
            Route::apiResource('announcements', AdminAnnouncementController::class);
            Route::patch('/announcements/{id}/toggle-active', [AdminAnnouncementController::class, 'toggleActive']);

            // System Settings (FR-12)
            Route::get('/settings', [AdminSystemSettingController::class, 'index']);
            Route::put('/settings/{key}', [AdminSystemSettingController::class, 'update']);
            Route::post('/settings/upload-logo', [PublicSettingController::class, 'uploadLogo']);

            // Reports & Audit Logs + CSV Export (FR-12)
            Route::get('/reports', [AdminReportController::class, 'index']);
            Route::post('/reports/generate', [AdminReportController::class, 'generate']);
            Route::get('/reports/{id}/download', [AdminReportController::class, 'download']);
            Route::get('/audit-logs', [AdminAuditLogController::class, 'index']);
            Route::get('/audit-logs/export', [AdminAuditLogController::class, 'exportCsv']);
        });
    });
});
