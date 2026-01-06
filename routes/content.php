<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Content Routes (Public)
|--------------------------------------------------------------------------
| Routes for viewing destinations, flora, fauna, gallery, articles
*/

// Destinations
Route::get('/destinations', [App\Http\Controllers\FrontDestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{slug}', [App\Http\Controllers\FrontDestinationController::class, 'show'])->name('destinations.show');
Route::post('/destinations/{slug}/review', [App\Http\Controllers\FrontDestinationController::class, 'storeReview'])
    ->middleware('auth')
    ->name('destinations.review');

// Explore Map
Route::get('/explore/map', [App\Http\Controllers\ExploreController::class, 'index'])->name('explore.map');

// Flora
Route::get('/flora', [App\Http\Controllers\FloraController::class, 'index'])->name('flora.index');
Route::get('/flora/{flora}', [App\Http\Controllers\FloraController::class, 'show'])->name('flora.show');

// Fauna
Route::get('/fauna', [App\Http\Controllers\FaunaController::class, 'index'])->name('fauna.index');
Route::get('/fauna/{fauna}', [App\Http\Controllers\FaunaController::class, 'show'])->name('fauna.show');

// Gallery
Route::get('/gallery', [App\Http\Controllers\GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{slug}', [App\Http\Controllers\GalleryController::class, 'show'])
    ->name('gallery.show')
    ->where('slug', '[a-z0-9\-]+');
Route::post('/gallery/{id}/track-view', [App\Http\Controllers\GalleryController::class, 'trackView'])
    ->name('gallery.track-view')
    ->where('id', '[0-9]+');
Route::get('/gallery/{gallery}/comments', [App\Http\Controllers\GalleryInteractionController::class, 'getComments'])
    ->name('gallery.comments');

// Blog & Articles
Route::get('/blog', [App\Http\Controllers\ArticleController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [App\Http\Controllers\ArticleController::class, 'show'])->name('blog.show');

// News
Route::get('/news', [App\Http\Controllers\NewsController::class, 'index'])->name('news');
Route::get('/news/{slug}', [App\Http\Controllers\NewsController::class, 'show'])->name('news.show');

// Contact & FAQ
Route::get('/contact', [App\Http\Controllers\PageController::class, 'contact'])->name('contact');
Route::post('/contact', [App\Http\Controllers\PageController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/faq', [App\Http\Controllers\PageController::class, 'faq'])->name('faq');

// Testimonials
Route::get('/testimonials', [App\Http\Controllers\FeedbackController::class, 'index'])->name('testimonials');
Route::get('/testimonials/create', [App\Http\Controllers\FeedbackController::class, 'create'])->name('testimonials.create');
Route::post('/testimonials', [App\Http\Controllers\FeedbackController::class, 'store'])->name('testimonials.store');
