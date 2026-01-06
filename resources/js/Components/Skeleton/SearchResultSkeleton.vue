<script setup>
// Search Result Skeleton - displays loading placeholder for search results
defineProps({
    count: {
        type: Number,
        default: 3
    }
});
</script>

<template>
    <div class="space-y-3">
        <!-- Skeleton header -->
        <div class="flex items-center gap-2 mb-4">
            <div class="w-4 h-4 rounded bg-gray-200 animate-pulse"></div>
            <div class="w-32 h-4 rounded bg-gray-200 animate-pulse"></div>
        </div>
        
        <!-- Skeleton items -->
        <div 
            v-for="i in count" 
            :key="i"
            class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100"
            :style="{ animationDelay: `${i * 100}ms` }"
        >
            <!-- Image skeleton -->
            <div class="relative flex-shrink-0">
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-gray-200 to-gray-100 animate-pulse"></div>
                <!-- Badge skeleton -->
                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-lg bg-gray-200 animate-pulse"></div>
            </div>
            
            <!-- Content skeleton -->
            <div class="flex-1 min-w-0 space-y-2">
                <!-- Title -->
                <div class="h-5 bg-gradient-to-r from-gray-200 to-gray-100 rounded-lg w-3/4 animate-pulse"></div>
                <!-- Description -->
                <div class="h-3 bg-gray-100 rounded w-full animate-pulse"></div>
                <!-- Badge -->
                <div class="h-5 bg-gray-200 rounded-full w-20 animate-pulse"></div>
            </div>
            
            <!-- Arrow skeleton -->
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-100 animate-pulse"></div>
        </div>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

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
