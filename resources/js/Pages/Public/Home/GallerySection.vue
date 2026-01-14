<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Camera, MapPin, ArrowRight, Images, Sparkles, ZoomIn, Heart, Play, Eye, ChevronRight } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ galleries: { type: Array, default: () => [] } });
const sectionRef = ref(null);
const scrollContainerRef = ref(null);
const scrollProgress = ref(0);
const activeIndex = ref(0);
let ctx;
let rafId = null;

// Get first 8 galleries for display
const displayGalleries = computed(() => props.galleries?.slice(0, 8) || []);

// Featured gallery (first one)
const featuredGallery = computed(() => props.galleries?.[0] || null);

// Other galleries (rest)
const otherGalleries = computed(() => props.galleries?.slice(1, 8) || []);

// Optimized scroll handler
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
        // Header entrance
        gsap.fromTo('.gallery-header > *', 
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
        
        // Featured gallery
        gsap.fromTo('.gallery-featured', 
            { opacity: 0, scale: 0.95, y: 30 }, 
            { 
                opacity: 1, scale: 1, y: 0, 
                duration: 0.7, 
                ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
        
        // Gallery items
        gsap.fromTo('.gallery-item', 
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
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-gray-50 via-white to-gray-50 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 left-0 w-48 sm:w-64 lg:w-80 h-48 sm:h-64 lg:h-80 bg-gradient-to-br from-emerald-200/30 to-teal-200/15 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute bottom-1/4 right-0 w-44 sm:w-56 lg:w-72 h-44 sm:h-56 lg:h-72 bg-gradient-to-tl from-cyan-200/25 to-blue-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="gallery-header text-left mb-8 sm:mb-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-2 sm:mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Galeri Foto</span>
                    <Camera class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-500" />
                </div>
                
                <!-- Title -->
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                    Momen Indah di 
                    <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        Taman Nasional
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 max-w-lg">Abadikan kenangan dari keindahan alam Lore Lindu</p>
            </div>

            <!-- Gallery Content -->
            <div v-if="galleries && galleries.length > 0" class="space-y-4 sm:space-y-5">
                <!-- Featured Gallery (First Item) -->
                <Link v-if="featuredGallery" :href="`/gallery/${featuredGallery.slug}`" class="gallery-featured group block">
                    <div class="relative h-56 sm:h-64 lg:h-80 rounded-xl sm:rounded-2xl overflow-hidden shadow-lg sm:shadow-xl hover:shadow-2xl transition-all duration-500">
                        <img 
                            :src="featuredGallery.image || '/images/placeholder-no-image.svg'" 
                            :alt="featuredGallery.title" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                            loading="lazy"
                            @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                        >
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                        
                        <!-- Top Badges -->
                        <div class="absolute top-3 sm:top-4 left-3 sm:left-4 right-3 sm:right-4 flex items-start justify-between">
                            <div class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[9px] sm:text-[10px] font-bold shadow-lg">
                                <Sparkles class="w-3 h-3" />
                                <span>UNGGULAN</span>
                            </div>
                            <div v-if="featuredGallery.mediaCount > 1" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-black/40 backdrop-blur-sm text-white text-[9px] sm:text-[10px] font-medium">
                                <Images class="w-3 h-3" />
                                {{ featuredGallery.mediaCount }} Foto
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-5 lg:p-6">
                            <!-- Category Badge -->
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/15 backdrop-blur-sm border border-white/20 text-white text-[9px] font-medium mb-2">
                                {{ featuredGallery.category || 'Galeri' }}
                            </span>
                            
                            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-white group-hover:text-emerald-200 transition-colors mb-2 line-clamp-2 leading-tight">
                                {{ featuredGallery.title }}
                            </h3>
                            
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3 text-gray-300 text-[10px] sm:text-xs">
                                    <span v-if="featuredGallery.location" class="flex items-center gap-1">
                                        <MapPin class="w-3 h-3" />
                                        {{ featuredGallery.location }}
                                    </span>
                                    <span v-if="featuredGallery.views" class="flex items-center gap-1">
                                        <Eye class="w-3 h-3" />
                                        {{ featuredGallery.views }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/15 backdrop-blur-sm text-white font-semibold text-[10px] sm:text-xs group-hover:bg-white/25 transition-all">
                                    <span>Lihat</span>
                                    <ZoomIn class="w-3 h-3" />
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Other Galleries - Horizontal Scroll -->
                <div v-if="otherGalleries.length > 0" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900">Galeri Lainnya</h3>
                        <div class="flex items-center gap-1.5 text-[10px] sm:text-xs text-gray-400">
                            <span>Swipe untuk melihat</span>
                            <ChevronRight class="w-3 h-3" />
                        </div>
                    </div>

                    <div 
                        ref="scrollContainerRef"
                        class="flex gap-3 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide"
                        style="-webkit-overflow-scrolling: touch;"
                    >
                        <Link 
                            v-for="gallery in otherGalleries" 
                            :key="gallery.id" 
                            :href="`/gallery/${gallery.slug}`" 
                            class="gallery-item group flex-shrink-0 snap-start"
                        >
                            <div class="relative w-[180px] sm:w-[220px] aspect-[4/3] rounded-xl overflow-hidden shadow-sm hover:shadow-lg border border-gray-200/80 hover:border-emerald-200 hover:-translate-y-1 transition-all duration-300">
                                <img 
                                    :src="gallery.image || '/images/placeholder-no-image.svg'" 
                                    :alt="gallery.title" 
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                    loading="lazy"
                                    @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                                >
                                
                                <!-- Gradient Overlay -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                                
                                <!-- Category Badge -->
                                <div class="absolute top-2 left-2">
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-white/15 backdrop-blur-sm border border-white/20 text-white text-[8px] font-medium">
                                        <Sparkles class="w-2 h-2" />
                                        {{ gallery.category }}
                                    </span>
                                </div>
                                
                                <!-- Media Count -->
                                <div v-if="gallery.mediaCount > 1" class="absolute top-2 right-2">
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-black/40 backdrop-blur-sm text-white text-[8px] font-medium">
                                        <Images class="w-2 h-2" />
                                        {{ gallery.mediaCount }}
                                    </span>
                                </div>
                                
                                <!-- Content -->
                                <div class="absolute bottom-0 left-0 right-0 p-2.5">
                                    <h4 class="text-[11px] sm:text-xs font-bold text-white group-hover:text-emerald-200 transition-colors line-clamp-2 leading-tight">
                                        {{ gallery.title }}
                                    </h4>
                                    <p v-if="gallery.location" class="flex items-center gap-0.5 text-gray-300 text-[9px] mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <MapPin class="w-2 h-2" />
                                        {{ gallery.location }}
                                    </p>
                                </div>
                                
                                <!-- Zoom Icon -->
                                <div class="absolute bottom-2.5 right-2.5 w-6 h-6 rounded-full bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300">
                                    <ZoomIn class="w-3 h-3 text-white" />
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Progress Bar & CTA -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <div class="relative w-24 h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div 
                                    class="absolute top-0 left-0 h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-[width] duration-100"
                                    :style="{ width: `${Math.max(scrollProgress, 10)}%` }"
                                ></div>
                            </div>
                        </div>
                        <Link href="/gallery" class="group inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium text-[11px] sm:text-xs shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            <span>Lihat Semua Galeri</span>
                            <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white/50 backdrop-blur-sm rounded-xl border border-gray-100">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-3">
                    <Camera class="w-6 h-6 text-gray-400" />
                </div>
                <h3 class="text-sm font-bold text-gray-700 mb-1">Belum Ada Galeri</h3>
                <p class="text-gray-500 text-xs max-w-xs mx-auto">Foto-foto menarik akan segera hadir.</p>
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
