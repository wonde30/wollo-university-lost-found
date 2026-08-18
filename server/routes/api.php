<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\VerificationController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\LocationController as PublicLocationController;
use App\Http\Controllers\Api\V1\Public\PublicItemController;
use App\Http\Controllers\Api\V1\Public\TrackingController;
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
use App\Http\Controllers\Api\V1\Admin\CampusController as AdminCampusController;
use App\Http\Controllers\Api\V1\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Api\V1\Admin\StorageLocationController as AdminStorageLocationController;
use App\Http\Controllers\Api\V1\Admin\UserManagementController;
use App\Http\Controllers\Api\V1\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Api\V1\Admin\SystemSettingController as AdminSystemSettingController;
use App\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\V1\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Guest & Public Discovery Routes (FR-10)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/login', [LoginController::class, 'login']);
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
        Route::get('/track/{reference_code}', [TrackingController::class, 'track'])->middleware('throttle:20,1');
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
        });

        // Student / User Items (FR-10)
        Route::prefix('items')->group(function (): void {
            Route::get('/', [ItemController::class, 'index']);
            Route::post('/lost', [ItemController::class, 'storeLost']);
            Route::post('/found', [ItemController::class, 'storeFound']);
            Route::get('/{id}', [ItemController::class, 'show']);
            Route::put('/{id}', [ItemController::class, 'update']);
            Route::delete('/{id}', [ItemController::class, 'destroy']);
            Route::patch('/{id}/status', [ItemStatusController::class, 'update']);
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
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::get('/preferences', [NotificationPreferenceController::class, 'index']);
            Route::put('/preferences', [NotificationPreferenceController::class, 'update']);
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

            // Physical Returns Processing (FR-11)
            Route::prefix('returns')->group(function (): void {
                Route::get('/', [ReturnController::class, 'index']);
                Route::post('/', [ReturnController::class, 'store']);
                Route::get('/{id}', [ReturnController::class, 'show']);
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
            Route::apiResource('departments', AdminDepartmentController::class);
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('locations', AdminLocationController::class);
            Route::apiResource('storage-locations', AdminStorageLocationController::class);

            // User & Role Management (FR-09, FR-12)
            Route::prefix('users')->group(function (): void {
                Route::get('/', [UserManagementController::class, 'index']);
                Route::get('/{id}', [UserManagementController::class, 'show']);
                Route::put('/{id}', [UserManagementController::class, 'update']);
                Route::patch('/{id}/role', [UserManagementController::class, 'updateRole']);
                Route::patch('/{id}/toggle-active', [UserManagementController::class, 'toggleActive']);
            });

            // Announcements (FR-12)
            Route::apiResource('announcements', AdminAnnouncementController::class)->except(['update']);

            // System Settings (FR-12)
            Route::get('/settings', [AdminSystemSettingController::class, 'index']);
            Route::put('/settings/{key}', [AdminSystemSettingController::class, 'update']);

            // Reports & Audit Logs + CSV Export (FR-12)
            Route::get('/reports', [AdminReportController::class, 'index']);
            Route::post('/reports/generate', [AdminReportController::class, 'generate']);
            Route::get('/audit-logs', [AdminAuditLogController::class, 'index']);
            Route::get('/audit-logs/export', [AdminAuditLogController::class, 'exportCsv']);
        });
    });
});
