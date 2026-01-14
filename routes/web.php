<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Main Entry Point
|--------------------------------------------------------------------------
| This file loads all modular route files for better organization.
| Each route file is kept under 250 lines for maintainability.
|
| Route Files:
| - public.php      : Home, static pages, PWA
| - auth.php        : Login, register, OAuth
| - user.php        : User dashboard, profile, notifications
| - booking.php     : Booking & payment flow
| - content.php     : Destinations, flora, fauna, gallery, blog
| - api-web.php     : Public API endpoints
| - images.php      : Image serving routes
| - admin/*.php     : Admin panel routes (split into modules)
*/

// ============================================
// PUBLIC ROUTES
// ============================================
require __DIR__ . '/public.php';

// Temporary test route for push notification
require __DIR__ . '/test-push.php';

// ============================================
// AUTHENTICATION ROUTES
// ============================================
require __DIR__ . '/auth.php';

// ============================================
// USER AUTHENTICATED ROUTES
// ============================================
require __DIR__ . '/user.php';

// ============================================
// BOOKING & PAYMENT ROUTES
// ============================================
require __DIR__ . '/booking.php';

// ============================================
// CONTENT ROUTES (Destinations, Flora, Fauna, Gallery, Blog)
// ============================================
require __DIR__ . '/content.php';

// ============================================
// API ROUTES (Web-based)
// ============================================
Route::prefix('api')->name('api.')->group(function () {
    require __DIR__ . '/api-web.php';
});

// ============================================
// IMAGE SERVING ROUTES
// ============================================
Route::prefix('images')->name('images.')->group(function () {
    require __DIR__ . '/images.php';
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::prefix('admin')->name('admin.')->group(function () {
    require __DIR__ . '/admin/core.php';
    require __DIR__ . '/admin/destinations.php';
    require __DIR__ . '/admin/flora-fauna.php';
    require __DIR__ . '/admin/gallery.php';
    require __DIR__ . '/admin/bookings-users.php';
    require __DIR__ . '/admin/content.php';
    require __DIR__ . '/admin/ai.php';
});
