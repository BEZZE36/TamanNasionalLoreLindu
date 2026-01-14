<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    Quote, Star, ArrowRight, MessageSquare, User, 
    ChevronLeft, ChevronRight, Sparkles, Heart, MapPin
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    testimonials: { type: Array, default: () => [] },
    avgRating: { type: [Number, String], default: 4.8 },
    totalReviews: { type: Number, default: 0 }
});

const sectionRef = ref(null);
const scrollContainerRef = ref(null);
const scrollProgress = ref(0);
const counters = ref({ rating: 0, reviews: 0 });
let ctx;
let rafId = null;

// Computed stats
const parsedRating = computed(() => parseFloat(props.avgRating) || 4.8);
const displayTestimonials = computed(() => props.testimonials?.slice(0, 8) || []);

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

// Get star rating display
const getStars = (rating) => {
    return Math.round(rating) || 5;
};

// Format date
const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// Get initials from name
const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

// Animate counters
const animateCounters = () => {
    gsap.to(counters.value, { rating: parsedRating.value, duration: 1.5, ease: 'power2.out' });
    gsap.to(counters.value, { reviews: props.totalReviews, duration: 1.8, ease: 'power2.out', snap: { reviews: 1 } });
};

onMounted(() => {
    if (scrollContainerRef.value) {
        scrollContainerRef.value.addEventListener('scroll', handleScroll, { passive: true });
    }

    ctx = gsap.context(() => {
        // Header entrance
        gsap.fromTo('.testi-header > *', 
            { opacity: 0, y: 25, scale: 0.98 }, 
            { 
                opacity: 1, y: 0, scale: 1, 
                duration: 0.6, stagger: 0.1, 
                ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 85%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse',
                    onEnter: () => animateCounters(),
                    onEnterBack: () => animateCounters()
                } 
            }
        );
        
        // Stats cards
        gsap.fromTo('.testi-stat', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { 
                opacity: 1, y: 0, scale: 1, 
                duration: 0.5, stagger: 0.08, 
                ease: 'back.out(1.2)', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
        
        // Testimonial cards
        gsap.fromTo('.testi-card', 
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
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-pink-50/30 to-white overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-8 sm:-top-12 lg:-top-16 left-[10%] w-48 sm:w-64 lg:w-80 h-48 sm:h-64 lg:h-80 bg-gradient-to-br from-pink-200/30 to-rose-200/15 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute bottom-0 right-[5%] w-44 sm:w-56 lg:w-72 h-44 sm:h-56 lg:h-72 bg-gradient-to-tl from-purple-200/25 to-fuchsia-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="testi-header text-left mb-8 sm:mb-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-200/50 text-pink-700 mb-2 sm:mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-pink-500"></span>
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Testimoni</span>
                    <Quote class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-pink-500" />
                </div>
                
                <!-- Title -->
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                    Cerita 
                    <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 bg-clip-text text-transparent">
                        Pengunjung
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 max-w-lg">Pengalaman menakjubkan dari pengunjung yang telah menjelajahi Taman Nasional</p>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-3 gap-2 sm:gap-3 max-w-sm mb-6 sm:mb-8">
                <!-- Rating -->
                <div class="testi-stat group bg-white rounded-xl p-2.5 sm:p-3 shadow-md border border-gray-100 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <Star class="w-4 h-4 text-amber-500 fill-amber-500" />
                        </div>
                        <div>
                            <p class="text-base sm:text-lg font-black text-gray-900">{{ counters.rating.toFixed(1) }}</p>
                            <p class="text-[8px] sm:text-[9px] text-gray-500 font-medium">Rating</p>
                        </div>
                    </div>
                </div>

                <!-- Total Reviews -->
                <div class="testi-stat group bg-white rounded-xl p-2.5 sm:p-3 shadow-md border border-gray-100 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-gradient-to-br from-pink-100 to-pink-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <MessageSquare class="w-4 h-4 text-pink-500" />
                        </div>
                        <div>
                            <p class="text-base sm:text-lg font-black text-gray-900">{{ Math.round(counters.reviews) }}</p>
                            <p class="text-[8px] sm:text-[9px] text-gray-500 font-medium">Ulasan</p>
                        </div>
                    </div>
                </div>

                <!-- Satisfaction -->
                <div class="testi-stat group bg-white rounded-xl p-2.5 sm:p-3 shadow-md border border-gray-100 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <Heart class="w-4 h-4 text-purple-500 fill-purple-500" />
                        </div>
                        <div>
                            <p class="text-base sm:text-lg font-black text-gray-900">98%</p>
                            <p class="text-[8px] sm:text-[9px] text-gray-500 font-medium">Puas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonials Content -->
            <div v-if="displayTestimonials.length > 0" class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900">Ulasan Terbaru</h3>
                    <div class="flex items-center gap-1.5 text-[10px] sm:text-xs text-gray-400">
                        <span>Swipe untuk melihat</span>
                        <ChevronRight class="w-3 h-3" />
                    </div>
                </div>

                <!-- Testimonial Cards Horizontal Scroll -->
                <div 
                    ref="scrollContainerRef"
                    class="flex gap-3 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide"
                    style="-webkit-overflow-scrolling: touch;"
                >
                    <div 
                        v-for="testimonial in displayTestimonials" 
                        :key="testimonial.id" 
                        class="testi-card group flex-shrink-0 snap-start w-[280px] sm:w-[320px]"
                    >
                        <div class="h-full bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 shadow-md hover:shadow-lg border border-gray-100 hover:border-pink-200 hover:-translate-y-1 transition-all duration-300">
                            <!-- Header: Avatar, Name, Stars -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <!-- Avatar -->
                                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center text-white font-bold text-xs sm:text-sm shadow-md">
                                        {{ getInitials(testimonial.name) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-1">{{ testimonial.name }}</h4>
                                        <p v-if="testimonial.destination" class="flex items-center gap-0.5 text-[9px] sm:text-[10px] text-gray-400">
                                            <MapPin class="w-2.5 h-2.5" />
                                            {{ testimonial.destination }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Stars -->
                                <div class="flex gap-0.5">
                                    <Star 
                                        v-for="i in 5" 
                                        :key="i" 
                                        :class="['w-3 h-3', i <= getStars(testimonial.rating) ? 'text-amber-400 fill-amber-400' : 'text-gray-200']" 
                                    />
                                </div>
                            </div>

                            <!-- Quote Content -->
                            <div class="relative mb-3">
                                <Quote class="absolute -top-1 -left-1 w-5 h-5 text-pink-200/50" />
                                <p class="text-gray-600 text-[11px] sm:text-xs leading-relaxed line-clamp-4 pl-4">
                                    {{ testimonial.content || testimonial.message }}
                                </p>
                            </div>

                            <!-- Footer: Date -->
                            <div class="flex items-center justify-between pt-2.5 border-t border-gray-100">
                                <span class="text-[9px] text-gray-400">{{ formatDate(testimonial.created_at) }}</span>
                                <div v-if="testimonial.is_verified" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-green-50 text-green-600 text-[8px] font-medium">
                                    <Sparkles class="w-2 h-2" />
                                    Terverifikasi
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar & CTA -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="relative w-24 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="absolute top-0 left-0 h-full bg-gradient-to-r from-pink-500 to-rose-500 rounded-full transition-[width] duration-100"
                                :style="{ width: `${Math.max(scrollProgress, 10)}%` }"
                            ></div>
                        </div>
                    </div>
                    <Link href="/testimonials" class="group inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-pink-500 to-rose-600 text-white font-medium text-[11px] sm:text-xs shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        <span>Lihat Semua Ulasan</span>
                        <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white/50 backdrop-blur-sm rounded-xl border border-gray-100">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-pink-100 to-rose-50 flex items-center justify-center mx-auto mb-3">
                    <Quote class="w-6 h-6 text-pink-400" />
                </div>
                <h3 class="text-sm font-bold text-gray-700 mb-1">Belum Ada Ulasan</h3>
                <p class="text-gray-500 text-xs max-w-xs mx-auto mb-4">Jadilah yang pertama berbagi pengalaman Anda!</p>
                <Link href="/testimonials/create" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-gradient-to-r from-pink-500 to-rose-600 text-white font-medium text-xs shadow-md hover:shadow-lg transition-all">
                    <MessageSquare class="w-3.5 h-3.5" />
                    <span>Tulis Ulasan</span>
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
