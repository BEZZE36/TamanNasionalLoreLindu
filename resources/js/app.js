import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// Global handler untuk 419 error (CSRF token expired)
// Ketika token kadaluarsa, halaman akan auto-reload untuk mendapatkan token baru
router.on('invalid', (event) => {
    if (event.detail.response.status === 419) {
        event.preventDefault();
        window.location.reload();
    }
});

createInertiaApp({
    title: (title) => `${title} - TNLL Explore`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);

        app.mount(el);

        // Register Service Worker for PWA and Push Notifications
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('ServiceWorker registered with scope:', registration.scope);

                    // Auto-subscribe to push notifications
                    autoSubscribePushNotification(registration);
                })
                .catch(error => {
                    console.warn('ServiceWorker registration failed:', error);
                });
        }

        // Auto-subscribe to push notifications
        async function autoSubscribePushNotification(registration) {
            try {
                // Check if push manager is supported
                if (!('PushManager' in window)) {
                    console.log('Push notifications not supported');
                    return;
                }

                // Check if already subscribed
                const existingSubscription = await registration.pushManager.getSubscription();
                if (existingSubscription) {
                    console.log('Already subscribed to push notifications');
                    return;
                }

                // Request notification permission
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    console.log('Notification permission denied');
                    return;
                }

                // Fetch VAPID public key from server
                const vapidResponse = await fetch('/push/vapid-public-key');
                const vapidData = await vapidResponse.json();

                if (!vapidData.success || !vapidData.publicKey) {
                    console.warn('VAPID key not available');
                    return;
                }

                // Convert VAPID key to Uint8Array
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

                // Subscribe to push
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidData.publicKey),
                });

                // Send subscription to server
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch('/push/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(subscription.toJSON()),
                });

                const data = await response.json();
                if (data.success) {
                    console.log('✅ Auto-subscribed to push notifications');
                } else {
                    console.warn('Failed to save push subscription:', data.message);
                }
            } catch (error) {
                console.warn('Auto-subscribe push notification failed:', error.message);
            }
        }

        return app;
    },
    progress: {
        color: '#8B5CF6',
        showSpinner: true,
    },
});
