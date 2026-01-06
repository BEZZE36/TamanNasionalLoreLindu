<script setup>
/**
 * SkeletonCard.vue
 * Reusable card skeleton component
 */
defineProps({
    variant: { type: String, default: 'default' }, // default, horizontal, minimal
    showImage: { type: Boolean, default: true },
    showAvatar: { type: Boolean, default: false },
    lines: { type: Number, default: 3 },
    aspectRatio: { type: String, default: '16/9' }
});
</script>

<template>
    <!-- Default Card Skeleton -->
    <div v-if="variant === 'default'" class="bg-white rounded-2xl overflow-hidden shadow-lg animate-pulse">
        <div v-if="showImage" class="skeleton-image" :style="{ aspectRatio }"></div>
        <div class="p-5 space-y-3">
            <div class="skeleton-title w-3/4"></div>
            <div v-for="n in lines" :key="n" 
                 class="skeleton-text" 
                 :style="{ width: n === lines ? '60%' : '100%' }">
            </div>
            <div class="flex items-center gap-3 pt-2">
                <div v-if="showAvatar" class="skeleton-avatar w-8 h-8"></div>
                <div class="skeleton-text w-24"></div>
            </div>
        </div>
    </div>

    <!-- Horizontal Card Skeleton -->
    <div v-else-if="variant === 'horizontal'" class="bg-white rounded-2xl overflow-hidden shadow-lg flex animate-pulse">
        <div v-if="showImage" class="skeleton-base w-32 h-24 flex-shrink-0"></div>
        <div class="p-4 flex-1 space-y-2">
            <div class="skeleton-title w-3/4"></div>
            <div class="skeleton-text w-full"></div>
            <div class="skeleton-text w-1/2"></div>
        </div>
    </div>

    <!-- Minimal Card Skeleton -->
    <div v-else-if="variant === 'minimal'" class="bg-white/50 rounded-xl p-4 space-y-2 animate-pulse">
        <div class="skeleton-text w-3/4 h-4"></div>
        <div class="skeleton-text w-1/2 h-3"></div>
    </div>
</template>
