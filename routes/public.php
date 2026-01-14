<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Routes that don't require authentication
*/

// Home Page
Route::get('/', function () {
    $destinations = \App\Models\Destination::with(['category', 'images', 'prices'])
        ->where('is_active', true)
        ->take(10)
        ->get()
        ->map(function ($dest) {
            return [
                'id' => $dest->id,
                'slug' => $dest->slug,
                'name' => $dest->name,
                'short_description' => $dest->short_description,
                'image' => $dest->primary_image_url,
                'category' => $dest->category,
                'rating' => $dest->rating ?? '4.8',
                'city' => $dest->city,
                'address' => $dest->address,
            ];
        });

    // Galleries for home gallery section
    $galleries = \App\Models\Gallery::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->with(['category'])
        ->take(10)
        ->get()
        ->map(function ($gallery) {
            return [
                'id' => $gallery->id,
                'slug' => $gallery->slug,
                'image' => $gallery->image_url ?? asset('assets/logo.png'),
                'title' => $gallery->title,
                'location' => $gallery->location ?? '',
                'description' => strip_tags($gallery->description ?? ''),
                'category' => $gallery->category?->name ?? 'Umum',
                'mediaCount' => $gallery->media()->count() + 1,
            ];
        });

    // Articles for blog section (exclude berita/news category)
    $articles = \App\Models\Article::published()
        ->where('category', '!=', 'berita')
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->map(function ($article) {
            return [
                'id' => $article->id,
                'slug' => $article->slug,
                'title' => $article->title,
                'excerpt' => $article->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($article->content), 100),
                'image_url' => $article->image_url,
                'category_label' => $article->category_label ?? 'Artikel',
                'category_color' => $article->category_color ?? 'primary',
                'created_at' => $article->created_at->format('d M Y'),
            ];
        });

    // News for news section (using Article model with category='berita')
    $news = \App\Models\Article::published()
        ->where('category', 'berita')
        ->orderBy('published_at', 'desc')
        ->take(10)
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'slug' => $item->slug,
                'title' => $item->title,
                'excerpt' => $item->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($item->content), 100),
                'image_url' => $item->image_url,
                'category' => $item->category_label,
                'created_at' => $item->formatted_date,
                'read_time' => $item->reading_time,
            ];
        });

    $faqItems = \App\Models\Setting::getSetting('faq_items', []);
    if (is_string($faqItems)) {
        $faqItems = json_decode($faqItems, true) ?? [];
    }

    // Testimonials for home section
    $testimonials = \App\Models\Feedback::where('is_published', true)
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->display_name_text,
                'rating' => $item->rating,
                'content' => $item->message,
                'destination' => $item->destination?->name ?? null,
                'created_at' => $item->created_at,
                'is_verified' => true,
            ];
        });

    $avgRating = \App\Models\Feedback::where('is_published', true)->avg('rating') ?? 4.8;
    $totalReviews = \App\Models\Feedback::where('is_published', true)->count();

    $stats = [
        'destinations' => \App\Models\Destination::count(),
        'flora' => \App\Models\Flora::count(),
        'fauna' => \App\Models\Fauna::count(),
        'visitors' => \App\Models\VisitorLog::count(),
    ];

    return Inertia::render('Public/Home', [
        'destinations' => $destinations,
        'galleries' => $galleries,
        'articles' => $articles,
        'news' => $news,
        'faqItems' => array_slice($faqItems, 0, 6),
        'testimonials' => $testimonials,
        'avgRating' => $avgRating,
        'totalReviews' => $totalReviews,
        'stats' => $stats,
    ]);
})->name('home');

// Chatbot
Route::post('/chat', [App\Http\Controllers\ChatbotController::class, 'chat'])->name('chat');

// PWA Offline Page
Route::get('/offline', fn() => Inertia::render('Public/Offline'))->name('offline');

// Sitemap
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Language Switch
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

// Static Pages
Route::get('/about', fn() => Inertia::render('Public/About'))->name('about');
Route::get('/privacy', function () {
    $content = \App\Models\Setting::getValue('privacy_policy', '');
    return Inertia::render('Public/Privacy', ['content' => $content]);
})->name('privacy');
Route::get('/terms', function () {
    $content = \App\Models\Setting::getValue('terms_conditions', '');
    return Inertia::render('Public/Terms', ['content' => $content]);
})->name('terms');
Route::get('/compare', fn() => Inertia::render('Public/Destinations/Compare'))->name('destinations.compare');
Route::get('/feedback', fn() => redirect()->route('testimonials'))->name('feedback.index');

// Public Announcements API (without api prefix to match existing usage)
Route::get('/api/announcements/public', [App\Http\Controllers\PublicAnnouncementController::class, 'index'])
    ->name('announcements.public');

// Newsletter Subscription
Route::post('/newsletter/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])
    ->name('newsletter.subscribe');

// Push Notification Subscription
Route::prefix('push')->name('push.')->group(function () {
    Route::get('/vapid-public-key', [App\Http\Controllers\PushSubscriptionController::class, 'vapidPublicKey'])
        ->name('vapid-key');
    Route::post('/subscribe', [App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])
        ->name('subscribe')->middleware('auth');
    Route::post('/unsubscribe', [App\Http\Controllers\PushSubscriptionController::class, 'unsubscribe'])
        ->name('unsubscribe')->middleware('auth');
    Route::get('/status', [App\Http\Controllers\PushSubscriptionController::class, 'status'])
        ->name('status');
});

