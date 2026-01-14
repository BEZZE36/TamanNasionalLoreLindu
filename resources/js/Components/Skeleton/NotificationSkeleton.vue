<script setup>
// Notification Skeleton - displays loading placeholder for notifications
defineProps({
    count: {
        type: Number,
        default: 3
    }
});
</script>

<template>
    <div class="p-2 space-y-2">
        <!-- Skeleton items -->
        <div 
            v-for="i in count" 
            :key="i"
            class="flex items-start gap-3 p-3 rounded-xl bg-gray-50"
            :style="{ animationDelay: `${i * 100}ms` }"
        >
            <!-- Icon skeleton -->
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-200 to-gray-100 animate-pulse"></div>
            </div>
            
            <!-- Content skeleton -->
            <div class="flex-1 min-w-0 space-y-2 py-0.5">
                <!-- Title -->
                <div class="h-4 bg-gray-200 rounded w-3/4 animate-pulse"></div>
                <!-- Description -->
                <div class="h-3 bg-gray-100 rounded w-full animate-pulse"></div>
                <!-- Time -->
                <div class="flex items-center gap-2 mt-1">
                    <div class="w-3 h-3 rounded bg-gray-200 animate-pulse"></div>
                    <div class="h-2 bg-gray-100 rounded w-16 animate-pulse"></div>
                </div>
            </div>
            
            <!-- Arrow skeleton -->
            <div class="flex-shrink-0 w-4 h-4 rounded bg-gray-100 animate-pulse mt-1"></div>
        </div>
    </div>
</template>

<style scoped>
.animate-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
