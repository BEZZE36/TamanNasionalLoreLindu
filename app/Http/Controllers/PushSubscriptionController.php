<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Subscribe to push notifications.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'nullable|string',
            'keys.auth' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        try {
            $subscription = PushSubscription::subscribe($user->id, [
                'endpoint' => $request->input('endpoint'),
                'keys' => [
                    'p256dh' => $request->input('keys.p256dh'),
                    'auth' => $request->input('keys.auth'),
                ],
                'contentEncoding' => $request->input('contentEncoding', 'aes128gcm'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push subscription saved successfully',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save push subscription',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unsubscribe from push notifications.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $deleted = PushSubscription::unsubscribeByEndpoint(
            $user->id,
            $request->input('endpoint')
        );

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'Push subscription removed successfully'
                : 'Subscription not found',
        ]);
    }

    /**
     * Check subscription status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'subscribed' => false,
                'authenticated' => false,
            ]);
        }

        $endpoint = $request->input('endpoint');

        if ($endpoint) {
            $subscription = PushSubscription::where('user_id', $user->id)
                ->where('endpoint', $endpoint)
                ->where('is_active', true)
                ->exists();

            return response()->json([
                'subscribed' => $subscription,
                'authenticated' => true,
            ]);
        }

        $hasAnySubscription = PushSubscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        return response()->json([
            'subscribed' => $hasAnySubscription,
            'authenticated' => true,
        ]);
    }

    /**
     * Get VAPID public key for client-side subscription.
     */
    public function vapidPublicKey(): JsonResponse
    {
        $publicKey = config('services.vapid.public_key');

        if (!$publicKey) {
            return response()->json([
                'success' => false,
                'message' => 'VAPID keys not configured',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'publicKey' => $publicKey,
        ]);
    }
}
