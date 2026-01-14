<?php

// Temporary test route for push notification
use Illuminate\Support\Facades\Route;

Route::get('/test-push', function () {
    $service = app(\App\Services\PushNotificationService::class);
    $result = $service->broadcast('🔔 Test Push Notification', 'Ini adalah test push notification dari TNLL!', '/');

    return response()->json([
        'message' => 'Push notification sent',
        'result' => $result
    ]);
});
