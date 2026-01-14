<script setup>
import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ArrowUpRight, ChevronRight, TrendingUp, Sparkles } from 'lucide-vue-next';

defineProps({
    stats: { type: Array, required: true }
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const cards = document.querySelectorAll('.stat-card');
        if (cards.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(cards, 
                    { opacity: 0, y: 20, scale: 0.95 }, 
                    { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
        <div 
            v-for="(stat, index) in stats" 
            :key="index"
            class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 border border-gray-100/80 shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-1"
        >
            <!-- Background Gradient -->
            <div :class="['absolute top-0 right-0 w-28 h-28 bg-gradient-to-bl rounded-bl-full transition-all duration-500 group-hover:scale-125 group-hover:opacity-80', stat.bgGradient]"></div>
            
            <!-- Pulse indicator for pending -->
            <div v-if="stat.pulse" class="absolute top-3 right-3 w-2 h-2 rounded-full bg-amber-500 animate-ping"></div>
            <div v-if="stat.pulse" class="absolute top-3 right-3 w-2 h-2 rounded-full bg-amber-500"></div>
            
            <!-- Sparkle decoration on hover -->
            <Sparkles class="absolute top-3 right-14 w-4 h-4 text-gray-200 opacity-0 group-hover:opacity-100 transition-all duration-500 group-hover:rotate-12" />
            
            <div class="relative z-10 flex items-start justify-between">
                <div class="space-y-2 flex-1">
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">{{ stat.title }}</p>
                    <p class="text-xl font-black text-gray-900 group-hover:text-gray-800 transition-colors">{{ stat.value }}</p>
                    
                    <!-- Sub info -->
                    <div v-if="stat.subValue" class="flex items-center gap-1.5 mt-1">
                        <div class="w-5 h-5 rounded-md bg-gray-100 flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                            <ArrowUpRight class="w-3 h-3 text-gray-600" />
                        </div>
                        <span class="text-[10px] font-medium text-gray-600">{{ stat.subLabel }}: <strong class="text-gray-800">{{ stat.subValue }}</strong></span>
                    </div>
                    
                    <!-- Link -->
                    <Link 
                        v-if="stat.linkHref" 
                        :href="stat.linkHref" 
                        class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 hover:text-amber-700 transition-colors mt-1 group/link"
                    >
                        {{ stat.linkText }}
                        <ChevronRight class="w-3 h-3 group-hover/link:translate-x-0.5 transition-transform" />
                    </Link>
                </div>
                
                <!-- Icon -->
                <div class="relative">
                    <div :class="['absolute inset-0 rounded-xl blur-lg opacity-50 group-hover:opacity-70 transition-opacity bg-gradient-to-br', stat.gradient]"></div>
                    <div :class="['relative w-11 h-11 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 bg-gradient-to-br', stat.gradient, stat.shadowColor]">
                        <component :is="stat.icon" class="w-5 h-5 text-white" />
                    </div>
                </div>
            </div>
            
            <!-- Bottom glow line on hover -->
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-violet-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        </div>
    </div>
</template>
