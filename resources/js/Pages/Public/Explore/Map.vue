<script setup>
/**
 * Map.vue - Explore Map Page
 * Premium redesign with GSAP animations, glassmorphism, satellite maps
 */
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
    Map, Star, MapPin, Navigation, X, ExternalLink, Search, 
    Filter, ChevronRight, Ticket, Clock, Compass, Globe2,
    Layers, ZoomIn, ZoomOut
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    destinations: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] }
});

const heroRef = ref(null);
const contentRef = ref(null);
const selectedDestination = ref(null);
const searchQuery = ref('');
const selectedCategory = ref(null);
const mapCenter = { lat: -1.5, lng: 120.25 };
let ctx;

// Filtered destinations
const filteredDestinations = computed(() => {
    let results = [...props.destinations];
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        results = results.filter(d => 
            d.name.toLowerCase().includes(query) || 
            d.city?.toLowerCase().includes(query) ||
            d.category?.toLowerCase().includes(query)
        );
    }
    
    if (selectedCategory.value) {
        results = results.filter(d => d.category_id === selectedCategory.value);
    }
    
    return results;
});

// Get map embed URL - prioritize database embed, fallback to coordinates
const getMapEmbedUrl = computed(() => {
    const dest = selectedDestination.value;
    
    if (dest?.google_maps_embed) {
        // Extract src from iframe if it's a full iframe tag
        const match = dest.google_maps_embed.match(/src="([^"]+)"/);
        let src = match ? match[1] : dest.google_maps_embed;
        // Ensure satellite view
        if (!src.includes('maptype=')) {
            src += (src.includes('?') ? '&' : '?') + 'maptype=satellite';
        }
        return src;
    }
    
    // Fallback to coordinates-based embed with satellite view
    const lat = dest?.lat || mapCenter.lat;
    const lng = dest?.lng || mapCenter.lng;
    const zoom = dest ? 16 : 10;
    return `https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d${dest ? 3000 : 255281}!2d${lng}!3d${lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sid!4v1704000000000!5m2!1sen!2sid`;
});

// Select destination
const selectDestination = (dest) => {
    selectedDestination.value = dest;
    
    // Animate the overlay
    gsap.fromTo('.dest-overlay', 
        { opacity: 0, y: 30, scale: 0.95 },
        { opacity: 1, y: 0, scale: 1, duration: 0.4, ease: 'back.out(1.5)' }
    );
};

// Close sidebar
const closeDetails = () => {
    gsap.to('.dest-overlay', {
        opacity: 0, y: 20, scale: 0.95, duration: 0.2,
        onComplete: () => { selectedDestination.value = null; }
    });
};

