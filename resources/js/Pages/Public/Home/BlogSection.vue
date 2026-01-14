<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    BookOpen, ArrowRight, Calendar, Clock, User, 
    Eye, TrendingUp, ChevronRight, Bookmark, Feather
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ articles: { type: Array, default: () => [] } });
const sectionRef = ref(null);
const scrollContainerRef = ref(null);
const scrollProgress = ref(0);
let ctx;
let rafId = null;

// Category colors with premium gradients
const getCategoryGradient = (color) => ({
    primary: 'from-emerald-500 to-teal-500',
    emerald: 'from-emerald-500 to-teal-500',
    amber: 'from-amber-500 to-orange-500',
    cyan: 'from-cyan-500 to-blue-500',
    rose: 'from-rose-500 to-pink-500',
    blue: 'from-blue-500 to-indigo-500',
    purple: 'from-purple-500 to-violet-500',
    green: 'from-green-500 to-emerald-500'
}[color] || 'from-emerald-500 to-teal-500');

// Format date nicely
const formatDate = (date) => {
    if (!date) return 'Baru';
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
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
        gsap.fromTo('.blog-header > *', 
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
        
        // Featured article
        gsap.fromTo('.blog-featured', 
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
        
        // Article cards
        gsap.fromTo('.blog-card', 
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
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-emerald-50/20 to-white overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-8 sm:-top-12 lg:-top-16 right-[10%] w-48 sm:w-64 lg:w-80 h-48 sm:h-64 lg:h-80 bg-gradient-to-br from-emerald-200/30 to-teal-200/15 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute bottom-0 left-[5%] w-44 sm:w-56 lg:w-72 h-44 sm:h-56 lg:h-72 bg-gradient-to-tr from-cyan-200/25 to-blue-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="blog-header text-left mb-8 sm:mb-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-2 sm:mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Blog & Artikel</span>
                    <BookOpen class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-500" />
                </div>
                
                <!-- Title -->
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                    Jelajahi 
                    <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        Inspirasi Wisata
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500">Tips perjalanan dan cerita petualangan menarik</p>
            </div>

            <!-- Articles Layout -->
            <div v-if="articles && articles.length > 0" class="space-y-4 sm:space-y-5">
                <!-- Featured Article (First Item) -->
                <Link v-if="articles[0]" :href="`/blog/${articles[0].slug}`" class="blog-featured group block">
                    <div class="relative rounded-xl sm:rounded-2xl overflow-hidden shadow-lg sm:shadow-xl hover:shadow-2xl transition-all duration-500 bg-white border border-gray-100">
                        <div class="grid lg:grid-cols-2 gap-0">
                            <!-- Image Side -->
                            <div class="relative h-48 sm:h-56 lg:h-72 overflow-hidden">
                                <img 
                                    :src="articles[0].image_url || '/images/placeholder-no-image.svg'" 
                                    :alt="articles[0].title" 
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                                    loading="lazy"
                                    @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-white/30"></div>
                                
                                <!-- Featured badge -->
                                <div class="absolute top-3 left-3 z-10">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[9px] sm:text-[10px] font-bold shadow-lg">
                                        <TrendingUp class="w-3 h-3" />
                                        <span>PILIHAN</span>
                                    </div>
                                </div>

                                <!-- Category badge on image - mobile only -->
                                <div class="absolute bottom-3 left-3 lg:hidden">
                                    <span :class="['inline-block px-2 py-1 rounded-lg text-[9px] font-bold text-white bg-gradient-to-r shadow-lg', getCategoryGradient(articles[0].category_color)]">
                                        {{ articles[0].category_label || 'Artikel' }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Content Side -->
                            <div class="relative p-4 sm:p-5 lg:p-6 flex flex-col justify-center">
                                <!-- Category badge - desktop -->
                                <div class="hidden lg:block mb-2">
                                    <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold text-white bg-gradient-to-r shadow-lg', getCategoryGradient(articles[0].category_color)]">
                                        <Feather class="w-3 h-3" />
                                        {{ articles[0].category_label || 'Artikel' }}
                                    </span>
                                </div>
                                
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 group-hover:text-emerald-600 transition-colors mb-2 line-clamp-2 leading-tight">
                                    {{ articles[0].title }}
                                </h3>
                                
                                <p class="text-gray-500 text-xs sm:text-sm leading-relaxed mb-3 line-clamp-2 lg:line-clamp-3">
                                    {{ articles[0].excerpt || 'Temukan cerita menarik dalam artikel ini...' }}
                                </p>
                                
                                <!-- Meta Info -->
                                <div class="flex flex-wrap items-center gap-3 text-gray-400 text-[10px] sm:text-xs mb-3">
                                    <span class="flex items-center gap-1">
                                        <Calendar class="w-3 h-3" />
                                        {{ formatDate(articles[0].created_at) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Clock class="w-3 h-3" />
                                        {{ articles[0].read_time || '5 min' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Eye class="w-3 h-3" />
                                        {{ articles[0].views_count || 0 }}
                                    </span>
                                </div>
                                
                                <!-- Author & CTA -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center ring-2 ring-emerald-100 shadow-sm">
                                            <User class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" />
                                        </div>
                                        <span class="text-[11px] sm:text-xs text-gray-600 font-medium">{{ articles[0].author || 'Admin' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-[10px] sm:text-xs shadow-md group-hover:shadow-lg transition-all">
                                        <span>Baca</span>
                                        <ArrowRight class="w-3 h-3" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Other Articles - Horizontal Scroll -->
                <div v-if="articles.length > 1" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900">Artikel Lainnya</h3>
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
                            v-for="article in articles.slice(1, 9)" 
                            :key="article.id" 
                            :href="`/blog/${article.slug}`" 
                            class="blog-card group flex-shrink-0 snap-start"
                        >
                            <div class="w-[240px] sm:w-[280px] bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg border border-gray-200/80 hover:border-emerald-200 hover:-translate-y-1 transition-all duration-300">
                                <!-- Image -->
                                <div class="relative h-32 sm:h-36 overflow-hidden">
                                    <img 
                                        :src="article.image_url || '/images/placeholder-no-image.svg'" 
                                        :alt="article.title" 
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                        loading="lazy"
                                        @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                                    
                                    <!-- Category Badge -->
                                    <div class="absolute bottom-2 left-2">
                                        <span :class="['inline-block px-2 py-0.5 rounded text-[8px] sm:text-[9px] font-bold text-white bg-gradient-to-r shadow', getCategoryGradient(article.category_color)]">
                                            {{ article.category_label || 'Artikel' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="p-3">
                                    <!-- Meta -->
                                    <div class="flex items-center gap-2 text-gray-400 text-[9px] mb-1.5">
                                        <span class="flex items-center gap-0.5">
                                            <Calendar class="w-2.5 h-2.5" />
                                            {{ formatDate(article.created_at) }}
                                        </span>
                                        <span class="flex items-center gap-0.5">
                                            <Clock class="w-2.5 h-2.5" />
                                            {{ article.read_time || '5 min' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Title -->
                                    <h4 class="text-xs sm:text-sm font-bold text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 mb-1.5 leading-tight">
                                        {{ article.title }}
                                    </h4>
                                    
                                    <!-- Footer -->
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center ring-1 ring-emerald-200">
                                                <User class="w-2.5 h-2.5 text-emerald-600" />
                                            </div>
                                            <span class="text-[9px] text-gray-500 font-medium">{{ article.author || 'Admin' }}</span>
                                        </div>
                                        <div class="flex items-center gap-0.5 text-emerald-600 font-semibold text-[9px] group-hover:gap-1 transition-all">
                                            <span>Baca</span>
                                            <ArrowRight class="w-2.5 h-2.5" />
                                        </div>
                                    </div>
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
                        <Link href="/blog" class="group inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium text-[11px] sm:text-xs shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            <span>Lihat Semua Artikel</span>
                            <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white/50 backdrop-blur-sm rounded-xl border border-gray-100">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-3">
                    <BookOpen class="w-6 h-6 text-gray-400" />
                </div>
                <h3 class="text-sm font-bold text-gray-700 mb-1">Belum Ada Artikel</h3>
                <p class="text-gray-500 text-xs max-w-xs mx-auto">Artikel menarik akan segera hadir.</p>
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
