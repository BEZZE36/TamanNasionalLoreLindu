<script setup>
/**
 * BlockDetector.vue
 * Periodically checks if current user has been blocked by admin
 * and redirects to blocked page if detected
 */
import { onMounted, onUnmounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const checkInterval = ref(null);

// Check user status every 5 seconds for faster detection
const CHECK_INTERVAL_MS = 5000;

const checkUserStatus = async () => {
    // Only check if user is logged in
    const user = page.props.auth?.user;
    if (!user || user.is_admin) return;
    
    // If user is already on blocked page, skip
    if (window.location.pathname === '/account-blocked') return;
    
    try {
        const response = await fetch('/api/user/check-status', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.is_blocked) {
                // User has been blocked, redirect to blocked page
                window.location.href = '/account-blocked';
            }
        }
    } catch (e) {
        // Silently fail on network errors
        console.warn('Block status check failed', e);
    }
};

onMounted(() => {
    // Initial check after a short delay
    setTimeout(checkUserStatus, 2000);
    
    // Set up periodic check
    checkInterval.value = setInterval(checkUserStatus, CHECK_INTERVAL_MS);
});

onUnmounted(() => {
    if (checkInterval.value) {
        clearInterval(checkInterval.value);
    }
});
</script>

<template>
    <!-- This component has no visual output -->
</template>