// Clear filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = null;
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Hero entrance animations
        const tl = gsap.timeline({ delay: 0.2 });
        tl.fromTo('.hero-badge', { opacity: 0, y: -20, scale: 0.8 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, ease: 'back.out(2)' })
          .fromTo('.hero-title', { opacity: 0, y: 30, filter: 'blur(10px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.6, ease: 'power3.out' }, '-=0.2')
          .fromTo('.hero-desc', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4 }, '-=0.3')
          .fromTo('.hero-stats > *', { opacity: 0, scale: 0.8, y: 15 }, { opacity: 1, scale: 1, y: 0, duration: 0.4, stagger: 0.1, ease: 'back.out(2)' }, '-=0.2');
        
        // Floating orb animations
        gsap.to('.float-orb-1', { y: -20, duration: 4, repeat: -1, yoyo: true, ease: 'sine.inOut' });
        gsap.to('.float-orb-2', { y: 15, x: -10, duration: 5, repeat: -1, yoyo: true, ease: 'sine.inOut' });
        gsap.to('.float-orb-3', { y: -15, x: 10, duration: 6, repeat: -1, yoyo: true, ease: 'sine.inOut' });
        
        // Content section entrance
        gsap.fromTo('.content-section', 
            { opacity: 0, y: 40 },
            { opacity: 1, y: 0, duration: 0.6, delay: 0.6, ease: 'power2.out' }
        );
        
        // Stagger destination cards
        gsap.fromTo('.dest-card',
            { opacity: 0, x: -20 },
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.8, ease: 'power2.out' }
        );
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div ref="heroRef">
        <Head>
            <title>Explore Map - TNLL</title>
            <meta name="description" content="Jelajahi peta interaktif satelit Taman Nasional Lore Lindu dengan lokasi destinasi wisata">
        </Head>
        
        <!-- Hero Section -->
        <section class="relative min-h-[45vh] sm:min-h-[50vh] overflow-hidden">
            <!-- Background Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-teal-900 to-cyan-950"></div>
            
            <!-- Radial Gradient Overlays -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(20,184,166,0.2),transparent_50%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(6,182,212,0.15),transparent_50%)]"></div>
            
            <!-- Floating Orbs -->
            <div class="float-orb-1 absolute top-[15%] right-[10%] w-32 h-32 sm:w-48 sm:h-48 lg:w-64 lg:h-64 bg-teal-400/15 rounded-full blur-3xl"></div>
            <div class="float-orb-2 absolute bottom-[20%] left-[5%] w-24 h-24 sm:w-40 sm:h-40 lg:w-56 lg:h-56 bg-cyan-400/10 rounded-full blur-3xl"></div>
            <div class="float-orb-3 absolute top-1/2 left-1/2 w-40 h-40 sm:w-60 sm:h-60 bg-emerald-400/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            
            <!-- Floating Geometric Shapes -->
            <div class="absolute top-[20%] right-[15%] w-16 h-16 sm:w-20 sm:h-20 border border-white/5 rounded-full animate-pulse-slow"></div>
            <div class="absolute bottom-[30%] left-[8%] w-12 h-12 sm:w-16 sm:h-16 border border-teal-500/10 rounded-2xl rotate-12 animate-float"></div>
            <div class="absolute top-[35%] left-[25%] w-8 h-8 sm:w-10 sm:h-10 border border-cyan-400/10 rounded-lg rotate-45 animate-float-delayed hidden sm:block"></div>
            
            <!-- Wave Transition -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-16 sm:h-20 lg:h-24" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
                <path d="M0,50L60,45C120,40,240,30,360,35C480,40,600,60,720,65C840,70,960,60,1080,50C1200,40,1320,30,1380,25L1440,20L1440,100L0,100Z" fill="#f9fafb"/>
            </svg>
            
            <!-- Content -->
            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-24 pb-20 sm:pb-24">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-1.5 text-[9px] sm:text-[10px] text-white/60 mb-4 sm:mb-5 font-medium">
                    <Link href="/" class="hover:text-white transition-colors">Beranda</Link>
                    <ChevronRight class="w-3 h-3" />
                    <span class="text-white/90">Explore Map</span>
                </nav>
                
                <!-- Badge -->
                <div class="hero-badge inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-white/10 backdrop-blur-xl border border-white/15 text-white/90 mb-4 sm:mb-5">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-cyan-500"></span>
                    </span>
                    <Globe2 class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-cyan-400" />
                    <span class="text-[9px] sm:text-[10px] font-semibold tracking-wide">Peta Interaktif Satelit</span>
                </div>
                
                <!-- Title -->
                <h1 class="hero-title text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-black text-white leading-tight mb-3 sm:mb-4">
                    Jelajahi Destinasi 
                    <span class="bg-gradient-to-r from-teal-300 via-cyan-300 to-emerald-300 bg-clip-text text-transparent">Taman Nasional 🗺️</span>
                </h1>
                
                <!-- Description -->
                <p class="hero-desc text-white/70 text-[11px] sm:text-xs md:text-sm max-w-xl mb-5 sm:mb-6 leading-relaxed">
                    Temukan lokasi wisata melalui peta satelit interaktif. Lihat detail destinasi, fasilitas, dan dapatkan petunjuk arah langsung.
                </p>
                
                <!-- Stats -->
                <div class="hero-stats flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-white/10 backdrop-blur border border-white/15 text-white">
                        <MapPin class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-teal-400" />
                        <span class="text-[10px] sm:text-xs font-bold">{{ destinations.length }}</span>
                        <span class="text-[9px] sm:text-[10px] text-white/70">Destinasi</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-white/10 backdrop-blur border border-white/15 text-white">
                        <Layers class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-cyan-400" />
                        <span class="text-[10px] sm:text-xs font-bold">{{ categories.length }}</span>
                        <span class="text-[9px] sm:text-[10px] text-white/70">Kategori</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-teal-500/20 to-cyan-500/20 backdrop-blur border border-teal-400/20 text-white">
                        <Compass class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-teal-300" />
                        <span class="text-[9px] sm:text-[10px] font-medium">Tampilan Satelit</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="content-section py-6 sm:py-8 bg-gray-50 min-h-[60vh]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
                    
                    <!-- Sidebar - Destinations List -->
                    <div class="lg:col-span-4 xl:col-span-3 space-y-3 sm:space-y-4">
                        
                        <!-- Search Box -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search class="w-4 h-4 text-gray-400" />
                            </div>
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Cari destinasi..."
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 text-xs sm:text-sm bg-white rounded-xl border border-gray-200 focus:ring-2 focus:ring-teal-500 focus:border-transparent shadow-sm transition-all"
                            >
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="flex flex-wrap gap-1.5 sm:gap-2">
                            <button 
                                @click="selectedCategory = null"
                                :class="[
                                    'px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-medium transition-all',
                                    !selectedCategory 
                                        ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/30' 
                                        : 'bg-white text-gray-600 hover:bg-teal-50 border border-gray-200'
                                ]"
                            >
                                Semua
                            </button>
                            <button 
                                v-for="cat in categories" 
                                :key="cat.id"
                                @click="selectedCategory = cat.id"
                                :class="[
                                    'px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-medium transition-all flex items-center gap-1',
                                    selectedCategory === cat.id 
                                        ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/30' 
                                        : 'bg-white text-gray-600 hover:bg-teal-50 border border-gray-200'
                                ]"
                            >
                                <span>{{ cat.icon }}</span>
                                <span>{{ cat.name }}</span>
                            </button>
                        </div>
                        
                        <!-- Results Count -->
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] sm:text-xs text-gray-500">
                                {{ filteredDestinations.length }} dari {{ destinations.length }} destinasi
                            </span>
                            <button 
                                v-if="searchQuery || selectedCategory"
                                @click="clearFilters"
                                class="text-[10px] sm:text-xs text-teal-600 hover:text-teal-700 font-medium"
                            >
                                Reset Filter
                            </button>
                        </div>
                        
                        <!-- Destinations List -->
                        <div class="space-y-2 sm:space-y-3 max-h-[400px] sm:max-h-[500px] lg:max-h-[calc(100vh-400px)] overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                            <button 
                                v-for="dest in filteredDestinations" 
                                :key="dest.id"
                                @click="selectDestination(dest)"
                                class="dest-card w-full text-left group"
                            >
                                <div :class="[
                                    'p-3 sm:p-4 rounded-xl transition-all duration-300',
                                    selectedDestination?.id === dest.id 
                                        ? 'bg-gradient-to-r from-teal-500 to-cyan-600 text-white shadow-xl shadow-teal-500/30 scale-[1.02]' 
                                        : 'bg-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 border border-gray-100'
                                ]">
                                    <div class="flex items-center gap-2.5 sm:gap-3">
                                        <div class="relative">
                                            <img 
                                                :src="dest.image || '/images/placeholder-no-image.svg'" 
                                                :alt="dest.name" 
                                                class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg object-cover ring-2 ring-white/20"
                                            >
                                            <div :class="[
                                                'absolute -top-1 -right-1 w-5 h-5 sm:w-6 sm:h-6 rounded-full flex items-center justify-center text-[10px]',
                                                selectedDestination?.id === dest.id ? 'bg-white/20' : 'bg-gray-100'
                                            ]">
                                                {{ dest.icon }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 :class="['font-bold text-xs sm:text-sm truncate', selectedDestination?.id === dest.id ? 'text-white' : 'text-gray-900']">
                                                {{ dest.name }}
                                            </h4>
                                            <div class="flex items-center gap-1.5 mt-0.5 sm:mt-1">
                                                <span :class="['text-[9px] sm:text-[10px] truncate', selectedDestination?.id === dest.id ? 'text-white/70' : 'text-gray-500']">
                                                    {{ dest.category }}
                                                </span>
                                                <span :class="['text-[8px]', selectedDestination?.id === dest.id ? 'text-white/40' : 'text-gray-300']">•</span>
                                                <div class="flex items-center gap-0.5">
                                                    <Star :class="['w-2.5 h-2.5 sm:w-3 sm:h-3', selectedDestination?.id === dest.id ? 'text-yellow-300 fill-yellow-300' : 'text-yellow-400 fill-yellow-400']" />
                                                    <span :class="['text-[9px] sm:text-[10px] font-medium', selectedDestination?.id === dest.id ? 'text-white' : 'text-gray-600']">
                                                        {{ (parseFloat(dest.rating) || 0).toFixed(1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 mt-1">
                                                <MapPin :class="['w-2.5 h-2.5', selectedDestination?.id === dest.id ? 'text-white/60' : 'text-gray-400']" />
                                                <span :class="['text-[8px] sm:text-[9px] truncate', selectedDestination?.id === dest.id ? 'text-white/60' : 'text-gray-400']">
                                                    {{ dest.city || 'Sulawesi Tengah' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div :class="[
                                            'text-[10px] sm:text-xs font-bold px-2 py-1 rounded-lg',
                                            selectedDestination?.id === dest.id ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-600'
                                        ]">
                                            {{ dest.price }}
                                        </div>
                                    </div>
                                </div>
                            </button>
                            
                            <!-- Empty State -->
                            <div v-if="filteredDestinations.length === 0" class="text-center py-8 sm:py-12">
                                <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                                    <Search class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400" />
                                </div>
                                <p class="text-xs sm:text-sm text-gray-500">Tidak ada destinasi ditemukan</p>
                                <button @click="clearFilters" class="mt-2 text-[10px] sm:text-xs text-teal-600 hover:text-teal-700 font-medium">
                                    Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div class="lg:col-span-8 xl:col-span-9">
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 h-[400px] sm:h-[500px] lg:h-[calc(100vh-280px)]">
                            <!-- Map Controls -->
                            <div class="absolute top-4 right-4 z-20 flex flex-col gap-2">
                                <div class="bg-white/90 backdrop-blur-xl rounded-xl shadow-lg p-1.5 border border-gray-200/50">
                                    <div class="flex items-center gap-1 px-2 py-1">
                                        <Globe2 class="w-3 h-3 text-teal-500" />
                                        <span class="text-[9px] sm:text-[10px] font-medium text-gray-600">Satelit</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Embedded Map -->
                            <div class="relative h-full">
                                <iframe 
                                    :src="getMapEmbedUrl"
                                    class="w-full h-full border-0"
                                    allowfullscreen
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                                
                                <!-- Selected Destination Overlay -->
                                <div 
                                    v-if="selectedDestination"
                                    class="dest-overlay absolute bottom-3 sm:bottom-4 left-3 sm:left-4 right-3 sm:right-4 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl p-4 sm:p-5 border border-gray-100"
                                >
                                    <button @click="closeDetails" class="absolute top-3 sm:top-4 right-3 sm:right-4 p-1.5 sm:p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors group">
                                        <X class="w-3 h-3 sm:w-4 sm:h-4 text-gray-600 group-hover:rotate-90 transition-transform" />
                                    </button>
                                    
                                    <div class="flex gap-3 sm:gap-4">
                                        <div class="relative">
                                            <img 
                                                :src="selectedDestination.image || '/images/placeholder-no-image.svg'" 
                                                :alt="selectedDestination.name" 
                                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover ring-2 ring-teal-500/20"
                                            >
                                            <div class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-gradient-to-r from-teal-500 to-cyan-600 flex items-center justify-center text-xs shadow-lg">
                                                {{ selectedDestination.icon }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] font-bold text-teal-600 uppercase tracking-wide">
                                                {{ selectedDestination.category }}
                                            </span>
                                            <h3 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900 mb-1 truncate">{{ selectedDestination.name }}</h3>
                                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-3">
                                                <div class="flex items-center gap-1">
                                                    <MapPin class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-gray-400" />
                                                    <span class="text-[10px] sm:text-xs text-gray-600">{{ selectedDestination.city || 'Sulawesi Tengah' }}</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <Star class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-yellow-400 fill-yellow-400" />
                                                    <span class="text-[10px] sm:text-xs font-medium text-gray-700">{{ (parseFloat(selectedDestination.rating) || 0).toFixed(1) }}</span>
                                                </div>
                                                <span class="text-[10px] sm:text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-lg">{{ selectedDestination.price }}</span>
                                            </div>
                                            <p v-if="selectedDestination.short_description" class="text-[10px] sm:text-xs text-gray-500 line-clamp-2 mb-3 hidden sm:block">
                                                {{ selectedDestination.short_description }}
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                <Link 
                                                    :href="`/destinations/${selectedDestination.slug}`" 
                                                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 bg-gradient-to-r from-teal-500 to-cyan-600 text-white font-bold rounded-xl text-[10px] sm:text-xs hover:shadow-lg hover:shadow-teal-500/30 hover:-translate-y-0.5 transition-all"
                                                >
                                                    <ExternalLink class="w-3 h-3 sm:w-3.5 sm:h-3.5" />Lihat Detail
                                                </Link>
                                                <a 
                                                    :href="`https://www.google.com/maps/dir/?api=1&destination=${selectedDestination.lat},${selectedDestination.lng}`" 
                                                    target="_blank" 
                                                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-100 text-gray-700 font-bold rounded-xl text-[10px] sm:text-xs hover:bg-gray-200 transition-all"
                                                >
                                                    <Navigation class="w-3 h-3 sm:w-3.5 sm:h-3.5" />Petunjuk Arah
                                                </a>
                                                <Link 
                                                    :href="`/book/${selectedDestination.slug}`" 
                                                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold rounded-xl text-[10px] sm:text-xs hover:shadow-lg hover:shadow-amber-500/30 hover:-translate-y-0.5 transition-all"
                                                >
                                                    <Ticket class="w-3 h-3 sm:w-3.5 sm:h-3.5" />Pesan Tiket
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hint when no destination selected -->
                                <div 
                                    v-if="!selectedDestination && destinations.length > 0"
                                    class="absolute bottom-3 sm:bottom-4 left-3 sm:left-4 right-3 sm:right-4 bg-white/90 backdrop-blur-xl rounded-xl p-3 sm:p-4 shadow-lg border border-gray-100 text-center"
                                >
                                    <div class="flex items-center justify-center gap-2 text-gray-500">
                                        <Map class="w-4 h-4 text-teal-500" />
                                        <span class="text-[10px] sm:text-xs">Pilih destinasi dari daftar untuk melihat lokasi di peta satelit</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Smooth scrollbar */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Animations */
@keyframes pulse-slow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.05); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(12deg); }
    50% { transform: translateY(-12px) rotate(12deg); }
}
.animate-float { animation: float 5s ease-in-out infinite; }

@keyframes float-delayed {
    0%, 100% { transform: translateY(0) rotate(45deg); }
    50% { transform: translateY(-10px) rotate(45deg); }
}
.animate-float-delayed { animation: float-delayed 6s ease-in-out infinite 1s; }

/* Line clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
