<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FloraController as AdminFloraController;
use App\Http\Controllers\Admin\FaunaController as AdminFaunaController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Flora & Fauna
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin', 'menu.access'])->group(function () {
    // ============ FLORA ============
    Route::prefix('flora')->name('flora.')->group(function () {
        // Bulk & Reorder
        Route::post('/bulk-delete', [AdminFloraController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/reorder', [AdminFloraController::class, 'reorder'])->name('reorder');

        // Dashboard
        Route::get('/dashboard', [AdminFloraController::class, 'dashboard'])->name('dashboard');

        // Statistics
        Route::get('/statistics', [\App\Http\Controllers\Admin\FloraStatsController::class, 'index'])->name('stats');

        // Comments
        Route::prefix('comments')->name('comments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminFloraCommentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\AdminFloraCommentController::class, 'store'])->name('store');
            Route::post('/{comment}/reply', [\App\Http\Controllers\Admin\AdminFloraCommentController::class, 'reply'])->name('reply');
            Route::delete('/{comment}', [\App\Http\Controllers\Admin\AdminFloraCommentController::class, 'destroy'])->name('destroy');
            Route::post('/batch-delete', [\App\Http\Controllers\Admin\AdminFloraCommentController::class, 'batchDelete'])->name('batch-delete');
            Route::post('/{comment}/toggle-status', [\App\Http\Controllers\Admin\AdminFloraCommentController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{comment}/toggle-pin', [\App\Http\Controllers\Admin\AdminFloraCommentController::class, 'togglePin'])->name('toggle-pin');
        });

        // Image Actions
        Route::delete('/image/{image}', [AdminFloraController::class, 'deleteImage'])->name('delete-image');
        Route::post('/image/bulk-delete', [AdminFloraController::class, 'bulkDeleteMedia'])->name('image.bulk-delete');
    });

    // Flora Item Actions
    Route::post('flora/{flora}/toggle-active', [AdminFloraController::class, 'toggleActive'])->name('flora.toggle-active');
    Route::post('flora/{flora}/toggle-featured', [AdminFloraController::class, 'toggleFeatured'])->name('flora.toggle-featured');
    Route::post('flora/{flora}/duplicate', [AdminFloraController::class, 'duplicate'])->name('flora.duplicate');
    Route::resource('flora', AdminFloraController::class);

    // ============ FAUNA ============
    Route::prefix('fauna')->name('fauna.')->group(function () {
        // Bulk & Reorder
        Route::post('/bulk-delete', [AdminFaunaController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/reorder', [AdminFaunaController::class, 'reorder'])->name('reorder');

        // Dashboard
        Route::get('/dashboard', [AdminFaunaController::class, 'dashboard'])->name('dashboard');

        // Statistics
        Route::get('/statistics', [\App\Http\Controllers\Admin\FaunaStatsController::class, 'index'])->name('stats');

        // Comments
        Route::prefix('comments')->name('comments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminFaunaCommentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\AdminFaunaCommentController::class, 'store'])->name('store');
            Route::post('/{comment}/reply', [\App\Http\Controllers\Admin\AdminFaunaCommentController::class, 'reply'])->name('reply');
            Route::delete('/{comment}', [\App\Http\Controllers\Admin\AdminFaunaCommentController::class, 'destroy'])->name('destroy');
            Route::post('/batch-delete', [\App\Http\Controllers\Admin\AdminFaunaCommentController::class, 'batchDelete'])->name('batch-delete');
            Route::post('/{comment}/toggle-status', [\App\Http\Controllers\Admin\AdminFaunaCommentController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{comment}/toggle-pin', [\App\Http\Controllers\Admin\AdminFaunaCommentController::class, 'togglePin'])->name('toggle-pin');
        });

        // Image Actions
        Route::delete('/image/{image}', [AdminFaunaController::class, 'deleteImage'])->name('delete-image');
        Route::post('/image/bulk-delete', [AdminFaunaController::class, 'bulkDeleteMedia'])->name('image.bulk-delete');
        Route::delete('/{id}/cover', [AdminFaunaController::class, 'deleteCoverImage'])->name('delete-cover-image');
    });

    // Fauna Item Actions
    Route::post('fauna/{fauna}/toggle-active', [AdminFaunaController::class, 'toggleActive'])->name('fauna.toggle-active');
    Route::post('fauna/{fauna}/toggle-featured', [AdminFaunaController::class, 'toggleFeatured'])->name('fauna.toggle-featured');
    Route::post('fauna/{fauna}/duplicate', [AdminFaunaController::class, 'duplicate'])->name('fauna.duplicate');
    Route::resource('fauna', AdminFaunaController::class);
});
