<script setup>
/**
 * BlogHero.vue - Premium Hero matching Destination/Tiket Saya Design
 * Features: Dark gradient, floating shapes, wave SVG, GSAP animations, stats cards
 */
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { Newspaper, Eye, Heart, MessageCircle, ChevronDown, Sparkles } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

const props = defineProps({
    totalArticles: { type: Number, default: 0 },
    totalViews: { type: Number, default: 0 },
    totalComments: { type: Number, default: 0 }
});

const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const counters = ref({ articles: 0, views: 0, comments: 0 });
let ctx;

const scrollToContent = () => {
    gsap.to(window, { duration: 1, scrollTo: { y: '#blog-content', offsetY: 80 }, ease: 'power3.inOut' });
};

onMounted(() => {
    ctx = gsap.context(() => {
        const tl = gsap.timeline({ delay: 0.3 });
        
        tl.fromTo('.hero-item', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );

        // Counter animations
        gsap.to(counters.value, {
            articles: props.totalArticles,
            views: props.totalViews,
            comments: props.totalComments,
            duration: 2,
            ease: 'power2.out',
            delay: 0.8
        });

        // Floating animation for geometric shapes
        gsap.to('.float-shape', {
            y: -15,
            duration: 3,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: { each: 0.5, from: 'random' }
        });

        // Parallax wave on scroll
        gsap.to('.wave-layer', {
            yPercent: 20,
            ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: 1.5 }
        });

        // Parallax effect for background
        gsap.to(backgroundRef.value, {
            yPercent: 30,
            ease: 'none',
            scrollTrigger: {
                trigger: heroRef.value,
                start: 'top top',
                end: 'bottom top',
                scrub: true
            }
        });

        // Parallax effect for content
        gsap.to(contentRef.value, {
            yPercent: -20,
            opacity: 0,
            ease: 'none',
            scrollTrigger: {
                trigger: heroRef.value,
                start: 'top top',
                end: 'bottom top',
                scrub: true
            }
        });

        // Scale down hero section
        gsap.to(heroRef.value, {
            scale: 0.95,
            ease: 'none',
            scrollTrigger: {
                trigger: heroRef.value,
                start: 'top top',
                end: 'bottom top',
                scrub: true
            }
        });
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="heroRef" class="relative min-h-[70vh] flex items-center justify-center overflow-hidden pt-16 pb-12">
        <!-- Premium Gradient Background -->
        <div ref="backgroundRef" class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-cyan-950 to-slate-900"></div>
            
            <!-- Animated Mesh Gradient -->
            <div class="absolute inset-0 opacity-60">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(6,182,212,0.15),transparent_50%)]"></div>
                <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(59,130,246,0.12),transparent_50%)]"></div>
                <div class="absolute bottom-0 left-1/2 w-full h-full bg-[radial-gradient(ellipse_at_50%_100%,rgba(99,102,241,0.08),transparent_50%)]"></div>
            </div>

            <!-- Floating Geometric Shapes -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="float-shape absolute top-[15%] left-[8%] w-20 h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                <div class="float-shape absolute top-[25%] right-[12%] w-14 h-14 border border-cyan-500/10 rounded-full"></div>
                <div class="float-shape absolute top-[55%] left-[15%] w-10 h-10 bg-gradient-to-br from-blue-500/5 to-cyan-500/5 rounded-lg rotate-45"></div>
                <div class="float-shape absolute bottom-[25%] right-[8%] w-16 h-16 border border-blue-500/[0.08] rounded-xl -rotate-12"></div>
                <div class="float-shape absolute top-[40%] left-[60%] w-12 h-12 border border-cyan-500/10 rounded-lg rotate-6"></div>
                <div class="float-shape absolute bottom-[40%] left-[25%] w-8 h-8 bg-gradient-to-br from-cyan-500/5 to-blue-500/5 rounded-full"></div>
            </div>

            <!-- Subtle Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

            <!-- Premium Wave SVG -->
            <svg class="wave-layer absolute -bottom-1 left-0 right-0 w-full h-44" viewBox="0 0 1440 200" fill="none" preserveAspectRatio="none">
                <path d="M0,120L60,110C120,100,240,80,360,85C480,90,600,120,720,130C840,140,960,130,1080,115C1200,100,1320,80,1380,70L1440,60L1440,200L0,200Z" fill="rgba(255,255,255,0.02)"/>
                <path d="M0,140L48,135C96,130,192,120,288,115C384,110,480,110,576,120C672,130,768,150,864,155C960,160,1056,150,1152,135C1248,120,1344,100,1392,90L1440,80L1440,200L0,200Z" fill="rgba(6,182,212,0.03)"/>
                <path d="M0,160L60,155C120,150,240,140,360,145C480,150,600,170,720,175C840,180,960,170,1080,160C1200,150,1320,140,1380,135L1440,130L1440,200L0,200Z" fill="#f9fafb"/>
            </svg>
        </div>

        <!-- Content -->
        <div ref="contentRef" class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <!-- Badge -->
                <div class="hero-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-4 hover:bg-white/15 hover:border-white/30 hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                    </span>
                    <span class="text-[10px] font-semibold tracking-wide">Baca Artikel Terbaru</span>
                    <Newspaper class="w-3 h-3 text-cyan-400" />
                </div>

                <!-- Title -->
                <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-2">
                    Blog &
                </h1>
                <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black bg-gradient-to-r from-cyan-300 via-blue-200 to-indigo-300 bg-clip-text text-transparent mb-4">
                    Artikel
                </h1>

                <!-- Description -->
                <p class="hero-item text-xs sm:text-sm text-white/60 max-w-xl mx-auto mb-8 leading-relaxed">
                    Temukan cerita, tips, dan informasi menarik seputar Taman Nasional Lore Lindu. 
                    Eksplorasi keindahan alam dan kekayaan budaya Sulawesi Tengah.
                </p>

                <!-- Stats Cards -->
                <div class="hero-item grid grid-cols-3 gap-3 max-w-md mx-auto mb-8">
                    <!-- Total Artikel -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-cyan-500/15 hover:border-cyan-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 cursor-default">
                        <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-cyan-500/30 transition-all duration-300">
                            <Newspaper class="w-4 h-4 text-cyan-300" />
                        </div>
                        <p class="text-lg sm:text-xl font-black text-white group-hover:text-cyan-200 transition-colors">{{ Math.round(counters.articles) }}</p>
                        <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Artikel</p>
                    </div>

                    <!-- Total Views -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-blue-500/15 hover:border-blue-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 cursor-default">
                        <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-blue-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-500/30 transition-all duration-300">
                            <Eye class="w-4 h-4 text-blue-300" />
                        </div>
                        <p class="text-lg sm:text-xl font-black text-white group-hover:text-blue-200 transition-colors">{{ Math.round(counters.views) }}+</p>
                        <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Views</p>
                    </div>

                    <!-- Total Comments -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-indigo-500/15 hover:border-indigo-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-500/10 transition-all duration-300 cursor-default">
                        <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-indigo-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-500/30 transition-all duration-300">
                            <MessageCircle class="w-4 h-4 text-indigo-300" />
                        </div>
                        <p class="text-lg sm:text-xl font-black text-white group-hover:text-indigo-200 transition-colors">{{ Math.round(counters.comments) }}</p>
                        <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Komentar</p>
                    </div>
                </div>

                <!-- Scroll Hint -->
                <button @click="scrollToContent" class="hero-item flex flex-col items-center gap-1.5 group cursor-pointer mx-auto">
                    <span class="text-[9px] text-white/30 uppercase tracking-[0.15em] font-medium group-hover:text-white/50 transition-colors">Scroll</span>
                    <div class="relative w-5 h-8 rounded-full border border-white/20 group-hover:border-white/40 flex justify-center pt-1.5 transition-colors">
                        <div class="w-1 h-2 bg-white/40 group-hover:bg-white/60 rounded-full animate-scroll-pulse transition-colors"></div>
                    </div>
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes scroll-pulse {
    0%, 100% { opacity: 1; transform: translateY(0); }
    50% { opacity: 0.3; transform: translateY(6px); }
}
.animate-scroll-pulse { animation: scroll-pulse 1.5s ease-in-out infinite; }
</style>
