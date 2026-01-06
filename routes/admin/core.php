<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Core
|--------------------------------------------------------------------------
| Dashboard, auth, and core admin functionality
*/

// Admin Root Redirect
Route::get('/', function () {
    return auth('admin')->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
});

// Admin Guest Routes (Login)
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// Admin Authenticated Routes
Route::middleware(['auth:admin', 'menu.access'])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/global-search', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global-search');

    // Analytics & Reports
    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])->name('revenue');
        Route::get('/visitors', [\App\Http\Controllers\Admin\ReportController::class, 'visitors'])->name('visitors');
        Route::get('/destinations', [\App\Http\Controllers\Admin\ReportController::class, 'destinations'])->name('destinations');
        Route::get('/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('export');
    });

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Admin\AdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\Admin\AdminController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Admin\AdminController::class, 'updatePassword'])->name('profile.password');

    // Admin Management
    Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class);
    Route::patch('/admins/{admin}/toggle', [\App\Http\Controllers\Admin\AdminController::class, 'toggleStatus'])->name('admins.toggle');

    // Admin API for real-time validation
    Route::post('/api/check-admin-email', [\App\Http\Controllers\Admin\AdminController::class, 'checkEmail'])->name('api.admins.check-email');
    Route::post('/api/check-admin-username', [\App\Http\Controllers\Admin\AdminController::class, 'checkUsername'])->name('api.admins.check-username');
    Route::post('/api/generate-username', [\App\Http\Controllers\Admin\AdminController::class, 'generateUsername'])->name('api.admins.generate-username');

    // Role Management
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::get('/roles-admins', [\App\Http\Controllers\Admin\RoleController::class, 'admins'])->name('roles.admins');
    Route::post('/roles-admins/{admin}', [\App\Http\Controllers\Admin\RoleController::class, 'assignRole'])->name('roles.assign');

    // Activity Logs
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activityLog}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('show');
        Route::delete('/{activityLog}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'destroy'])->name('destroy');
        Route::post('/clear', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clear'])->name('clear');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
        Route::get('/api', [\App\Http\Controllers\Admin\NotificationController::class, 'apiIndex'])->name('api');
        Route::post('/api/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::post('/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [\App\Http\Controllers\Admin\NotificationController::class, 'clearAll'])->name('clear');
    });

    // Database Management
    Route::prefix('database')->name('database.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('index');
        Route::post('/backup', [\App\Http\Controllers\Admin\DatabaseController::class, 'backupCreate'])->name('backup.create');
        Route::get('/backup/{filename}/download', [\App\Http\Controllers\Admin\DatabaseController::class, 'backupDownload'])->name('backup.download');
        Route::delete('/backup/{filename}', [\App\Http\Controllers\Admin\DatabaseController::class, 'backupDestroy'])->name('backup.destroy');
        Route::post('/import', [\App\Http\Controllers\Admin\DatabaseController::class, 'importStore'])->name('import.store');

        // Auto Backup Settings
        Route::get('/auto-backup-settings', [\App\Http\Controllers\Admin\DatabaseController::class, 'getAutoBackupSettings'])->name('auto-backup.get');
        Route::post('/auto-backup-settings', [\App\Http\Controllers\Admin\DatabaseController::class, 'updateAutoBackupSettings'])->name('auto-backup.update');

        // API for auto-refresh
        Route::get('/api/list', [\App\Http\Controllers\Admin\DatabaseController::class, 'getBackupsList'])->name('api.list');
    });

    // Bulk Actions
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::post('/delete', [\App\Http\Controllers\Admin\BulkActionController::class, 'delete'])->name('delete');
        Route::post('/update-status', [\App\Http\Controllers\Admin\BulkActionController::class, 'updateStatus'])->name('update-status');
        Route::get('/export', [\App\Http\Controllers\Admin\BulkActionController::class, 'export'])->name('export');
    });

    // Export Data
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/bookings', [\App\Http\Controllers\Admin\ExportController::class, 'bookings'])->name('bookings');
        Route::get('/users', [\App\Http\Controllers\Admin\ExportController::class, 'users'])->name('users');
        Route::get('/revenue', [\App\Http\Controllers\Admin\ExportController::class, 'revenue'])->name('revenue');
    });

    // File Manager
    Route::get('/file-manager', [\App\Http\Controllers\Admin\FileManagerController::class, 'index'])->name('file-manager.index');
    Route::delete('/file-manager/{upload}', [\App\Http\Controllers\Admin\FileManagerController::class, 'destroy'])->name('file-manager.destroy');
});
