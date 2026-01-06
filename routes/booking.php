<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Booking & Payment Routes
|--------------------------------------------------------------------------
| Routes for booking flow and payment processing
*/

// Booking Routes
Route::get('/book/{slug}', [BookingController::class, 'create'])->middleware('auth')->name('booking.create');
Route::post('/book/{slug}', [BookingController::class, 'store'])->middleware('auth')->name('booking.store');
Route::get('/booking/{orderNumber}/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/booking/{orderNumber}/confirm-cash', [BookingController::class, 'confirmCash'])->name('booking.confirm-cash');
Route::get('/booking/{orderNumber}/success', [BookingController::class, 'success'])->name('booking.success');

// Payment Callbacks (Midtrans)
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/notification', [PaymentController::class, 'handleNotification'])->name('notification');
    Route::get('/finish', [PaymentController::class, 'finish'])->name('finish');
    Route::get('/unfinish', [PaymentController::class, 'unfinish'])->name('unfinish');
    Route::get('/error', [PaymentController::class, 'error'])->name('error');
    Route::get('/status/{orderNumber}', [PaymentController::class, 'checkStatus'])->name('status');
});
