<template>
    <div class="push-notification-toggle">
        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                    <Bell v-if="!isSubscribed" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    <BellRing v-else class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white text-sm">
                        Push Notifications
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ statusDescription }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Status Badge -->
                <span 
                    v-if="!isSupported"
                    class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400"
                >
                    Tidak Didukung
                </span>
                <span 
                    v-else-if="permission === 'denied'"
                    class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400"
                >
                    Diblokir
                </span>
                
                <!-- Toggle Switch -->
                <button
                    v-else
                    @click="toggle"
                    :disabled="isLoading"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isSubscribed ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                    role="switch"
                    :aria-checked="isSubscribed"
                >
                    <span
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="isSubscribed ? 'translate-x-5' : 'translate-x-0'"
                    />
                </button>
            </div>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-xs text-red-600 dark:text-red-400 px-4">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { Bell, BellRing } from 'lucide-vue-next';
import { usePushNotifications } from '@/Composables/usePushNotifications';

const {
    isSupported,
    permission,
    isSubscribed,
    isLoading,
    error,
    init,
    toggle,
} = usePushNotifications();

const statusDescription = computed(() => {
    if (!isSupported.value) {
        return 'Browser tidak mendukung push notifications';
    }
    if (permission.value === 'denied') {
        return 'Push notifications diblokir di browser';
    }
    if (isSubscribed.value) {
        return 'Anda akan menerima notifikasi artikel baru';
    }
    return 'Aktifkan untuk mendapat notifikasi artikel baru';
});

onMounted(() => {
    init();
});
</script>

<style scoped>
.push-notification-toggle {
    /* Component wrapper styles */
}
</style>
