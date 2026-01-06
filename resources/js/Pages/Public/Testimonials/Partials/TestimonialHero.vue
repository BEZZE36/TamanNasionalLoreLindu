<script setup>
import { Quote, Star } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({ 
    avgRating: { type: [Number, String], default: 0 }, 
    totalReviews: { type: Number, default: 0 } 
});

// Parse avgRating to number
const parsedRating = computed(() => parseFloat(props.avgRating) || 0);
</script>

<template>
    <section class="relative pt-32 pb-20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-600 via-fuchsia-700 to-purple-900"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-pink-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-fuchsia-400/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 animate-pulse" style="animation-delay: 1s"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4"><Quote class="w-4 h-4" />Testimonials</span>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">Apa Kata Mereka 💬</h1>
                <p class="text-white/80 max-w-xl mb-6">Pengalaman dan cerita dari pengunjung Taman Nasional Lore Lindu</p>
                
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            <Star v-for="i in 5" :key="i" :class="['w-5 h-5', i <= Math.round(parsedRating) ? 'text-yellow-400 fill-yellow-400' : 'text-white/30']" />
                        </div>
                        <span class="text-white font-bold">{{ parsedRating.toFixed(1) }}</span>
                    </div>
                    <div class="text-white/70">{{ totalReviews }} ulasan</div>
                </div>
            </div>
        </div>
    </section>
</template>
