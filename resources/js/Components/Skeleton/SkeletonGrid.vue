<script setup>
/**
 * SkeletonGrid.vue
 * Grid layout skeleton for galleries, destination lists
 */
defineProps({
    count: { type: Number, default: 6 },
    columns: { type: String, default: '3' }, // 2, 3, 4
    aspectRatio: { type: String, default: '16/9' },
    showContent: { type: Boolean, default: true }
});
</script>

<template>
    <div class="grid gap-6"
         :class="{
             'grid-cols-1 sm:grid-cols-2': columns === '2',
             'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': columns === '3',
             'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4': columns === '4'
         }">
        <div v-for="n in count" :key="n" 
             class="bg-white rounded-2xl overflow-hidden shadow-lg animate-pulse">
            <!-- Image -->
            <div class="skeleton-image" :style="{ aspectRatio }"></div>
            
            <!-- Content -->
            <div v-if="showContent" class="p-4 space-y-3">
                <div class="skeleton-title w-3/4"></div>
                <div class="skeleton-text w-full"></div>
                <div class="flex items-center justify-between pt-2">
                    <div class="skeleton-text w-20"></div>
                    <div class="skeleton-base w-8 h-8 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</template>
