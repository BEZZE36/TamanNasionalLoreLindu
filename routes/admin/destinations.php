<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Destinations
|--------------------------------------------------------------------------
| All destination management routes
*/

Route::middleware(['auth:admin', 'menu.access'])->group(function () {
    // Statistics & Export (before resource)
    Route::get('destinations/statistics', [\App\Http\Controllers\Admin\DestinationStatsController::class, 'index'])->name('destinations.stats');
    Route::get('destinations/export/{format}', [\App\Http\Controllers\Admin\DestinationStatsController::class, 'export'])->name('destinations.export');
    Route::get('destinations/comments', [\App\Http\Controllers\Admin\AdminDestinationCommentController::class, 'index'])->name('destinations.comments.index');

    // Bulk & Reorder
    Route::post('destinations/bulk-delete', [AdminDestinationController::class, 'bulkDelete'])->name('destinations.bulk-delete');
    Route::post('destinations/reorder', [AdminDestinationController::class, 'reorder'])->name('destinations.reorder');

    // Comments Management
    Route::prefix('destinations/comments')->name('destinations.comments.')->group(function () {
        Route::post('/', [\App\Http\Controllers\Admin\AdminDestinationCommentController::class, 'store'])->name('store');
        Route::post('/batch-delete', [\App\Http\Controllers\Admin\AdminDestinationCommentController::class, 'batchDelete'])->name('batch-delete');
        Route::post('/{comment}/reply', [\App\Http\Controllers\Admin\AdminDestinationCommentController::class, 'reply'])->name('reply');
        Route::delete('/{comment}', [\App\Http\Controllers\Admin\AdminDestinationCommentController::class, 'destroy'])->name('destroy');
        Route::post('/{comment}/toggle-status', [\App\Http\Controllers\Admin\AdminDestinationCommentController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{comment}/toggle-pin', [\App\Http\Controllers\Admin\AdminDestinationCommentController::class, 'togglePin'])->name('toggle-pin');
    });

    // Resource Routes
    Route::resource('destinations', AdminDestinationController::class);
    Route::resource('destination-categories', \App\Http\Controllers\Admin\DestinationCategoryController::class)->except(['show', 'create', 'edit']);

    // Additional Actions
    Route::delete('/destinations/image/{image}', [AdminDestinationController::class, 'deleteImage'])->name('destinations.delete-image');
    Route::post('/destinations/image/{image}/primary', [AdminDestinationController::class, 'setPrimaryImage'])->name('destinations.set-primary-image');
    Route::post('destinations/{destination}/toggle-active', [AdminDestinationController::class, 'toggleActive'])->name('destinations.toggle-active');
    Route::post('destinations/{destination}/toggle-featured', [AdminDestinationController::class, 'toggleFeatured'])->name('destinations.toggle-featured');
    Route::post('destinations/{destination}/duplicate', [AdminDestinationController::class, 'duplicate'])->name('destinations.duplicate');
});
