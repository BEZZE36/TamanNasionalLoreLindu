<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    Newspaper, ArrowRight, Calendar, Clock, TrendingUp, 
    Eye, Bell, ChevronRight, Zap, Radio, AlertCircle, Megaphone
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    news: { type: Array, default: () => [] }
});

const sectionRef = ref(null);
const scrollContainerRef = ref(null);
const scrollProgress = ref(0);
let ctx;
let rafId = null;

// Format date nicely
const formatDate = (date) => {
    if (!date) return 'Baru';
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// Time ago format
const timeAgo = (date) => {
    if (!date) return 'Baru saja';
    const now = new Date();
    const past = new Date(date);
    const diffMs = now - past;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffHours < 1) return 'Baru saja';
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays < 7) return `${diffDays} hari lalu`;
    return formatDate(date);
};

// Get category icon
const getCategoryIcon = (category) => {
    const icons = {
        'Pengumuman': Bell,
        'Event': Calendar,
        'Update': Zap,
        'Info': AlertCircle,
        'default': Newspaper
    };
    return icons[category] || icons.default;
};

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
        gsap.fromTo('.news-header > *', 
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
        
        // Featured news
        gsap.fromTo('.news-featured', 
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
        
        // News items
        gsap.fromTo('.news-item', 
            { opacity: 0, y: 30 },
            { 
                opacity: 1, y: 0, 
                duration: 0.5, stagger: 0.08, 
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
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-gray-50/30 to-white overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-[10%] w-48 sm:w-64 lg:w-80 h-48 sm:h-64 lg:h-80 bg-gradient-to-br from-rose-200/25 to-pink-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute bottom-0 right-[10%] w-44 sm:w-56 lg:w-72 h-44 sm:h-56 lg:h-72 bg-gradient-to-tl from-cyan-200/20 to-blue-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="news-header text-left mb-8 sm:mb-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-rose-50 to-pink-50 border border-rose-200/50 text-rose-700 mb-2 sm:mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-rose-500"></span>
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Berita Terkini</span>
                    <Radio class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-rose-500" />
                </div>
                
                <!-- Title -->
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                    Berita & 
                    <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-rose-500 via-pink-500 to-orange-500 bg-clip-text text-transparent">
                        Pengumuman
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500">Informasi terkini seputar Taman Nasional Lore Lindu</p>
            </div>

            <!-- News Layout -->
            <div v-if="news && news.length > 0" class="space-y-4 sm:space-y-5">
                <!-- Featured News (First Item) -->
                <Link v-if="news[0]" :href="`/news/${news[0].slug}`" class="news-featured group block">
                    <div class="relative h-56 sm:h-64 lg:h-80 rounded-xl sm:rounded-2xl overflow-hidden shadow-lg sm:shadow-xl hover:shadow-2xl transition-all duration-500 border border-gray-100">
                        <img 
                            :src="news[0].image_url || '/images/placeholder-no-image.svg'" 
                            :alt="news[0].title" 
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                            loading="lazy" 
                            @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                        >
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                        
                        <!-- Top Badges -->
                        <div class="absolute top-3 sm:top-4 left-3 sm:left-4 right-3 sm:right-4 flex items-start justify-between z-10">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gradient-to-r from-rose-500 to-pink-500 text-white text-[9px] sm:text-[10px] font-bold shadow-lg">
                                <Zap class="w-3 h-3" />
                                <span>TERBARU</span>
                            </div>
                            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-black/40 backdrop-blur-sm text-white/90 text-[9px] sm:text-[10px] font-medium">
                                <Clock class="w-3 h-3" />
                                {{ timeAgo(news[0].created_at) }}
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-5 lg:p-6">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[9px] sm:text-[10px] font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 shadow-lg mb-2">
                                <Megaphone class="w-3 h-3" />
                                {{ news[0].category || 'Berita' }}
                            </span>
                            
                            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-white group-hover:text-emerald-200 transition-colors mb-2 line-clamp-2 leading-tight">
                                {{ news[0].title }}
                            </h3>
                            
                            <p class="text-gray-300 text-xs sm:text-sm leading-relaxed mb-3 line-clamp-2 max-w-2xl">
                                {{ news[0].excerpt || 'Baca berita terbaru tentang update dan informasi penting...' }}
                            </p>
                            
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3 text-gray-400 text-[10px] sm:text-xs">
                                    <span class="flex items-center gap-1">
                                        <Calendar class="w-3 h-3" />
                                        {{ formatDate(news[0].created_at) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Eye class="w-3 h-3" />
                                        {{ news[0].views_count || 0 }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/15 backdrop-blur-sm text-white font-semibold text-[10px] sm:text-xs group-hover:bg-white/25 transition-all">
                                    <span>Baca</span>
                                    <ArrowRight class="w-3 h-3" />
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Other News - Horizontal Scroll -->
                <div v-if="news.length > 1" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900">Berita Lainnya</h3>
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
                            v-for="item in news.slice(1, 9)" 
                            :key="item.id" 
                            :href="`/news/${item.slug}`" 
                            class="news-item group flex-shrink-0 snap-start"
                        >
                            <div class="relative w-[200px] sm:w-[240px] h-36 sm:h-40 rounded-xl overflow-hidden shadow-sm hover:shadow-lg border border-gray-200/80 hover:border-rose-200 hover:-translate-y-1 transition-all duration-300">
                                <img 
                                    :src="item.image_url || '/images/placeholder-no-image.svg'" 
                                    :alt="item.title" 
                                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                    loading="lazy" 
                                    @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                                >
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>
                                
                                <!-- Time Badge -->
                                <div class="absolute top-2 right-2 z-10">
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-black/40 backdrop-blur-sm text-white/90 text-[8px] font-medium">
                                        <Clock class="w-2.5 h-2.5" />
                                        {{ timeAgo(item.created_at) }}
                                    </span>
                                </div>
                                
                                <!-- Content -->
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[8px] font-bold text-white bg-gradient-to-r from-teal-500 to-cyan-500 shadow mb-1.5">
                                        <component :is="getCategoryIcon(item.category)" class="w-2.5 h-2.5" />
                                        {{ item.category || 'Berita' }}
                                    </span>
                                    
                                    <h4 class="text-xs font-bold text-white group-hover:text-emerald-200 transition-colors line-clamp-2 leading-tight">
                                        {{ item.title }}
                                    </h4>
                                </div>
                                
                                <!-- Hover Arrow -->
                                <div class="absolute bottom-3 right-3 w-6 h-6 rounded-full bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300">
                                    <ArrowRight class="w-3 h-3 text-white" />
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Progress Bar & CTA -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <div class="relative w-24 h-1 bg-gray-200 rounded-full overflow-hidden">
                                <div 
                                    class="absolute top-0 left-0 h-full bg-gradient-to-r from-rose-500 to-pink-500 rounded-full transition-[width] duration-100"
                                    :style="{ width: `${Math.max(scrollProgress, 10)}%` }"
                                ></div>
                            </div>
                        </div>
                        <Link href="/news" class="group inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-rose-500 to-pink-600 text-white font-medium text-[11px] sm:text-xs shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            <span>Lihat Semua Berita</span>
                            <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white/50 backdrop-blur-sm rounded-xl border border-gray-100">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-rose-100 to-pink-50 flex items-center justify-center mx-auto mb-3">
                    <Newspaper class="w-6 h-6 text-rose-400" />
                </div>
                <h3 class="text-sm font-bold text-gray-700 mb-1">Belum Ada Berita</h3>
                <p class="text-gray-500 text-xs max-w-xs mx-auto">Berita terbaru akan segera hadir.</p>
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
