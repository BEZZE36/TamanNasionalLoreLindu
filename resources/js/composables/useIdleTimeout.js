import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Composable untuk auto-logout setelah periode inactivity
 * 
 * Fitur:
 * - Deteksi aktivitas user (scroll, click, mousemove, keypress, dll)
 * - Sinkronisasi antar tab UNTUK AKUN YANG SAMA (admin terpisah dari user)
 * - Auto-logout ketika timer habis
 * - Timer pause saat tab tidak aktif, resume saat kembali ke tab
 * 
 * CARA MENGATUR WAKTU TIMEOUT:
 * Ubah nilai IDLE_TIMEOUT di bawah ini
 * 
 * Contoh:
 * - 20 detik  = 20 * 1000;
 * - 5 menit  = 5 * 60 * 1000;
 * - 10 menit = 10 * 60 * 1000;
 * - 15 menit = 15 * 60 * 1000;
 * - 30 menit = 30 * 60 * 1000;
 * - 1 jam    = 60 * 60 * 1000;
 * 
 * @param {boolean} isAuthenticated - Apakah user sudah login
 * @param {boolean} isAdmin - Apakah ini admin panel (untuk redirect yang benar)
 */

// ========================================
// PENGATURAN WAKTU TIMEOUT (dalam milidetik)
// ========================================
const IDLE_TIMEOUT = 10 * 60 * 1000; // 10 menit

export function useIdleTimeout(isAuthenticated = true, isAdmin = false) {
    // Key berbeda untuk admin dan user agar tidak saling mempengaruhi
    const STORAGE_KEY = isAdmin ? 'tnll_admin_last_activity' : 'tnll_user_last_activity';
    const LOGOUT_KEY = isAdmin ? 'tnll_admin_logout_event' : 'tnll_user_logout_event';

    // URL redirect yang berbeda untuk admin dan user
    const LOGIN_URL = isAdmin ? '/admin/login' : '/login';
    const LOGOUT_URL = isAdmin ? '/admin/logout' : '/logout';

    let timeoutId = null;
    let isActive = false;
    let isPaused = false; // Track apakah timer sedang di-pause (tab tidak visible)
    let pausedAt = null; // Waktu ketika timer di-pause
    let remainingTime = IDLE_TIMEOUT; // Sisa waktu yang tersisa

    // Update aktivitas terakhir (lokal dan di localStorage untuk sync antar tab DENGAN AKUN SAMA)
    const updateLastActivity = () => {
        if (!isActive || !isAuthenticated || isPaused) return;

        const now = Date.now();
        localStorage.setItem(STORAGE_KEY, now.toString());

        // Reset timer ke nilai penuh
        remainingTime = IDLE_TIMEOUT;
        resetTimer();
    };

    // Reset timer
    const resetTimer = () => {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        if (!isPaused) {
            timeoutId = setTimeout(() => {
                performLogout();
            }, remainingTime);
        }
    };

    // Lakukan logout
    const performLogout = () => {
        // Notify semua tab DENGAN AKUN YANG SAMA untuk logout
        localStorage.setItem(LOGOUT_KEY, Date.now().toString());

        // Clear storage
        localStorage.removeItem(STORAGE_KEY);

        // Redirect ke logout
        router.post(LOGOUT_URL, {}, {
            preserveState: false,
            preserveScroll: false,
            onSuccess: () => {
                // Redirect handled by Laravel
            },
            onError: () => {
                // Fallback: redirect manual
                window.location.href = LOGIN_URL;
            }
        });
    };

    // Handler untuk storage event (sinkronisasi antar tab DENGAN AKUN YANG SAMA)
    const handleStorageEvent = (event) => {
        if (!isActive || !isAuthenticated) return;

        // Jika tab lain DENGAN AKUN YANG SAMA mengirim logout event
        if (event.key === LOGOUT_KEY) {
            // Tab lain sudah logout, kita juga logout
            window.location.href = LOGIN_URL;
            return;
        }

        // Jika tab lain DENGAN AKUN YANG SAMA update aktivitas, reset timer kita juga
        if (event.key === STORAGE_KEY) {
            remainingTime = IDLE_TIMEOUT;
            resetTimer();
        }
    };

    // Event yang menandakan user aktif
    const activityEvents = [
        'mousemove',
        'mousedown',
        'keypress',
        'keydown',
        'scroll',
        'touchstart',
        'touchmove',
        'click',
        'wheel'
    ];

    // Handler untuk visibility change (tab focus/blur)
    const handleVisibilityChange = () => {
        if (!isActive || !isAuthenticated) return;

        if (document.visibilityState === 'hidden') {
            // Tab menjadi tidak visible - PAUSE timer
            isPaused = true;
            pausedAt = Date.now();

            // Hitung sisa waktu yang tersisa
            const lastActivity = localStorage.getItem(STORAGE_KEY);
            if (lastActivity) {
                const elapsed = Date.now() - parseInt(lastActivity);
                remainingTime = Math.max(0, IDLE_TIMEOUT - elapsed);
            }

            // Clear timeout saat tab tidak visible
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
        } else {
            // Tab menjadi visible lagi - RESUME timer
            isPaused = false;

            // Cek apakah sudah melewati timeout saat tab tidak visible
            const lastActivity = localStorage.getItem(STORAGE_KEY);
            if (lastActivity) {
                const elapsed = Date.now() - parseInt(lastActivity);
                if (elapsed >= IDLE_TIMEOUT) {
                    // Sudah melewati timeout, logout
                    performLogout();
                    return;
                }
                // Update remaining time
                remainingTime = IDLE_TIMEOUT - elapsed;
            }

            // Reset timer dengan remaining time
            resetTimer();

            // Update aktivitas karena user kembali ke tab (dianggap aktivitas)
            updateLastActivity();
        }
    };

    // Start tracking
    const startTracking = () => {
        if (!isAuthenticated) return;

        isActive = true;
        isPaused = false;
        remainingTime = IDLE_TIMEOUT;

        // Set initial last activity
        localStorage.setItem(STORAGE_KEY, Date.now().toString());

        // Add event listeners
        activityEvents.forEach(event => {
            // Gunakan passive untuk performance
            window.addEventListener(event, updateLastActivity, { passive: true });
        });

        // Listen untuk storage events (sinkronisasi antar tab DENGAN AKUN YANG SAMA)
        window.addEventListener('storage', handleStorageEvent);

        // Listen untuk visibility change
        document.addEventListener('visibilitychange', handleVisibilityChange);

        // Start timer hanya jika tab visible
        if (document.visibilityState === 'visible') {
            resetTimer();
        }
    };

    // Stop tracking
    const stopTracking = () => {
        isActive = false;

        // Clear timer
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }

        // Remove event listeners
        activityEvents.forEach(event => {
            window.removeEventListener(event, updateLastActivity);
        });

        window.removeEventListener('storage', handleStorageEvent);
        document.removeEventListener('visibilitychange', handleVisibilityChange);
    };

    // Lifecycle hooks
    onMounted(() => {
        startTracking();
    });

    onUnmounted(() => {
        stopTracking();
    });

    // Return functions untuk manual control jika diperlukan
    return {
        startTracking,
        stopTracking,
        updateLastActivity
    };
}
