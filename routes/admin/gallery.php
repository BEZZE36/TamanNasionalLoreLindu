<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Gallery & Media
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin', 'menu.access'])->group(function () {
    // Media Actions
    Route::delete('/gallery-media/{media}', [AdminGalleryController::class, 'destroyMedia'])->name('gallery.destroy-media');
    Route::post('/gallery-media/bulk-delete', [AdminGalleryController::class, 'bulkDeleteMedia'])->name('gallery.media.bulk-delete');
    Route::post('/gallery-media/{media}/crop', [AdminGalleryController::class, 'cropMedia'])->name('gallery.crop-media');

    // Bulk & Reorder
    Route::post('/gallery/bulk-delete', [AdminGalleryController::class, 'bulkDelete'])->name('gallery.bulk-delete');
    Route::post('/gallery/reorder', [AdminGalleryController::class, 'reorder'])->name('gallery.reorder');

    // Statistics & Export
    Route::get('/gallery/statistics', [\App\Http\Controllers\Admin\GalleryStatsController::class, 'index'])->name('gallery.stats');
    Route::get('/gallery/export/{format}', [\App\Http\Controllers\Admin\GalleryStatsController::class, 'export'])->name('gallery.export');

    // Gallery Item Actions
    Route::post('/gallery/{gallery}/toggle-active', [AdminGalleryController::class, 'toggleActive'])->name('gallery.toggle-active');
    Route::post('/gallery/{gallery}/toggle-featured', [AdminGalleryController::class, 'toggleFeatured'])->name('gallery.toggle-featured');
    Route::post('/gallery/{gallery}/duplicate', [AdminGalleryController::class, 'duplicate'])->name('gallery.duplicate');
    Route::get('/gallery/{gallery}/preview', [AdminGalleryController::class, 'preview'])->name('gallery.preview');

    // Comments
    Route::prefix('gallery/comments')->name('gallery.comments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminGalleryCommentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\AdminGalleryCommentController::class, 'store'])->name('store');
        Route::post('/{comment}/reply', [\App\Http\Controllers\Admin\AdminGalleryCommentController::class, 'reply'])->name('reply');
        Route::delete('/{comment}', [\App\Http\Controllers\Admin\AdminGalleryCommentController::class, 'destroy'])->name('destroy');
        Route::post('/batch-delete', [\App\Http\Controllers\Admin\AdminGalleryCommentController::class, 'batchDelete'])->name('batch-delete');
        Route::post('/{comment}/toggle-status', [\App\Http\Controllers\Admin\AdminGalleryCommentController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{comment}/toggle-pin', [\App\Http\Controllers\Admin\AdminGalleryCommentController::class, 'togglePin'])->name('toggle-pin');
    });

    // Resources
    Route::resource('gallery', AdminGalleryController::class);
    Route::resource('gallery-categories', \App\Http\Controllers\Admin\GalleryCategoryController::class);

    // Banners
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::post('/banners/reorder', [\App\Http\Controllers\Admin\BannerController::class, 'reorder'])->name('banners.reorder');

    // TinyMCE Image Upload
    Route::post('/upload-image', function (\Illuminate\Http\Request $request) {
        $request->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120']);

        $imageData = \App\Services\ImageService::storeToDatabase($request->file('file'));
        $hash = \App\Services\ImageService::generateHash($imageData['data']);

        \App\Models\Upload::updateOrCreate(
            ['hash' => $hash],
            [
                'filename' => $imageData['filename'],
                'image_data' => $imageData['data'],
                'image_mime' => $imageData['mime'],
                'size' => $imageData['size'],
            ]
        );

        return response()->json(['location' => route('images.upload', $hash)]);
    })->name('upload.image');
});
