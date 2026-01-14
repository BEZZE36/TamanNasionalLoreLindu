<script setup>
/**
 * GalleryHero.vue - Premium Hero with GSAP Animations
 * Design: Follows DetailHero pattern with glassmorphism and premium aesthetics
 * Sizing: text-xs CTA, text-[11px] info, text-[10px] badges
 */
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Camera, Images, Video, ChevronRight, Sparkles, Eye, Heart, TrendingUp } from 'lucide-vue-next';

const props = defineProps({
    totalGalleries: { type: Number, default: 0 },
    totalPhotos: { type: Number, default: 0 },
    totalVideos: { type: Number, default: 0 },
    totalViews: { type: Number, default: 0 },
    totalLikes: { type: Number, default: 0 }
});

const heroRef = ref(null);
let ctx;

const formatCount = (num) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num?.toString() || '0';
};

onMounted(() => {
    ctx = gsap.context(() => {
        const tl = gsap.timeline({ delay: 0.2 });
        
        // Breadcrumb
        tl.fromTo('.hero-breadcrumb', 
            { opacity: 0, y: -15 }, 
            { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }
        );
        
        // Badge
        tl.fromTo('.hero-badge', 
            { opacity: 0, scale: 0.7, y: 10 }, 
            { opacity: 1, scale: 1, y: 0, duration: 0.4, ease: 'back.out(3)' }, 
            '-=0.2'
        );
        
        // Title with blur reveal
        tl.fromTo('.hero-title', 
            { opacity: 0, y: 25, filter: 'blur(8px)' }, 
            { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.5, ease: 'power3.out' }, 
            '-=0.2'
        );
        
        // Description
        tl.fromTo('.hero-desc', 
            { opacity: 0, y: 15 }, 
            { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }, 
            '-=0.2'
        );
        
        // Stats
        tl.fromTo('.hero-stat', 
            { opacity: 0, scale: 0.8, y: 15 }, 
            { opacity: 1, scale: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'back.out(2)' }, 
            '-=0.1'
        );
    }, heroRef.value);
});

onBeforeUnmount(() => { 
    if (ctx) ctx.revert(); 
});
</script>

<template>
    <section ref="heroRef" class="relative min-h-[45vh] sm:min-h-[50vh] overflow-hidden">
        <!-- Premium Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-violet-600 via-purple-700 to-indigo-900"></div>
        
        <!-- Animated Decorative Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-1/4 right-[5%] w-48 sm:w-64 lg:w-80 h-48 sm:h-64 lg:h-80 bg-gradient-to-br from-violet-400/20 to-purple-400/10 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-1/4 left-[5%] w-44 sm:w-56 lg:w-72 h-44 sm:h-56 lg:h-72 bg-gradient-to-tl from-indigo-400/15 to-blue-400/5 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
            
            <!-- Floating geometric shapes -->
            <div class="absolute top-[15%] right-[15%] w-16 h-16 border border-white/5 rounded-full animate-float"></div>
            <div class="absolute bottom-[30%] left-[10%] w-12 h-12 border border-violet-400/10 rounded-xl rotate-12 animate-float-delayed"></div>
            <div class="absolute top-[60%] right-[8%] w-8 h-8 bg-white/5 rounded-lg rotate-45 animate-float"></div>
        </div>

        <!-- Wave Transition -->
        <svg class="absolute bottom-0 left-0 right-0 w-full h-16 sm:h-20 lg:h-24" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
            <path d="M0,50L60,45C120,40,240,30,360,35C480,40,600,60,720,65C840,70,960,60,1080,50C1200,40,1320,30,1380,25L1440,20L1440,100L0,100Z" fill="white"/>
        </svg>

        <!-- Content -->
        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 sm:pt-28 pb-20 sm:pb-24">
            <!-- Breadcrumb -->
            <nav class="hero-breadcrumb flex items-center gap-1.5 text-[10px] sm:text-[11px] text-white/60 mb-4 sm:mb-5 font-medium">
                <Link href="/" class="hover:text-white transition-colors">Beranda</Link>
                <ChevronRight class="w-3 h-3" />
                <span class="text-white/90">Galeri</span>
            </nav>

            <div class="max-w-2xl">
                <!-- Badge -->
                <div class="hero-badge inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white mb-3 sm:mb-4 shadow-lg">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-violet-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-violet-400"></span>
                    </span>
                    <Camera class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Galeri Media TNLL</span>
                </div>

                <!-- Title -->
                <h1 class="hero-title text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-3 sm:mb-4">
                    Galeri Media
                    <span class="block mt-1 bg-gradient-to-r from-violet-200 via-purple-200 to-indigo-200 bg-clip-text text-transparent">
                        📸 Taman Nasional
                    </span>
                </h1>

                <!-- Description -->
                <p class="hero-desc text-white/70 text-xs sm:text-sm max-w-lg mb-5 sm:mb-6 leading-relaxed">
                    Jelajahi koleksi foto dan video menakjubkan dari keindahan alam Taman Nasional Lore Lindu. Temukan momen-momen indah yang diabadikan pengunjung.
                </p>

                <!-- Stats -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="hero-stat inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/95 text-gray-900 shadow-lg">
                        <Images class="w-3.5 h-3.5 text-violet-500" />
                        <span class="text-[11px] sm:text-xs font-bold">{{ formatCount(totalGalleries) }}</span>
                        <span class="text-[9px] sm:text-[10px] text-gray-500">Galeri</span>
                    </div>
                    
                    <div class="hero-stat inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white">
                        <Camera class="w-3.5 h-3.5 text-cyan-300" />
                        <span class="text-[11px] sm:text-xs font-bold">{{ formatCount(totalPhotos) }}</span>
                        <span class="text-[9px] sm:text-[10px] text-white/60">Foto</span>
                    </div>
                    
                    <div class="hero-stat inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white">
                        <Video class="w-3.5 h-3.5 text-pink-300" />
                        <span class="text-[11px] sm:text-xs font-bold">{{ formatCount(totalVideos) }}</span>
                        <span class="text-[9px] sm:text-[10px] text-white/60">Video</span>
                    </div>

                    <div class="hero-stat inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white">
                        <Eye class="w-3.5 h-3.5 text-emerald-300" />
                        <span class="text-[11px] sm:text-xs font-bold">{{ formatCount(totalViews) }}</span>
                        <span class="text-[9px] sm:text-[10px] text-white/60">Dilihat</span>
                    </div>

                    <div class="hero-stat inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg shadow-red-500/25">
                        <Heart class="w-3.5 h-3.5 fill-current" />
                        <span class="text-[11px] sm:text-xs font-bold">{{ formatCount(totalLikes) }}</span>
                        <span class="text-[9px] sm:text-[10px] text-white/80">Disukai</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.05); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-12px); }
}
.animate-float { animation: float 6s ease-in-out infinite; }
.animate-float-delayed { animation: float 6s ease-in-out infinite 2s; }
</style>
