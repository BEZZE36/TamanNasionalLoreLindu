<script setup>
/**
 * Wishlist Page - Premium Redesign
 * Matches Destination Hero Design with GSAP animations
 * Features: Hero section, 3D cards, parallax effects, responsive design, tabs for different content types
 */
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { 
    Heart, MapPin, Trash2, ArrowRight, Compass, Star, Clock, 
    Bookmark, TrendingUp, HeartCrack, Search, Leaf, Bird, FileText, Newspaper, Image, Eye
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

defineOptions({ layout: PublicLayout });

const props = defineProps({
    destinations: Object, // Paginated
    blogArticles: Array,  // Blog favorites
    newsArticles: Array,  // News favorites
});

// Refs
const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const cardsRef = ref(null);
const counters = ref({ total: 0 });

// Tabs
const tabs = [
    { id: 'destinations', name: 'Destinasi', icon: MapPin },
    { id: 'blog', name: 'Blog', icon: FileText },
    { id: 'news', name: 'Berita', icon: Newspaper },
];
const activeTab = ref('destinations');

let ctx;

// Stats computed
const totalWishlist = computed(() => {
    return (props.destinations?.data?.length || 0) + 
           (props.blogArticles?.length || 0) + 
           (props.newsArticles?.length || 0);
});

const latestAdded = computed(() => {
    if (props.destinations?.data?.length) return props.destinations.data[0]?.name;
    if (props.blogArticles?.length) return props.blogArticles[0]?.title;
    if (props.newsArticles?.length) return props.newsArticles[0]?.title;
    return 'Belum ada';
});

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

// Smooth scroll to wishlist content
const scrollToContent = () => {
    gsap.to(window, { duration: 1, scrollTo: { y: '#wishlist-content', offsetY: 80 }, ease: 'power3.inOut' });
};

// Remove from wishlist with animation
const removeFromWishlist = async (slug, index) => {
    // Animate card removal
    const card = document.querySelector(`[data-wishlist-index="${index}"]`);
    if (card) {
        await gsap.to(card, { 
            scale: 0.8, 
            opacity: 0, 
            y: -20,
            duration: 0.4, 
            ease: 'power2.in' 
        });
    }
    
    router.post(`/wishlist/${slug}/toggle`, {}, {
        preserveScroll: true,
        preserveState: true
    });
};

// Card hover effects
const cardStates = ref({});
const initCardState = (index) => {
    if (!cardStates.value[index]) {
        cardStates.value[index] = { 
            isHovered: false, 
            tiltStyle: {},
            glowPosition: { x: 50, y: 50 }
        };
    }
    return cardStates.value[index];
};

const handleCardMouseMove = (e, index) => {
    const card = e.currentTarget;
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const rotateX = ((y - rect.height / 2) / rect.height) * 10;
    const rotateY = ((rect.width / 2 - x) / rect.width) * 10;
    
    const state = cardStates.value[index];
    if (state) {
        state.tiltStyle = { 
            transform: `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)` 
        };
        state.glowPosition = { x: (x / rect.width) * 100, y: (y / rect.height) * 100 };
    }
};

const handleCardMouseLeave = (index) => {
    const state = cardStates.value[index];
    if (state) {
        state.isHovered = false;
        state.tiltStyle = { transform: 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)' };
    }
};

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(price || 0);
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Hero entrance animation
        const tl = gsap.timeline({ delay: 0.3 });
        tl.fromTo('.hero-item', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );

        // Counter animations
        gsap.to(counters.value, {
            total: totalWishlist.value,
            duration: 2,
            ease: 'power2.out',
            delay: 0.8
        });

        // Floating shapes animation
        gsap.to('.float-shape', {
            y: -15,
            duration: 3,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: { each: 0.5, from: 'random' }
        });

        // Parallax wave
        gsap.to('.wave-layer', {
            yPercent: 20,
            ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: 1.5 }
        });

        // Background parallax
        gsap.to(backgroundRef.value, {
            yPercent: 30,
            ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: true }
        });

        // Content parallax & fade
        gsap.to(contentRef.value, {
            yPercent: -20,
            opacity: 0,
            ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: true }
        });

        // Hero scale
        gsap.to(heroRef.value, {
            scale: 0.95,
            ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: true }
        });

        // Cards entrance animation
        if (props.destinations?.data?.length) {
            gsap.fromTo('.wishlist-card', 
                { opacity: 0, y: 40, scale: 0.95 }, 
                { 
                    opacity: 1, y: 0, scale: 1, 
                    duration: 0.6, 
                    stagger: 0.1, 
                    ease: 'power3.out',
                    scrollTrigger: { 
                        trigger: '#wishlist-content', 
                        start: 'top 80%', 
                        toggleActions: 'play none none reverse' 
                    }
                }
            );
        }

        // Empty state animation
        if (!props.destinations?.data?.length) {
            gsap.fromTo('.empty-state', 
                { opacity: 0, y: 30 }, 
                { 
                    opacity: 1, y: 0, 
                    duration: 0.8, 
                    ease: 'power3.out',
                    scrollTrigger: { 
                        trigger: '#wishlist-content', 
                        start: 'top 85%' 
                    }
                }
            );

            // Floating heart animation
            gsap.to('.empty-heart', {
                y: -10,
                rotation: 5,
                duration: 2,
                ease: 'sine.inOut',
                yoyo: true,
                repeat: -1
            });
        }
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <Head title="Wishlist Saya" />

    <div class="min-h-screen bg-gray-50">
        <!-- Premium Hero Section -->
        <section ref="heroRef" class="relative min-h-[80vh] sm:min-h-[85vh] flex items-center justify-center overflow-hidden">
            <!-- Background with gradient and effects -->
            <div ref="backgroundRef" class="absolute inset-0">
                <!-- Dark gradient base -->
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-rose-950/80 to-slate-900"></div>
                
                <!-- Animated Mesh Gradient -->
                <div class="absolute inset-0 opacity-60">
                    <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(244,63,94,0.15),transparent_50%)]"></div>
                    <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(236,72,153,0.12),transparent_50%)]"></div>
                    <div class="absolute bottom-0 left-1/2 w-full h-full bg-[radial-gradient(ellipse_at_50%_100%,rgba(168,85,247,0.08),transparent_50%)]"></div>
                </div>

                <!-- Floating Geometric Shapes -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="float-shape absolute top-[15%] left-[8%] w-16 h-16 sm:w-20 sm:h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                    <div class="float-shape absolute top-[25%] right-[12%] w-10 h-10 sm:w-14 sm:h-14 border border-rose-500/10 rounded-full"></div>
                    <div class="float-shape absolute top-[55%] left-[15%] w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-pink-500/5 to-rose-500/5 rounded-lg rotate-45"></div>
                    <div class="float-shape absolute bottom-[25%] right-[8%] w-12 h-12 sm:w-16 sm:h-16 border border-pink-500/[0.08] rounded-xl -rotate-12"></div>
                    <div class="float-shape absolute top-[40%] left-[50%] w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-rose-500/5 to-transparent rounded-full"></div>
                </div>

                <!-- Subtle Grid Pattern -->
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

                <!-- Premium Wave SVG -->
                <svg class="wave-layer absolute -bottom-1 left-0 right-0 w-full h-32 sm:h-40 md:h-44" viewBox="0 0 1440 200" fill="none" preserveAspectRatio="none">
                    <path d="M0,120L60,110C120,100,240,80,360,85C480,90,600,120,720,130C840,140,960,130,1080,115C1200,100,1320,80,1380,70L1440,60L1440,200L0,200Z" fill="rgba(255,255,255,0.02)"/>
                    <path d="M0,140L48,135C96,130,192,120,288,115C384,110,480,110,576,120C672,130,768,150,864,155C960,160,1056,150,1152,135C1248,120,1344,100,1392,90L1440,80L1440,200L0,200Z" fill="rgba(244,63,94,0.03)"/>
                    <path d="M0,160L60,155C120,150,240,140,360,145C480,150,600,170,720,175C840,180,960,170,1080,160C1200,150,1320,140,1380,135L1440,130L1440,200L0,200Z" fill="#f9fafb"/>
                </svg>
            </div>

            <!-- Hero Content -->
            <div ref="contentRef" class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-24">
                <div class="text-center max-w-3xl mx-auto">
                    <!-- Badge with blinking dot -->
                    <div class="hero-item inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-3 sm:mb-4 hover:bg-white/15 hover:border-white/30 hover:scale-105 transition-all duration-300 cursor-default">
                        <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-rose-500"></span>
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-semibold tracking-wide">Koleksi Favorit</span>
                        <Heart class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-rose-400 fill-rose-400" />
                    </div>

                    <!-- Title -->
                    <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black text-white leading-tight mb-1 sm:mb-2">
                        Wishlist
                    </h1>
                    <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black bg-gradient-to-r from-rose-300 via-pink-200 to-purple-300 bg-clip-text text-transparent mb-3 sm:mb-4">
                        Saya ❤️
                    </h1>

                    <!-- Description -->
                    <p class="hero-item text-white/60 text-[11px] sm:text-xs md:text-sm max-w-xl mx-auto mb-5 sm:mb-6 leading-relaxed px-2 sm:px-0">
                        Koleksi konten favorit yang telah Anda simpan - 
                        <span class="text-rose-400 font-semibold">Destinasi, Flora, Fauna, Blog, Berita & Galeri</span>
                    </p>

                    <!-- Stats Cards -->
                    <div class="hero-item grid grid-cols-2 gap-2 sm:gap-3 max-w-xs sm:max-w-sm mx-auto mb-8 sm:mb-10 px-4 sm:px-0">
                        <!-- Total Wishlist -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2.5 sm:p-3 hover:bg-rose-500/15 hover:border-rose-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-rose-500/10 transition-all duration-300 cursor-default">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 mx-auto mb-1.5 rounded-lg bg-rose-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-rose-500/30 transition-all duration-300">
                                <Heart class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-rose-300 fill-rose-300" />
                            </div>
                            <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-rose-200 transition-colors">{{ Math.round(counters.total) }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Tersimpan</p>
                        </div>

                        <!-- Last Added -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2.5 sm:p-3 hover:bg-pink-500/15 hover:border-pink-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-pink-500/10 transition-all duration-300 cursor-default">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 mx-auto mb-1.5 rounded-lg bg-pink-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-pink-500/30 transition-all duration-300">
                                <Bookmark class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-pink-300" />
                            </div>
                            <p class="text-[10px] sm:text-xs font-bold text-white group-hover:text-pink-200 transition-colors line-clamp-1">{{ latestAdded || '-' }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Terakhir</p>
                        </div>
                    </div>

                    <!-- Scroll Indicator -->
                    <button @click="scrollToContent" class="hero-item flex flex-col items-center gap-1 sm:gap-1.5 group cursor-pointer mx-auto">
                        <span class="text-[8px] sm:text-[9px] text-white/30 uppercase tracking-[0.15em] font-medium group-hover:text-white/50 transition-colors">Scroll</span>
                        <div class="relative w-4 h-6 sm:w-5 sm:h-8 rounded-full border border-white/20 group-hover:border-white/40 flex justify-center pt-1 sm:pt-1.5 transition-colors">
                            <div class="w-0.5 h-1.5 sm:w-1 sm:h-2 bg-white/40 group-hover:bg-white/60 rounded-full animate-scroll-pulse transition-colors"></div>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <!-- Wishlist Content -->
        <section id="wishlist-content" class="py-10 sm:py-14 md:py-16 lg:py-20 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
            <!-- Background decoration -->
            <div class="absolute top-0 right-0 w-64 sm:w-96 h-64 sm:h-96 bg-rose-500/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 sm:w-80 h-64 sm:h-80 bg-pink-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-8 sm:mb-10 lg:mb-12">
                    <h2 class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                        Konten <span class="bg-gradient-to-r from-rose-500 to-pink-500 bg-clip-text text-transparent">Favorit</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto">
                        Destinasi, blog, dan berita yang telah Anda simpan
                    </p>
                </div>

                <!-- Tabs -->
                <div class="flex justify-center gap-2 sm:gap-3 mb-8">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-300',
                            activeTab === tab.id 
                                ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-lg shadow-rose-500/25' 
                                : 'bg-white text-gray-600 hover:bg-gray-100 shadow-md'
                        ]"
                    >
                        <component :is="tab.icon" class="w-4 h-4" />
                        {{ tab.name }}
                        <span v-if="tab.id === 'destinations'" class="ml-1 px-1.5 py-0.5 bg-white/20 rounded-md text-[10px]">{{ destinations?.data?.length || 0 }}</span>
                        <span v-else-if="tab.id === 'blog'" class="ml-1 px-1.5 py-0.5 bg-white/20 rounded-md text-[10px]">{{ blogArticles?.length || 0 }}</span>
                        <span v-else-if="tab.id === 'news'" class="ml-1 px-1.5 py-0.5 bg-white/20 rounded-md text-[10px]">{{ newsArticles?.length || 0 }}</span>
                    </button>
                </div>

                <!-- Destinations Grid -->
                <template v-if="activeTab === 'destinations' && destinations?.data?.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
                        <div 
                            v-for="(destination, index) in destinations.data" 
                            :key="destination.id"
                            :data-wishlist-index="index"
                            class="wishlist-card group"
                            @mouseenter="() => { initCardState(index); cardStates[index].isHovered = true; }"
                            @mousemove="(e) => handleCardMouseMove(e, index)"
                            @mouseleave="() => handleCardMouseLeave(index)"
                        >
                            <div 
                                class="relative h-[340px] sm:h-[360px] lg:h-[380px] rounded-xl sm:rounded-2xl overflow-hidden bg-white shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100/80"
                                :style="cardStates[index]?.tiltStyle || {}"
                            >
                                <!-- Dynamic Glow Effect -->
                                <div 
                                    v-if="cardStates[index]?.isHovered" 
                                    class="absolute inset-0 pointer-events-none z-10 opacity-50"
                                    :style="`background: radial-gradient(circle at ${cardStates[index]?.glowPosition?.x || 50}% ${cardStates[index]?.glowPosition?.y || 50}%, rgba(244,63,94,0.15), transparent 50%);`"
                                ></div>

                                <!-- Image Section -->
                                <div class="relative h-[55%] overflow-hidden">
                                    <img 
                                        :src="destination.primary_image_url || '/images/placeholder-no-image.svg'" 
                                        :alt="destination.name"
                                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                        loading="lazy"
                                    >
                                    
                                    <!-- Premium Gradient Overlay -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                                    
                                    <!-- Animated Corner Accents -->
                                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-500 delay-100">
                                        <div class="w-8 h-px bg-gradient-to-l from-white/60 to-transparent transform origin-right group-hover:scale-x-100 scale-x-0 transition-transform duration-500"></div>
                                        <div class="w-px h-8 bg-gradient-to-b from-white/60 to-transparent absolute top-0 right-0 transform origin-top group-hover:scale-y-100 scale-y-0 transition-transform duration-500 delay-75"></div>
                                    </div>

                                    <!-- Top Badges -->
                                    <div class="absolute top-2.5 sm:top-3 left-2.5 sm:left-3 flex flex-wrap items-center gap-1 sm:gap-1.5 z-20">
                                        <span class="inline-flex items-center gap-0.5 sm:gap-1 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg bg-black/40 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-semibold border border-white/10">
                                            <MapPin class="w-2 h-2 sm:w-2.5 sm:h-2.5" />
                                            {{ destination.category?.name || 'Wisata' }}
                                        </span>
                                        <span v-if="destination.is_featured" class="inline-flex items-center gap-0.5 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[8px] sm:text-[9px] font-bold uppercase shadow-lg shadow-amber-500/30">
                                            <TrendingUp class="w-2 h-2 sm:w-2.5 sm:h-2.5" />
                                            Populer
                                        </span>
                                    </div>

                                    <!-- Rating Badge -->
                                    <div class="absolute top-2.5 sm:top-3 right-2.5 sm:right-3 z-20">
                                        <div class="flex items-center gap-0.5 sm:gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-lg bg-white/95 shadow-lg text-[9px] sm:text-[10px] font-bold text-gray-900">
                                            <Star class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-amber-500 fill-amber-500" />
                                            {{ destination.rating || '4.8' }}
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <button 
                                        @click.prevent="removeFromWishlist(destination.slug, index)"
                                        class="absolute bottom-2.5 sm:bottom-3 right-2.5 sm:right-3 z-20 w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-red-500/80 backdrop-blur-md border border-red-400/50 flex items-center justify-center text-white hover:bg-red-600 hover:scale-110 transition-all duration-300 shadow-lg group/btn"
                                    >
                                        <Trash2 class="w-3.5 h-3.5 sm:w-4 sm:h-4 transition-transform group-hover/btn:rotate-12" />
                                    </button>

                                    <!-- Title Overlay -->
                                    <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                                        <h3 class="text-sm sm:text-base font-bold text-white line-clamp-1 mb-0.5 sm:mb-1 group-hover:text-rose-200 transition-colors duration-300">
                                            {{ destination.name }}
                                        </h3>
                                        <div class="flex items-center gap-1.5 sm:gap-2 text-white/70 text-[9px] sm:text-[10px]">
                                            <span class="flex items-center gap-0.5 sm:gap-1">
                                                <MapPin class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                                {{ destination.city || 'Sulawesi Tengah' }}
                                            </span>
                                            <span v-if="destination.operating_hours" class="flex items-center gap-0.5 sm:gap-1">
                                                <Clock class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                                {{ destination.operating_hours }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content Section -->
                                <div class="p-3 sm:p-4 h-[45%] flex flex-col">
                                    <!-- Description -->
                                    <p class="flex-1 text-gray-600 text-[10px] sm:text-[11px] leading-relaxed line-clamp-3 sm:line-clamp-4">
                                        {{ destination.short_description || 'Destinasi wisata alam yang menakjubkan dengan pemandangan indah dan pengalaman tak terlupakan.' }}
                                    </p>

                                    <!-- Bottom Row -->
                                    <div class="flex items-center justify-between pt-2.5 sm:pt-3 mt-auto border-t border-gray-100">
                                        <div>
                                            <span class="text-[8px] sm:text-[9px] text-gray-400 uppercase tracking-wider font-medium block">Mulai dari</span>
                                            <p class="text-xs sm:text-sm font-bold bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">
                                                {{ formatPrice(destination.prices?.[0]?.price || destination.adult_price) }}
                                            </p>
                                        </div>
                                        
                                        <!-- CTA Button -->
                                        <Link 
                                            :href="`/destinations/${destination.slug}`"
                                            class="relative overflow-hidden flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 text-white text-[9px] sm:text-[10px] font-semibold shadow-lg shadow-rose-500/25 group-hover:shadow-rose-500/40 transition-all duration-300"
                                        >
                                            <span>Jelajahi</span>
                                            <ArrowRight class="w-2.5 h-2.5 sm:w-3 sm:h-3 transform group-hover:translate-x-1 transition-transform duration-300" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="destinations.links && destinations.last_page > 1" class="mt-10 sm:mt-12 flex justify-center gap-1 sm:gap-2 flex-wrap">
                        <template v-for="link in destinations.links" :key="link.label">
                            <Link 
                                v-if="link.url" 
                                :href="link.url"
                                :class="[
                                    'px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-all duration-300', 
                                    link.active 
                                        ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-lg shadow-rose-500/25' 
                                        : 'bg-white text-gray-700 hover:bg-gray-100 shadow-md hover:shadow-lg'
                                ]"
                                v-html="link.label"
                            />
                            <span 
                                v-else 
                                class="px-3 sm:px-4 py-1.5 sm:py-2 text-gray-400 text-xs sm:text-sm" 
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </template>

                <!-- Blog Articles Grid -->
                <div v-if="activeTab === 'blog'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
                    <Link 
                        v-for="article in blogArticles" 
                        :key="article.id"
                        :href="`/blog/${article.slug}`"
                        class="wishlist-card group bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100/80"
                    >
                        <div class="relative h-40 sm:h-48 overflow-hidden">
                            <img 
                                :src="article.featured_image || '/images/placeholder-no-image.svg'" 
                                :alt="article.title"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg bg-cyan-500/90 text-white text-[10px] font-semibold">Blog</span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 text-sm line-clamp-2 mb-2 group-hover:text-cyan-600 transition-colors">{{ article.title }}</h3>
                            <p class="text-gray-500 text-xs line-clamp-2 mb-3">{{ article.excerpt }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span class="flex items-center gap-1"><Eye class="w-3 h-3" />{{ article.views_count || 0 }}</span>
                                <span class="flex items-center gap-1"><Heart class="w-3 h-3" />{{ article.likes_count || 0 }}</span>
                                <span>{{ formatDate(article.published_at) }}</span>
                            </div>
                        </div>
                    </Link>
                    <div v-if="!blogArticles?.length" class="col-span-full text-center py-12">
                        <FileText class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 text-sm">Belum ada artikel blog yang disimpan</p>
                        <Link href="/blog" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-cyan-500 text-white text-xs font-semibold rounded-lg hover:bg-cyan-600 transition-colors">
                            Jelajahi Blog <ArrowRight class="w-3 h-3" />
                        </Link>
                    </div>
                </div>

                <!-- News Articles Grid -->
                <div v-if="activeTab === 'news'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
                    <Link 
                        v-for="article in newsArticles" 
                        :key="article.id"
                        :href="`/news/${article.slug}`"
                        class="wishlist-card group bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100/80"
                    >
                        <div class="relative h-40 sm:h-48 overflow-hidden">
                            <img 
                                :src="article.featured_image || '/images/placeholder-no-image.svg'" 
                                :alt="article.title"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg bg-rose-500/90 text-white text-[10px] font-semibold">Berita</span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 text-sm line-clamp-2 mb-2 group-hover:text-rose-600 transition-colors">{{ article.title }}</h3>
                            <p class="text-gray-500 text-xs line-clamp-2 mb-3">{{ article.excerpt }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span class="flex items-center gap-1"><Eye class="w-3 h-3" />{{ article.views_count || 0 }}</span>
                                <span class="flex items-center gap-1"><Heart class="w-3 h-3" />{{ article.likes_count || 0 }}</span>
                                <span>{{ formatDate(article.published_at) }}</span>
                            </div>
                        </div>
                    </Link>
                    <div v-if="!newsArticles?.length" class="col-span-full text-center py-12">
                        <Newspaper class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 text-sm">Belum ada berita yang disimpan</p>
                        <Link href="/news" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-rose-500 text-white text-xs font-semibold rounded-lg hover:bg-rose-600 transition-colors">
                            Jelajahi Berita <ArrowRight class="w-3 h-3" />
                        </Link>
                    </div>
                </div>

                <!-- Empty State (when destinations tab selected but empty) -->
                <div v-if="activeTab === 'destinations' && !destinations?.data?.length" class="empty-state text-center py-12 sm:py-16 lg:py-20">
                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 mx-auto mb-6 sm:mb-8">
                        <!-- Animated background circles -->
                        <div class="absolute inset-0 bg-gradient-to-br from-rose-100 to-pink-100 rounded-3xl rotate-6 animate-pulse"></div>
                        <div class="absolute inset-2 bg-gradient-to-br from-white to-rose-50 rounded-2xl shadow-xl flex items-center justify-center">
                            <HeartCrack class="empty-heart w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 text-rose-400" />
                        </div>
                        <!-- Floating sparkles -->
                        <div class="absolute -top-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-gradient-to-br from-amber-400 to-orange-400 rounded-full animate-ping"></div>
                        <div class="absolute -bottom-1 -left-1 w-2 h-2 sm:w-3 sm:h-3 bg-gradient-to-br from-rose-400 to-pink-400 rounded-full animate-pulse"></div>
                    </div>
                    
                    <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2 sm:mb-3">
                        Belum Ada Favorit
                    </h3>
                    <p class="text-gray-500 text-xs sm:text-sm mb-6 sm:mb-8 max-w-sm sm:max-w-md mx-auto leading-relaxed px-4">
                        Anda belum menyimpan konten apapun. Jelajahi destinasi, flora, fauna, blog, berita & galeri lalu tambahkan ke wishlist!
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                        <Link 
                            href="/destinations" 
                            class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:-translate-y-0.5 transition-all duration-300"
                        >
                            <Compass class="w-4 h-4 sm:w-5 sm:h-5" />
                            Mulai Jelajahi
                        </Link>
                        <Link 
                            href="/" 
                            class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-white text-gray-700 font-semibold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 border border-gray-200"
                        >
                            <Search class="w-4 h-4 sm:w-5 sm:h-5" />
                            Kembali ke Beranda
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes scroll-pulse {
    0%, 100% { opacity: 1; transform: translateY(0); }
    50% { opacity: 0.3; transform: translateY(4px); }
}
.animate-scroll-pulse { animation: scroll-pulse 1.5s ease-in-out infinite; }

/* Performance optimizations */
.hero-item, .float-shape, .wishlist-card {
    will-change: transform, opacity;
}

/* Card transition */
.wishlist-card > div {
    transition: transform 0.2s ease-out, box-shadow 0.5s ease;
}
</style>
