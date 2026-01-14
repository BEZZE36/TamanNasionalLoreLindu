<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| Guest and authentication-related routes
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Register with OTP
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register/send-otp', [AuthController::class, 'sendRegisterOtp'])->name('register.send-otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyRegisterOtp'])->name('register.verify-otp');
    Route::post('/register/resend-otp', [AuthController::class, 'resendRegisterOtp'])->name('register.resend-otp');

    // Forgot Password with OTP
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password/find', [AuthController::class, 'findAccount'])->name('password.find');
    Route::post('/forgot-password/send-otp', [AuthController::class, 'sendResetOtp'])->name('password.send-otp');
    Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp'])->name('password.verify-otp');
    Route::post('/forgot-password/resend-otp', [AuthController::class, 'resendResetOtp'])->name('password.resend-otp');
    Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');

    // Google OAuth
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// Logout (requires auth)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Blocked Account Page
Route::get('/account-blocked', function () {
    return \Inertia\Inertia::render('Auth/BlockedAccount');
})->name('account.blocked')->middleware('auth');
