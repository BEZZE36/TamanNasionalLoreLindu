<script setup>
/**
 * HeroBanner.vue - Premium Dashboard Hero Section
 * Matches Destination/FAQ Hero Design with GSAP animations
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { LayoutGrid, Ticket, CheckCircle, Clock } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

const { props } = usePage();
const user = computed(() => props.auth?.user);

// Stats from props
const stats = computed(() => props.stats || {
    totalBookings: 0,
    activeTickets: 0,
    pendingPayments: 0
});

// Refs
const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const counters = ref({ bookings: 0, active: 0, pending: 0 });
let ctx;

// Get greeting based on time
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

// Smooth scroll to content
const scrollToContent = () => {
    gsap.to(window, { duration: 1, scrollTo: { y: '#dashboard-content', offsetY: 80 }, ease: 'power3.inOut' });
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
            bookings: stats.value.totalBookings,
            active: stats.value.activeTickets,
            pending: stats.value.pendingPayments,
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
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="heroRef" class="relative min-h-[75vh] sm:min-h-[80vh] flex items-center justify-center overflow-hidden">
        <!-- Background with gradient and effects - FULL WIDTH -->
        <div ref="backgroundRef" class="absolute inset-0 -mx-[100vw] w-[200vw] left-1/2 -translate-x-1/2">
            <!-- Dark gradient base -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900"></div>
            
            <!-- Animated Mesh Gradient -->
            <div class="absolute inset-0 opacity-60">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(16,185,129,0.15),transparent_50%)]"></div>
                <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(6,182,212,0.12),transparent_50%)]"></div>
                <div class="absolute bottom-0 left-1/2 w-full h-full bg-[radial-gradient(ellipse_at_50%_100%,rgba(59,130,246,0.08),transparent_50%)]"></div>
            </div>

            <!-- Floating Geometric Shapes -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="float-shape absolute top-[15%] left-[8%] w-16 h-16 sm:w-20 sm:h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                <div class="float-shape absolute top-[25%] right-[12%] w-10 h-10 sm:w-14 sm:h-14 border border-emerald-500/10 rounded-full"></div>
                <div class="float-shape absolute top-[55%] left-[15%] w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-cyan-500/5 to-teal-500/5 rounded-lg rotate-45"></div>
                <div class="float-shape absolute bottom-[25%] right-[8%] w-12 h-12 sm:w-16 sm:h-16 border border-cyan-500/[0.08] rounded-xl -rotate-12"></div>
                <div class="float-shape absolute top-[40%] left-[50%] w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-emerald-500/5 to-transparent rounded-full"></div>
            </div>

            <!-- Subtle Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

            <!-- Premium Wave SVG -->
            <svg class="wave-layer absolute -bottom-1 left-0 right-0 w-full h-32 sm:h-40 md:h-44" viewBox="0 0 1440 200" fill="none" preserveAspectRatio="none">
                <path d="M0,120L60,110C120,100,240,80,360,85C480,90,600,120,720,130C840,140,960,130,1080,115C1200,100,1320,80,1380,70L1440,60L1440,200L0,200Z" fill="rgba(255,255,255,0.02)"/>
                <path d="M0,140L48,135C96,130,192,120,288,115C384,110,480,110,576,120C672,130,768,150,864,155C960,160,1056,150,1152,135C1248,120,1344,100,1392,90L1440,80L1440,200L0,200Z" fill="rgba(16,185,129,0.03)"/>
                <path d="M0,160L60,155C120,150,240,140,360,145C480,150,600,170,720,175C840,180,960,170,1080,160C1200,150,1320,140,1380,135L1440,130L1440,200L0,200Z" fill="#f9fafb"/>
            </svg>
        </div>

        <!-- Hero Content -->
        <div ref="contentRef" class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-24">
            <div class="text-center max-w-3xl mx-auto">
                <!-- Badge with blinking dot -->
                <div class="hero-item inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-3 sm:mb-4 hover:bg-white/15 hover:border-white/30 hover:scale-105 transition-all duration-300 cursor-default">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[9px] sm:text-[10px] font-semibold tracking-wide">Dashboard Pengguna</span>
                    <LayoutGrid class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-400" />
                </div>

                <!-- Greeting & Title -->
                <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black text-white leading-tight mb-1 sm:mb-2">
                    {{ greeting }},
                </h1>
                <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-300 bg-clip-text text-transparent mb-3 sm:mb-4">
                    {{ user?.name }}! 👋
                </h1>

                <!-- Description -->
                <p class="hero-item text-white/60 text-[11px] sm:text-xs md:text-sm max-w-xl mx-auto mb-5 sm:mb-6 leading-relaxed px-2 sm:px-0">
                    Kelola tiket dan jelajahi keindahan 
                    <span class="text-emerald-400 font-semibold">Taman Nasional Lore Lindu</span> 
                    langsung dari dashboard Anda
                </p>

                <!-- Stats Cards -->
                <div class="hero-item grid grid-cols-3 gap-1.5 sm:gap-2 md:gap-3 max-w-md sm:max-w-lg mx-auto mb-8 sm:mb-10 px-2 sm:px-0">
                    <!-- Total Bookings -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-emerald-500/15 hover:border-emerald-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 cursor-default">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500/30 transition-all duration-300">
                            <Ticket class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-300" />
                        </div>
                        <p class="text-sm sm:text-base lg:text-lg font-black text-white group-hover:text-emerald-200 transition-colors">{{ Math.round(counters.bookings) }}</p>
                        <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Total Pesanan</p>
                    </div>

                    <!-- Active Tickets -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-teal-500/15 hover:border-teal-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-teal-500/10 transition-all duration-300 cursor-default">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-teal-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-500/30 transition-all duration-300">
                            <CheckCircle class="w-3 h-3 sm:w-4 sm:h-4 text-teal-300" />
                        </div>
                        <p class="text-sm sm:text-base lg:text-lg font-black text-white group-hover:text-teal-200 transition-colors">{{ Math.round(counters.active) }}</p>
                        <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Tiket Aktif</p>
                    </div>

                    <!-- Pending -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-amber-500/15 hover:border-amber-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-default">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-amber-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/30 transition-all duration-300">
                            <Clock class="w-3 h-3 sm:w-4 sm:h-4 text-amber-300" />
                        </div>
                        <p class="text-sm sm:text-base lg:text-lg font-black text-white group-hover:text-amber-200 transition-colors">{{ Math.round(counters.pending) }}</p>
                        <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Menunggu</p>
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
</template>

<style scoped>
@keyframes scroll-pulse {
    0%, 100% { opacity: 1; transform: translateY(0); }
    50% { opacity: 0.3; transform: translateY(4px); }
}
.animate-scroll-pulse { animation: scroll-pulse 1.5s ease-in-out infinite; }

/* Performance optimizations */
.hero-item, .float-shape {
    will-change: transform, opacity;
}
</style>
