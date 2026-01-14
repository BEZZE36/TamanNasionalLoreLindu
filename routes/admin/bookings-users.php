<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;

/*
|--------------------------------------------------------------------------
| Admin Routes - Bookings, Users, Tickets
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin', 'menu.access'])->group(function () {
    // ============ BOOKINGS ============
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [AdminBookingController::class, 'index'])->name('index');
        Route::get('/create', [AdminBookingController::class, 'create'])->name('create');
        Route::post('/', [AdminBookingController::class, 'store'])->name('store');
        Route::get('/{booking}/edit', [AdminBookingController::class, 'edit'])->name('edit');
        Route::put('/{booking}', [AdminBookingController::class, 'update'])->name('update');
        Route::get('/{booking}', [AdminBookingController::class, 'show'])->name('show');
        Route::patch('/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('update-status');
        Route::post('/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('cancel');
        Route::post('/{booking}/send-ticket', [AdminBookingController::class, 'sendTicket'])->name('send-ticket');
        Route::get('/{booking}/invoice', [AdminBookingController::class, 'downloadInvoice'])->name('invoice');
        Route::delete('/{booking}', [AdminBookingController::class, 'destroy'])->name('destroy');
    });
    Route::get('/bookings-export', [AdminBookingController::class, 'export'])->name('bookings.export');

    // ============ BOOKINGS API ============
    Route::get('/api/bookings/pending-count', [AdminBookingController::class, 'pendingCount'])->name('api.bookings.pending-count');

    // ============ URL ALIASES (for backwards compatibility) ============
    Route::get('/scan', fn() => redirect()->route('admin.tickets.scan'))->name('scan.redirect');
    Route::get('/galleries', fn() => redirect()->route('admin.gallery.index'))->name('galleries.redirect');
    Route::get('/reports', fn() => redirect()->route('admin.analytics.index'))->name('reports.redirect');

    // ============ USERS ============
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::patch('/{user}/status', [AdminUserController::class, 'updateStatus'])->name('update-status');
        Route::post('/{user}/block', [AdminUserController::class, 'block'])->name('block');
        Route::post('/{user}/unblock', [AdminUserController::class, 'unblock'])->name('unblock');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });

    // ============ TICKETS ============
    // QR Scanner (must be before resource routes)
    Route::get('/tickets/scan', [\App\Http\Controllers\Admin\TicketScanController::class, 'scan'])->name('tickets.scan');
    Route::post('/tickets/validate', [\App\Http\Controllers\Admin\TicketScanController::class, 'checkTicket'])->name('tickets.api.validate');
    Route::post('/tickets/process-payment', [\App\Http\Controllers\Admin\TicketScanController::class, 'processPayment'])->name('tickets.process-payment');
    Route::post('/tickets/validate-entry', [\App\Http\Controllers\Admin\TicketScanController::class, 'validateEntry'])->name('tickets.validate-entry');

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [AdminTicketController::class, 'index'])->name('index');
        Route::get('/validate', [AdminTicketController::class, 'validatePage'])->name('validate');
        Route::post('/redeem', [AdminTicketController::class, 'redeem'])->name('redeem');
        Route::get('/{ticket}', [AdminTicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/manual-validate', [AdminTicketController::class, 'manualValidate'])->name('manual-validate');
        Route::get('/check/{code}', [AdminTicketController::class, 'checkStatus'])->name('check-status');
    });

    // Coupons
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);
    Route::post('/coupons/{coupon}/toggle', [\App\Http\Controllers\Admin\CouponController::class, 'toggle'])->name('coupons.toggle');
});
