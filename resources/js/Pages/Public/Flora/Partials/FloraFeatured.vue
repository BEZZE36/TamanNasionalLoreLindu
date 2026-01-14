<script setup>
/**
 * FloraFeatured.vue - Featured Flora with Swipe Scroll
 * Design: Horizontal scroll like GallerySection
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Sparkles, ChevronRight, ArrowRight, Star, Leaf } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    featuredFlora: { type: Array, default: () => [] }
});

const sectionRef = ref(null);
const scrollContainerRef = ref(null);
const scrollProgress = ref(0);
let ctx;
let rafId = null;

const handleScroll = () => {
    if (rafId) return;
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
        gsap.fromTo('.featured-header > *', 
            { opacity: 0, y: 25, scale: 0.98 }, 
            { 
                opacity: 1, y: 0, scale: 1, 
                duration: 0.6, stagger: 0.1, 
                ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 85%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
        
        gsap.fromTo('.featured-item', 
            { opacity: 0, y: 30 }, 
            { 
                opacity: 1, y: 0, 
                duration: 0.5, stagger: 0.06, 
                ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 75%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
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
    <section v-if="featuredFlora?.length > 0" ref="sectionRef" class="py-12 sm:py-16 bg-gradient-to-b from-gray-50 via-white to-gray-50 overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="featured-header text-left mb-6 sm:mb-8">
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200/50 text-emerald-700 mb-2 sm:mb-3 shadow-sm">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Flora Unggulan</span>
                    <Star class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-amber-500 fill-amber-500" />
                </div>
                
                <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                    Spesies 
                    <span class="bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 bg-clip-text text-transparent">
                        Unggulan
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 max-w-lg">Koleksi flora endemik dan langka yang istimewa</p>
            </div>

            <!-- Swipe Hint -->
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] sm:text-xs text-gray-400 flex items-center gap-1">
                    Swipe untuk melihat
                    <ChevronRight class="w-3 h-3" />
                </span>
            </div>

            <!-- Horizontal Scroll -->
            <div 
                ref="scrollContainerRef"
                class="flex gap-3 sm:gap-4 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide"
                style="-webkit-overflow-scrolling: touch;"
            >
                <Link 
                    v-for="flora in featuredFlora" 
                    :key="flora.id" 
                    :href="`/flora/${flora.slug || flora.id}`" 
                    class="featured-item group flex-shrink-0 snap-start"
                >
                    <div class="relative w-[220px] sm:w-[260px] aspect-[3/4] rounded-xl sm:rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl border border-gray-200/80 hover:border-emerald-200 transition-all duration-500 hover:-translate-y-1">
                        <img 
                            :src="flora.image_url || '/images/placeholder-no-image.svg'" 
                            :alt="flora.name" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                            loading="lazy"
                            @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                        >
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                        
                        <!-- Top Badges -->
                        <div class="absolute top-2 sm:top-3 left-2 sm:left-3 right-2 sm:right-3 flex items-start justify-between">
                            <div class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[9px] sm:text-[10px] font-bold shadow-lg">
                                <Star class="w-3 h-3 fill-current" />
                                <span>UNGGULAN</span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                            <!-- Category Badge -->
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/15 backdrop-blur-sm border border-white/20 text-white text-[9px] font-medium mb-2">
                                <Leaf class="w-2.5 h-2.5" />
                                {{ flora.category_label || 'Flora' }}
                            </span>
                            
                            <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-emerald-200 transition-colors mb-1 line-clamp-2 leading-tight">
                                {{ flora.name }}
                            </h3>
                            <p v-if="flora.scientific_name" class="text-[10px] sm:text-[11px] text-gray-300 italic line-clamp-1 mb-2">
                                {{ flora.scientific_name }}
                            </p>
                            
                            <div class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white/15 backdrop-blur-sm text-white font-semibold text-[10px] sm:text-[11px] group-hover:bg-white/25 transition-all w-fit">
                                <span>Lihat</span>
                                <ArrowRight class="w-3 h-3" />
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Progress Bar & CTA -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4">
                <div class="flex items-center gap-2">
                    <div class="relative w-24 h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div 
                            class="absolute top-0 left-0 h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full transition-[width] duration-100"
                            :style="{ width: `${Math.max(scrollProgress, 10)}%` }"
                        ></div>
                    </div>
                </div>
                <Link href="/flora" class="group inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium text-[11px] sm:text-xs shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <span>Lihat Semua Flora</span>
                    <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
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
</style>
