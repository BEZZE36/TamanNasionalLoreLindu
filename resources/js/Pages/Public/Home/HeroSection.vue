<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Link } from '@inertiajs/vue3';
import { MapPin, Trees, Compass, Bird, Users, Mountain, Leaf, Shield } from 'lucide-vue-next';

// Import Hero Background Partial
import HeroBackground from './Partials/HeroBackground.vue';

gsap.registerPlugin(ScrollTrigger);

defineProps({
    stats: Object,
});

const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
let ctx;

const scrollToContent = () => {
    const nextSection = document.querySelector('#video-info-section');
    if (nextSection) {
        gsap.to(window, {
            duration: 1.2,
            scrollTo: { y: nextSection, offsetY: 0 },
            ease: 'power3.inOut'
        });
    } else {
        gsap.to(window, {
            duration: 1.2,
            scrollTo: { y: window.innerHeight, autoKill: false },
            ease: 'power3.inOut'
        });
    }
};

onMounted(() => {
    import('gsap/ScrollToPlugin').then(({ ScrollToPlugin }) => {
        gsap.registerPlugin(ScrollToPlugin);
    });

    ctx = gsap.context(() => {
        // Hero content entrance animation
        gsap.fromTo('.hero-item',
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.08, ease: 'power2.out', delay: 0.2 }
        );

        // Parallax effect for background - moves slower (0.3x speed)
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

        // Parallax effect for content - moves faster and fades out
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

        // Scale down hero section slightly as you scroll
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

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <section 
        ref="heroRef"
        class="relative h-screen flex items-center justify-center overflow-hidden"
    >
        <!-- Hero Background Partial with Decorative Elements -->
        <div ref="backgroundRef" class="absolute inset-0">
            <HeroBackground />
        </div>

        <!-- Main Content -->
        <div ref="contentRef" class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                
                <!-- Badge with green blinking dot - Responsive -->
                <div class="hero-item inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-3 sm:mb-4 hover:bg-white/15 hover:border-white/30 hover:scale-105 transition-all duration-300 cursor-default">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[9px] sm:text-[10px] font-semibold tracking-wide">Selamat Datang di TNLL Explore</span>
                </div>

                <!-- Title with hover effect - Responsive -->
                <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black text-white leading-tight mb-2 sm:mb-3 hover:scale-[1.02] transition-transform duration-300">
                    Jelajahi Keindahan
                    <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-emerald-300 via-teal-300 to-cyan-300 bg-clip-text text-transparent bg-[length:200%_200%] animate-gradient-shift">
                        Taman Nasional Lore Lindu
                    </span>
                </h1>

                <!-- Description with hover - Responsive -->
                <p class="hero-item text-white/80 text-[11px] sm:text-xs md:text-sm max-w-xl sm:max-w-2xl mx-auto mb-4 sm:mb-5 leading-relaxed hover:text-white/90 transition-colors duration-300 px-2 sm:px-0">
                    Taman Nasional Lore Lindu merupakan cagar biosfer UNESCO yang terletak di Sulawesi Tengah dengan luas 217.991,18 hektar. 
                    Kawasan ini menjadi habitat bagi satwa endemik langka seperti Anoa, Babirusa, dan Maleo, serta menyimpan 
                    peninggalan megalitik misterius berupa patung-patung batu kuno yang tersebar di Lembah Bada, Besoa, dan Napu.
                </p>

                <!-- Feature highlights with hover - Responsive -->
                <div class="hero-item flex flex-wrap justify-center gap-1.5 sm:gap-2 mb-4 sm:mb-5 px-2 sm:px-0">
                    <div class="inline-flex items-center gap-0.5 sm:gap-1 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-white/10 backdrop-blur border border-white/15 text-white/80 hover:bg-emerald-500/20 hover:border-emerald-400/30 hover:text-white hover:scale-105 transition-all duration-300 cursor-default">
                        <Shield class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-300" />
                        <span class="text-[8px] sm:text-[9px] font-medium">UNESCO Biosphere Reserve</span>
                    </div>
                    <div class="inline-flex items-center gap-0.5 sm:gap-1 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-white/10 backdrop-blur border border-white/15 text-white/80 hover:bg-teal-500/20 hover:border-teal-400/30 hover:text-white hover:scale-105 transition-all duration-300 cursor-default">
                        <Mountain class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-teal-300" />
                        <span class="text-[8px] sm:text-[9px] font-medium">217.991 Hektar</span>
                    </div>
                    <div class="inline-flex items-center gap-0.5 sm:gap-1 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-white/10 backdrop-blur border border-white/15 text-white/80 hover:bg-cyan-500/20 hover:border-cyan-400/30 hover:text-white hover:scale-105 transition-all duration-300 cursor-default">
                        <Leaf class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-cyan-300" />
                        <span class="text-[8px] sm:text-[9px] font-medium">Zona Wallacea</span>
                    </div>
                </div>

                <!-- CTA Buttons with enhanced hover - Responsive -->
                <div class="hero-item flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-3 mb-5 sm:mb-6">
                    <Link
                        href="/destinations"
                        class="group inline-flex items-center gap-1 sm:gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium text-[11px] sm:text-xs rounded-full shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-1 hover:scale-105 active:scale-100 transition-all duration-300"
                    >
                        <Compass class="w-3 h-3 sm:w-3.5 sm:h-3.5 group-hover:rotate-45 transition-transform duration-300" />
                        <span>Jelajahi Destinasi</span>
                    </Link>
                    <Link
                        href="/about"
                        class="group inline-flex items-center gap-1 sm:gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 bg-white/10 backdrop-blur-md border border-white/25 text-white font-medium text-[11px] sm:text-xs rounded-full hover:bg-white/20 hover:border-white/40 hover:-translate-y-1 hover:scale-105 active:scale-100 transition-all duration-300"
                    >
                        <Users class="w-3 h-3 sm:w-3.5 sm:h-3.5 group-hover:scale-110 transition-transform duration-300" />
                        <span>Tentang Kami</span>
                    </Link>
                </div>

                <!-- Quick Stats with hover effects - Responsive -->
                <div class="hero-item grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-2.5 max-w-xl sm:max-w-2xl mx-auto px-2 sm:px-0">
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-emerald-500/15 hover:border-emerald-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 cursor-default">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500/30 transition-all duration-300">
                            <MapPin class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-300" />
                        </div>
                        <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-emerald-200 transition-colors">{{ stats?.destinations || 6 }}+</p>
                        <p class="text-[8px] sm:text-[9px] text-white/60 group-hover:text-white/80 transition-colors">Destinasi</p>
                    </div>
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-teal-500/15 hover:border-teal-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-teal-500/10 transition-all duration-300 cursor-default">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-teal-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-500/30 transition-all duration-300">
                            <Trees class="w-3 h-3 sm:w-4 sm:h-4 text-teal-300" />
                        </div>
                        <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-teal-200 transition-colors">{{ stats?.flora || 15 }}+</p>
                        <p class="text-[8px] sm:text-[9px] text-white/60 group-hover:text-white/80 transition-colors">Jenis Flora</p>
                    </div>
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-cyan-500/15 hover:border-cyan-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 cursor-default">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-cyan-500/30 transition-all duration-300">
                            <Bird class="w-3 h-3 sm:w-4 sm:h-4 text-cyan-300" />
                        </div>
                        <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-cyan-200 transition-colors">{{ stats?.fauna || 24 }}+</p>
                        <p class="text-[8px] sm:text-[9px] text-white/60 group-hover:text-white/80 transition-colors">Jenis Fauna</p>
                    </div>
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-amber-500/15 hover:border-amber-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-default">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-amber-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/30 transition-all duration-300">
                            <Users class="w-3 h-3 sm:w-4 sm:h-4 text-amber-300" />
                        </div>
                        <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-amber-200 transition-colors">{{ stats?.visitors ? Math.round(stats.visitors / 1000) : 20 }}K+</p>
                        <p class="text-[8px] sm:text-[9px] text-white/60 group-hover:text-white/80 transition-colors">Pengunjung</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator with hover - Responsive -->
        <button 
            @click="scrollToContent"
            class="absolute bottom-4 sm:bottom-6 lg:bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-1 sm:gap-1.5 text-white/70 hover:text-white hover:scale-110 transition-all duration-300 cursor-pointer group"
            aria-label="Scroll ke bawah"
        >
            <span class="text-[8px] sm:text-[9px] uppercase tracking-widest font-medium">Scroll</span>
            <div class="w-4 h-6 sm:w-5 sm:h-8 rounded-full border-2 border-white/30 group-hover:border-emerald-400/60 flex items-start justify-center p-1 sm:p-1.5 transition-colors duration-300">
                <div class="w-0.5 h-1.5 sm:w-1 sm:h-2 bg-white/60 group-hover:bg-emerald-400 rounded-full animate-scroll-wheel transition-colors duration-300"></div>
            </div>
        </button>
    </section>
</template>

<style scoped>
/* GPU-accelerated CSS animations */
@keyframes gradient-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.animate-gradient-shift {
    animation: gradient-shift 4s ease infinite;
}

@keyframes scroll-wheel {
    0%, 100% { opacity: 0.6; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(3px); }
}

.animate-scroll-wheel {
    animation: scroll-wheel 1.5s ease-in-out infinite;
}

@keyframes float-slow {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.2; }
    50% { transform: translateY(-15px) scale(1.05); opacity: 0.25; }
}

.animate-float-slow {
    animation: float-slow 8s ease-in-out infinite;
    will-change: transform, opacity;
}

@keyframes float-medium {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.15; }
    50% { transform: translateY(12px) scale(0.95); opacity: 0.2; }
}

.animate-float-medium {
    animation: float-medium 6s ease-in-out infinite;
    will-change: transform, opacity;
}

@keyframes pulse-slow {
    0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.15; }
    50% { transform: translate(-50%, -50%) scale(1.08); opacity: 0.2; }
}

.animate-pulse-slow {
    animation: pulse-slow 7s ease-in-out infinite;
    will-change: transform, opacity;
}

/* Performance optimizations */
.hero-item {
    will-change: transform, opacity;
}
</style>
