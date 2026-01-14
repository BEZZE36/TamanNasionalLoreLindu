<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Web-based APIs)
|--------------------------------------------------------------------------
| Public API endpoints for AJAX/fetch calls
*/

// Coupon Validation
Route::post('/coupon/validate', [App\Http\Controllers\CouponValidationController::class, 'validateCoupon'])
    ->name('coupon.validate');

// Global Search
Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::get('/search/history', [App\Http\Controllers\SearchController::class, 'getHistory'])->name('search.history');
Route::post('/search/history', [App\Http\Controllers\SearchController::class, 'saveToHistory'])->name('search.history.save');
Route::delete('/search/history', [App\Http\Controllers\SearchController::class, 'clearHistory'])->name('search.history.clear');
Route::delete('/search/history/{id}', [App\Http\Controllers\SearchController::class, 'deleteHistoryItem'])->name('search.history.delete');

// Compare Destinations API
Route::get('/destinations/compare', function (Illuminate\Http\Request $request) {
    $ids = explode(',', $request->get('ids', ''));
    $destinations = App\Models\Destination::whereIn('id', $ids)
        ->active()
        ->get()
        ->map(function ($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'slug' => $d->slug,
                'city' => $d->city,
                'image' => $d->primary_image_url,
                'rating' => number_format($d->average_rating, 1),
                'price' => $d->formatted_price ?? 'Gratis',
                'category' => $d->category ?? 'Alam',
                'facilities' => $d->facilities_count ?? 0,
            ];
        });
    return response()->json(['destinations' => $destinations]);
});

// Destination Comments (public read)
Route::get('/destinations/{destination}/comments', [App\Http\Controllers\DestinationInteractionController::class, 'getComments'])
    ->name('destinations.comments');

// Gallery Comments (public read)
Route::get('/gallery/{gallery}/comments', [App\Http\Controllers\GalleryInteractionController::class, 'getComments'])
    ->name('gallery.comments.get');

// User Status Check API (for blocked account page)
Route::get('/user/check-status', function () {
    $user = auth()->user();

    if (!$user) {
        return response()->json(['status' => 'guest', 'logged_in' => false]);
    }

    return response()->json([
        'status' => $user->status,
        'logged_in' => true,
        'is_blocked' => $user->status === 'blocked',
    ]);
})->name('user.check-status');

// Check email/phone uniqueness for profile validation
Route::post('/user/check-unique', function (Illuminate\Http\Request $request) {
    $user = auth()->user();
    $field = $request->input('field'); // 'email' or 'phone'
    $value = $request->input('value');

    if (!$user || !$field || !$value) {
        return response()->json(['available' => true]);
    }

    // Check if value exists for another user
    $query = App\Models\User::where($field, $value)
        ->where('id', '!=', $user->id);

    $exists = $query->exists();

    return response()->json([
        'available' => !$exists,
        'message' => $exists ? ($field === 'email'
            ? 'Email ini sudah digunakan oleh pengguna lain.'
            : 'Nomor telepon ini sudah digunakan oleh pengguna lain.') : null,
    ]);
})->name('user.check-unique');

// Check newsletter email uniqueness
Route::post('/newsletter/check-email', function (Illuminate\Http\Request $request) {
    $email = $request->input('email');
    $currentEmail = $request->input('current_email'); // Current user's newsletter email

    if (!$email) {
        return response()->json(['available' => true]);
    }

    // Check if email exists in newsletter_subscribers (excluding current email)
    $query = App\Models\NewsletterSubscriber::where('email', $email);

    if ($currentEmail) {
        $query->where('email', '!=', $currentEmail);
    }

    $exists = $query->exists();

    return response()->json([
        'available' => !$exists,
        'message' => $exists ? 'Email ini sudah terdaftar untuk newsletter.' : null,
    ]);
})->name('newsletter.check-email');

// Verify current password for real-time validation
Route::post('/user/verify-password', function (Illuminate\Http\Request $request) {
    $user = auth()->user();
    $password = $request->input('password');

    if (!$user || !$password) {
        return response()->json(['valid' => false]);
    }

    $valid = Illuminate\Support\Facades\Hash::check($password, $user->password);

    return response()->json([
        'valid' => $valid,
        'message' => $valid ? null : 'Password saat ini tidak sesuai.',
    ]);
})->name('user.verify-password');

// Newsletter status polling endpoint for real-time updates
Route::get('/newsletter/status', function () {
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'has_subscription' => false,
            'is_active' => false,
            'disabled_by_admin' => false,
        ]);
    }

    $subscription = App\Models\NewsletterSubscriber::where('user_id', $user->id)->first();

    if (!$subscription) {
        return response()->json([
            'has_subscription' => false,
            'is_active' => false,
            'disabled_by_admin' => false,
        ]);
    }

    return response()->json([
        'has_subscription' => true,
        'is_active' => $subscription->is_active,
        'disabled_by_admin' => $subscription->disabled_by_admin ?? false,
        'email' => $subscription->email,
    ]);
})->name('newsletter.status');
