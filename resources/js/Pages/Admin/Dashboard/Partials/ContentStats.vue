<script setup>
import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ChevronRight, ExternalLink } from 'lucide-vue-next';

defineProps({
    stats: { type: Array, required: true }
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const items = document.querySelectorAll('.content-stat');
        if (items.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(items, 
                    { opacity: 0, x: -15, scale: 0.95 }, 
                    { opacity: 1, x: 0, scale: 1, duration: 0.4, stagger: 0.08, ease: 'power2.out' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <Link 
            v-for="(stat, index) in stats"
            :key="index"
            :href="stat.href"
            :class="[
                'content-stat group rounded-xl p-4 border transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer bg-gradient-to-br',
                stat.bgColor,
                stat.borderColor,
                stat.hoverBg
            ]"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Icon with gradient background -->
                    <div class="relative">
                        <div :class="['absolute inset-0 rounded-lg blur-md opacity-50 group-hover:opacity-70 transition-opacity bg-gradient-to-br', stat.iconBg]"></div>
                        <div :class="['relative w-10 h-10 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg bg-gradient-to-br', stat.iconBg]">
                            <component :is="stat.icon" :class="['w-5 h-5', stat.iconColor]" />
                        </div>
                    </div>
                    <div>
                        <p :class="['text-xl font-black', stat.textColor]">{{ stat.value }}</p>
                        <p :class="['text-[11px] font-semibold opacity-80', stat.textColor]">{{ stat.title }}</p>
                    </div>
                </div>
                
                <!-- Arrow indicator -->
                <div class="opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
                    <div :class="['w-7 h-7 rounded-lg flex items-center justify-center bg-white/60 backdrop-blur-sm shadow-sm', stat.textColor]">
                        <ChevronRight class="w-4 h-4" />
                    </div>
                </div>
            </div>
            
            <!-- Bottom progress bar decoration -->
            <div class="mt-3 h-1 rounded-full bg-white/50 overflow-hidden">
                <div :class="['h-full rounded-full bg-gradient-to-r transition-all duration-500 group-hover:w-full', stat.iconBg]" style="width: 60%"></div>
            </div>
        </Link>
    </div>
</template>
