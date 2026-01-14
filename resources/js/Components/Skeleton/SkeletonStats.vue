<script setup>
/**
 * SkeletonStats.vue
 * Stats cards skeleton for dashboards
 */
defineProps({
    count: { type: Number, default: 4 },
    variant: { type: String, default: 'grid' } // grid, inline
});
</script>

<template>
    <!-- Grid Layout -->
    <div v-if="variant === 'grid'" 
         class="grid gap-4"
         :class="count <= 3 ? `grid-cols-${count}` : 'grid-cols-2 lg:grid-cols-4'">
        <div v-for="n in count" :key="n" 
             class="bg-white rounded-2xl p-6 shadow-lg animate-pulse">
            <div class="flex items-center justify-between mb-4">
                <div class="skeleton-base w-12 h-12 rounded-xl"></div>
                <div class="skeleton-text w-16 h-3"></div>
            </div>
            <div class="space-y-2">
                <div class="skeleton-title w-1/2 h-8"></div>
                <div class="skeleton-text w-3/4 h-3"></div>
            </div>
        </div>
    </div>

    <!-- Inline Layout -->
    <div v-else class="flex gap-4 overflow-hidden animate-pulse">
        <div v-for="n in count" :key="n" 
             class="flex-shrink-0 bg-white/10 backdrop-blur rounded-2xl p-4 text-center min-w-[120px]">
            <div class="skeleton-title w-12 h-8 mx-auto mb-2"></div>
            <div class="skeleton-text w-16 h-3 mx-auto"></div>
        </div>
    </div>
</template>
