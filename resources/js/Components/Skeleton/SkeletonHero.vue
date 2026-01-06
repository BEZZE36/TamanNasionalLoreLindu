<script setup>
/**
 * SkeletonHero.vue
 * Hero section skeleton
 */
defineProps({
    height: { type: String, default: 'h-[500px]' },
    showBreadcrumb: { type: Boolean, default: true },
    showStats: { type: Boolean, default: false }
});
</script>

<template>
    <section class="relative overflow-hidden animate-pulse" :class="height">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-300 via-gray-200 to-gray-300"></div>
        
        <!-- Content -->
        <div class="absolute inset-0 flex items-center">
            <div class="container mx-auto px-4">
                <!-- Breadcrumb -->
                <div v-if="showBreadcrumb" class="flex items-center gap-2 mb-6">
                    <div class="skeleton-text w-16 h-3 bg-white/30"></div>
                    <div class="skeleton-text w-4 h-3 bg-white/30"></div>
                    <div class="skeleton-text w-24 h-3 bg-white/30"></div>
                </div>
                
                <!-- Badge -->
                <div class="skeleton-base w-32 h-8 rounded-full mb-4 bg-white/20"></div>
                
                <!-- Title -->
                <div class="skeleton-base w-2/3 h-12 rounded-lg mb-4 bg-white/30"></div>
                
                <!-- Subtitle -->
                <div class="skeleton-base w-1/2 h-6 rounded-lg mb-6 bg-white/20"></div>
                
                <!-- Stats -->
                <div v-if="showStats" class="flex gap-6 mt-8">
                    <div v-for="n in 3" :key="n" class="text-center">
                        <div class="skeleton-base w-16 h-10 rounded-lg mb-2 bg-white/20"></div>
                        <div class="skeleton-base w-20 h-4 rounded bg-white/10"></div>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <div class="skeleton-base w-40 h-12 rounded-xl mt-8 bg-white/30"></div>
            </div>
        </div>
    </section>
</template>
