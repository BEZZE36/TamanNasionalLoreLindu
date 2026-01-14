import { ref, computed, onMounted } from 'vue';

/**
 * Composable for managing push notification subscriptions.
 */
export function usePushNotifications() {
    const isSupported = ref(false);
    const permission = ref('default');
    const isSubscribed = ref(false);
    const isLoading = ref(false);
    const error = ref(null);
    const vapidPublicKey = ref(null);

    /**
     * Check if push notifications are supported.
     */
    const checkSupport = () => {
        isSupported.value = 'serviceWorker' in navigator &&
            'PushManager' in window &&
            'Notification' in window;

        if ('Notification' in window) {
            permission.value = Notification.permission;
        }

        return isSupported.value;
    };

    /**
     * Fetch VAPID public key from server.
     */
    const fetchVapidKey = async () => {
        try {
            const response = await fetch('/push/vapid-public-key');
            const data = await response.json();

            if (data.success) {
                vapidPublicKey.value = data.publicKey;
                return data.publicKey;
            }
            return null;
        } catch (err) {
            console.warn('Failed to fetch VAPID key:', err);
            return null;
        }
    };

    /**
     * Convert VAPID key to Uint8Array for subscription.
     */
    const urlBase64ToUint8Array = (base64String) => {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    };

    /**
     * Request notification permission from user.
     */
    const requestPermission = async () => {
        if (!isSupported.value) {
            error.value = 'Push notifications are not supported';
            return false;
        }

        try {
            const result = await Notification.requestPermission();
            permission.value = result;
            return result === 'granted';
        } catch (err) {
            error.value = 'Failed to request permission';
            return false;
        }
    };

    /**
     * Get current push subscription.
     */
    const getCurrentSubscription = async () => {
        try {
            const registration = await navigator.serviceWorker.ready;
            return await registration.pushManager.getSubscription();
        } catch (err) {
            console.warn('Failed to get subscription:', err);
            return null;
        }
    };

    /**
     * Subscribe to push notifications.
     */
    const subscribe = async () => {
        if (!isSupported.value) {
            error.value = 'Push notifications are not supported';
            return false;
        }

        isLoading.value = true;
        error.value = null;

        try {
            // Request permission if not granted
            if (permission.value !== 'granted') {
                const granted = await requestPermission();
                if (!granted) {
                    error.value = 'Permission denied';
                    isLoading.value = false;
                    return false;
                }
            }

            // Get VAPID key
            if (!vapidPublicKey.value) {
                await fetchVapidKey();
            }

            if (!vapidPublicKey.value) {
                error.value = 'VAPID key not available';
                isLoading.value = false;
                return false;
            }

            // Wait for service worker
            const registration = await navigator.serviceWorker.ready;

            // Subscribe to push
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey.value),
            });

            // Send subscription to server
            const response = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(subscription.toJSON()),
            });

            const data = await response.json();

            if (data.success) {
                isSubscribed.value = true;
                return true;
            } else {
                error.value = data.message || 'Failed to save subscription';
                return false;
            }
        } catch (err) {
            console.error('Subscribe error:', err);
            error.value = err.message || 'Failed to subscribe';
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Unsubscribe from push notifications.
     */
    const unsubscribe = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            const subscription = await getCurrentSubscription();

            if (subscription) {
                // Unsubscribe from push manager
                await subscription.unsubscribe();

                // Remove from server
                await fetch('/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ endpoint: subscription.endpoint }),
                });
            }

            isSubscribed.value = false;
            return true;
        } catch (err) {
            console.error('Unsubscribe error:', err);
            error.value = err.message || 'Failed to unsubscribe';
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Toggle subscription state.
     */
    const toggle = async () => {
        if (isSubscribed.value) {
            return await unsubscribe();
        } else {
            return await subscribe();
        }
    };

    /**
     * Check current subscription status.
     */
    const checkSubscription = async () => {
        try {
            // First check browser subscription
            const browserSubscription = await getCurrentSubscription();

            // Always verify with server (even if no browser subscription)
            // This handles cases where browser data was cleared but server still has record
            const response = await fetch('/push/status');
            const data = await response.json();

            if (data.subscribed) {
                // Server says we're subscribed
                if (browserSubscription) {
                    // Browser also has subscription - all good
                    isSubscribed.value = true;
                } else {
                    // Server has record but browser doesn't - need to re-subscribe
                    // For now, just show as subscribed (user can toggle to fix)
                    isSubscribed.value = true;
                }
            } else {
                // Server says not subscribed
                isSubscribed.value = false;
            }
        } catch (err) {
            console.warn('Failed to check subscription:', err);
            // Fallback to browser subscription state
            const subscription = await getCurrentSubscription();
            isSubscribed.value = !!subscription;
        }
    };

    /**
     * Initialize on mount.
     */
    const init = async () => {
        checkSupport();
        if (isSupported.value) {
            await fetchVapidKey();
            await checkSubscription();
        }
    };

    // Computed states
    const canSubscribe = computed(() =>
        isSupported.value && permission.value !== 'denied' && !isSubscribed.value
    );

    const statusText = computed(() => {
        if (!isSupported.value) return 'Tidak didukung';
        if (permission.value === 'denied') return 'Diblokir';
        if (isSubscribed.value) return 'Aktif';
        return 'Nonaktif';
    });

    return {
        // State
        isSupported,
        permission,
        isSubscribed,
        isLoading,
        error,
        vapidPublicKey,

        // Computed
        canSubscribe,
        statusText,

        // Methods
        init,
        subscribe,
        unsubscribe,
        toggle,
        requestPermission,
        checkSubscription,
    };
}
