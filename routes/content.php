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
Route::post('/destinations/{destination}/comments', [App\Http\Controllers\DestinationInteractionController::class, 'storeComment'])
    ->middleware('auth')
    ->name('destinations.comments.store');
Route::get('/destinations/{destination}/comments', [App\Http\Controllers\DestinationInteractionController::class, 'getComments'])
    ->name('destinations.comments.index');

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

// Article Interactions (Public)
Route::post('/articles/{article}/track-view', [App\Http\Controllers\ArticleViewController::class, 'track'])->name('articles.track-view');
Route::post('/articles/views/{view}/time-spent', [App\Http\Controllers\ArticleViewController::class, 'trackTimeSpent'])->name('articles.track-time');
Route::post('/articles/{article}/toggle-like', [App\Http\Controllers\ArticleInteractionController::class, 'toggleLike'])->name('articles.toggle-like');
Route::post('/articles/{article}/toggle-favorite', [App\Http\Controllers\ArticleInteractionController::class, 'toggleFavorite'])->name('articles.toggle-favorite');
Route::get('/articles/{article}/interaction-status', [App\Http\Controllers\ArticleInteractionController::class, 'status'])->name('articles.interaction-status');
Route::post('/articles/{article}/comments', [App\Http\Controllers\ArticleCommentController::class, 'store'])->middleware('auth')->name('articles.comments.store');
Route::get('/my-favorites', [App\Http\Controllers\ArticleInteractionController::class, 'favorites'])->middleware('auth')->name('articles.my-favorites');
Route::get('/my-likes', [App\Http\Controllers\ArticleInteractionController::class, 'likes'])->middleware('auth')->name('articles.my-likes');

// Contact & FAQ
Route::get('/contact', [App\Http\Controllers\PageController::class, 'contact'])->name('contact');
Route::post('/contact', [App\Http\Controllers\PageController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/faq', [App\Http\Controllers\PageController::class, 'faq'])->name('faq');

// Testimonials
Route::get('/testimonials', [App\Http\Controllers\FeedbackController::class, 'index'])->name('testimonials');
Route::get('/testimonials/create', [App\Http\Controllers\FeedbackController::class, 'create'])->name('testimonials.create');
Route::post('/testimonials', [App\Http\Controllers\FeedbackController::class, 'store'])->name('testimonials.store');
