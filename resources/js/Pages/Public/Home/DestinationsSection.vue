<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ArrowRight, Mountain, Star, Sparkles, MapPin, TrendingUp, ChevronRight } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ destinations: Array });
const sectionRef = ref(null);
const scrollContainerRef = ref(null);
const scrollProgress = ref(0);
let ctx;
let rafId = null;

// Optimized scroll handler with requestAnimationFrame
const handleScroll = () => {
    if (rafId) return; // Skip if already pending
    rafId = requestAnimationFrame(() => {
        if (!scrollContainerRef.value) return;
        const container = scrollContainerRef.value;
        const scrollWidth = container.scrollWidth - container.clientWidth;
        if (scrollWidth > 0) {
            scrollProgress.value = (container.scrollLeft / scrollWidth) * 100;
        }
        rafId = null;
    });
};

onMounted(() => {
    if (scrollContainerRef.value) {
        scrollContainerRef.value.addEventListener('scroll', handleScroll, { passive: true });
    }

    ctx = gsap.context(() => {
        gsap.fromTo('.dest-header > *', 
            { opacity: 0, y: 25 }, 
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', 
                scrollTrigger: { trigger: sectionRef.value, start: 'top 85%', end: 'bottom top', toggleActions: 'play reverse play reverse' } 
            }
        );
        
        gsap.fromTo('.dest-card', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.08, ease: 'power2.out', 
                scrollTrigger: { trigger: sectionRef.value, start: 'top 80%', end: 'bottom top', toggleActions: 'play reverse play reverse' } 
            }
        );
    }, sectionRef.value);
});

onBeforeUnmount(() => { 
    if (ctx) ctx.revert();
    if (rafId) cancelAnimationFrame(rafId);
    if (scrollContainerRef.value) {
        scrollContainerRef.value.removeEventListener('scroll', handleScroll);
    }
});
</script>

<template>
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-gray-50/50 to-white overflow-hidden">
        <!-- Simple Background -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 right-[10%] w-72 h-72 bg-emerald-100/40 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-[5%] w-64 h-64 bg-cyan-100/30 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="dest-header text-left mb-10">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-semibold tracking-wide">Destinasi Populer</span>
                    <TrendingUp class="w-3 h-3 text-emerald-500" />
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-2">
                    Jelajahi Keindahan 
                    <span class="block bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        Alam Sulawesi
                    </span>
                </h2>
                <p class="text-sm text-gray-500">Temukan destinasi wisata terbaik di Taman Nasional Lore Lindu</p>
            </div>

            <!-- Horizontal Scroll Container - Optimized -->
            <div 
                ref="scrollContainerRef"
                class="flex gap-3 overflow-x-auto pb-6 snap-x snap-mandatory scrollbar-hide"
                style="-webkit-overflow-scrolling: touch;"
            >
                <Link 
                    v-for="(dest, index) in destinations?.slice(0, 10)" 
                    :key="dest.id" 
                    :href="`/destinations/${dest.slug}`" 
                    class="dest-card group flex-shrink-0 snap-start"
                >
                    <!-- Card Container with Fixed Height -->
                    <div class="w-[280px] sm:w-[320px] h-[350px] sm:h-[370px] flex flex-col rounded-xl overflow-hidden shadow-sm hover:shadow-lg bg-white border border-gray-200/80 hover:-translate-y-1 transition-all duration-300">
                        <!-- Image Section - Fixed 16:9 Ratio -->
                        <div class="relative w-full aspect-video flex-shrink-0 overflow-hidden bg-gray-200">
                            <!-- Image - 16:9 -->
                            <img 
                                :src="dest.image || dest.primary_image_url || '/images/placeholder-no-image.svg'" 
                                :alt="dest.name" 
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                loading="lazy"
                                decoding="async"
                            >
                            
                            <!-- Top Badges -->
                            <div class="absolute top-3 left-3 right-3 flex items-start justify-between z-10">
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/40 text-white text-[10px] font-semibold">
                                    <Mountain class="w-3 h-3" />
                                    <span>{{ dest.category?.name || 'Wisata' }}</span>
                                </div>
                                <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-bold shadow-lg">
                                    <Star class="w-3 h-3 fill-current" />
                                    <span>{{ dest.rating || '4.8' }}</span>
                                </div>
                            </div>
                            
                            <!-- Popular Badge -->
                            <div v-if="index < 3" class="absolute top-10 left-3 z-10">
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 text-white text-[8px] font-bold">
                                    <TrendingUp class="w-2 h-2" />
                                    <span>POPULER</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Content Section -->
                        <div class="flex-1 flex flex-col p-3.5">
                            <!-- Text Content -->
                            <div class="flex-1">
                                <h3 class="text-sm font-bold text-gray-900 mb-1 line-clamp-1 group-hover:text-emerald-600 transition-colors">
                                    {{ dest.name }}
                                </h3>
                                <p class="text-gray-500 text-[11px] leading-relaxed line-clamp-6">
                                    {{ dest.short_description || 'Destinasi wisata alam yang menakjubkan dengan pemandangan indah.' }}
                                </p>
                            </div>
                            
                            <!-- Divider Line -->
                            <div class="border-t border-gray-100 my-2.5"></div>
                            
                            <!-- Location & CTA -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1 text-gray-400 text-[10px] flex-1 min-w-0">
                                    <MapPin class="w-3 h-3 flex-shrink-0 text-emerald-500" />
                                    <span class="truncate">{{ dest.address || dest.city || 'Sulawesi Tengah' }}</span>
                                </div>
                                <div class="flex items-center gap-0.5 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-semibold group-hover:bg-emerald-600 transition-colors flex-shrink-0">
                                    <span>Jelajahi</span>
                                    <ChevronRight class="w-3 h-3" />
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Progress Bar & CTA -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-5">
                <!-- Progress Indicator -->
                <div class="flex items-center gap-2">
                    <div class="relative w-28 h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div 
                            class="absolute top-0 left-0 h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-[width] duration-100"
                            :style="{ width: `${scrollProgress}%` }"
                        ></div>
                    </div>
                    <span class="text-[11px] text-gray-400">Swipe untuk melihat lebih</span>
                    <ChevronRight class="w-3.5 h-3.5 text-gray-400" />
                </div>

                <!-- CTA Button -->
                <Link 
                    href="/destinations" 
                    class="group inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium text-xs shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
                >
                    <span>Lihat Semua Destinasi</span>
                    <ArrowRight class="w-3.5 h-3.5" />
                </Link>
            </div>
        </div>
    </section>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.snap-x {
    scroll-snap-type: x mandatory;
}
.snap-start {
    scroll-snap-align: start;
}
</style>
