<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| User Authenticated Routes
|--------------------------------------------------------------------------
| Routes that require user authentication
*/

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Stats
        $stats = [
            'totalBookings' => $user->bookings()->count(),
            'activeTickets' => $user->bookings()->where('status', 'paid')->count(),
            'pendingPayments' => $user->bookings()->where('status', 'pending')->count(),
        ];

        // Recent bookings with relationships
        $recentBookings = $user->bookings()
            ->with(['destination.images', 'payment'])
            ->latest()
            ->take(3)
            ->get();

        $recentNews = \App\Models\Article::published()
            ->where('category', 'berita')
            ->latest('published_at')
            ->take(3)
            ->get();

        $recentArticles = \App\Models\Article::published()
            ->where('category', '!=', 'berita')
            ->latest('published_at')
            ->take(3)
            ->get();

        return \Inertia\Inertia::render('User/Dashboard', compact(
            'stats',
            'recentBookings',
            'recentNews',
            'recentArticles'
        ));
    })->name('dashboard');

    // My Bookings
    Route::get('/my-bookings', function () {
        $bookings = auth()->user()->bookings()
            ->with(['destination.images', 'payment'])
            ->latest()
            ->paginate(10);
        return \Inertia\Inertia::render('User/Bookings/Index', compact('bookings'));
    })->name('user.bookings');

    Route::get('/my-reviews', [\App\Http\Controllers\User\UserReviewController::class, 'index'])->name('user.reviews');
    Route::get('/my-bookings/{orderNumber}', [BookingController::class, 'show'])->name('user.bookings.show');
    Route::get('/booking/{orderNumber}/ticket', [BookingController::class, 'downloadTicket'])->name('booking.ticket');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('user.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('user.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('user.profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('user.profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('user.profile.avatar.delete');


    // Newsletter Management
    Route::prefix('newsletter')->name('user.newsletter.')->group(function () {
        Route::post('/unsubscribe', [\App\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
        Route::put('/update-email', [\App\Http\Controllers\NewsletterController::class, 'updateEmail'])->name('update-email');
        Route::post('/resubscribe', [\App\Http\Controllers\NewsletterController::class, 'resubscribe'])->name('resubscribe');
    });


    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [App\Http\Controllers\NotificationController::class, 'getRecent'])->name('recent');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/clear-all', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('clear-all');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });

    // Wishlist
    Route::get('/wishlist', [App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{destination}', [App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // User Interactions (comments, likes)
    Route::post('/gallery/{gallery}/like', [App\Http\Controllers\GalleryInteractionController::class, 'toggleLike'])->name('gallery.like');
    Route::post('/gallery/{gallery}/comment', [App\Http\Controllers\GalleryInteractionController::class, 'storeComment'])->name('gallery.comment');
    Route::delete('/gallery/comment/{comment}', [App\Http\Controllers\GalleryInteractionController::class, 'deleteComment'])->name('gallery.comment.delete');

    Route::post('/flora/{flora}/comment', [App\Http\Controllers\FloraInteractionController::class, 'storeComment'])->name('flora.comment');
    Route::delete('/flora/comment/{comment}', [App\Http\Controllers\FloraInteractionController::class, 'deleteComment'])->name('flora.comment.delete');

    Route::post('/fauna/{fauna}/comment', [App\Http\Controllers\FaunaInteractionController::class, 'storeComment'])->name('fauna.comment');
    Route::delete('/fauna/comment/{comment}', [App\Http\Controllers\FaunaInteractionController::class, 'deleteComment'])->name('fauna.comment.delete');

    Route::post('/api/destinations/{destination}/comment', [App\Http\Controllers\DestinationInteractionController::class, 'storeComment'])
        ->name('destinations.comment.store');
});
