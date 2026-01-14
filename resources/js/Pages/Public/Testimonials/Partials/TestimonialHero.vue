<script setup>
/**
 * TestimonialHero.vue - Premium Hero matching Destination Page Design
 * Features: Dark gradient, GSAP parallax, floating shapes, wave SVG, stats cards
 */
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { Quote, Star, Users, MessageSquare, ChevronDown, Sparkles, Heart } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

const props = defineProps({
    avgRating: { type: [Number, String], default: 0 },
    totalReviews: { type: Number, default: 0 }
});

const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const counters = ref({ rating: 0, reviews: 0 });
let ctx;

const parsedRating = computed(() => parseFloat(props.avgRating) || 0);

const scrollToTestimonials = () => {
    gsap.to(window, { duration: 1, scrollTo: { y: '#testimonials-grid', offsetY: 80 }, ease: 'power3.inOut' });
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
            rating: parsedRating.value,
            reviews: props.totalReviews,
            duration: 2,
            ease: 'power2.out',
            delay: 0.8
        });

        // Subtle floating animation for geometric shapes
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
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-pink-950 to-slate-900"></div>
            
            <!-- Animated Mesh Gradient -->
            <div class="absolute inset-0 opacity-60">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(236,72,153,0.15),transparent_50%)]"></div>
                <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(168,85,247,0.12),transparent_50%)]"></div>
                <div class="absolute bottom-0 left-1/2 w-full h-full bg-[radial-gradient(ellipse_at_50%_100%,rgba(244,114,182,0.08),transparent_50%)]"></div>
            </div>

            <!-- Floating Geometric Shapes -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="float-shape absolute top-[15%] left-[8%] w-20 h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                <div class="float-shape absolute top-[25%] right-[12%] w-14 h-14 border border-pink-500/10 rounded-full"></div>
                <div class="float-shape absolute top-[55%] left-[15%] w-10 h-10 bg-gradient-to-br from-fuchsia-500/5 to-pink-500/5 rounded-lg rotate-45"></div>
                <div class="float-shape absolute bottom-[25%] right-[8%] w-16 h-16 border border-purple-500/[0.08] rounded-xl -rotate-12"></div>
                <div class="float-shape absolute top-[40%] left-[60%] w-8 h-8 border border-pink-400/10 rounded-full"></div>
            </div>

            <!-- Subtle Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

            <!-- Premium Wave SVG -->
            <svg class="wave-layer absolute -bottom-1 left-0 right-0 w-full h-44" viewBox="0 0 1440 200" fill="none" preserveAspectRatio="none">
                <path d="M0,120L60,110C120,100,240,80,360,85C480,90,600,120,720,130C840,140,960,130,1080,115C1200,100,1320,80,1380,70L1440,60L1440,200L0,200Z" fill="rgba(255,255,255,0.02)"/>
                <path d="M0,140L48,135C96,130,192,120,288,115C384,110,480,110,576,120C672,130,768,150,864,155C960,160,1056,150,1152,135C1248,120,1344,100,1392,90L1440,80L1440,200L0,200Z" fill="rgba(236,72,153,0.03)"/>
                <path d="M0,160L60,155C120,150,240,140,360,145C480,150,600,170,720,175C840,180,960,170,1080,160C1200,150,1320,140,1380,135L1440,130L1440,200L0,200Z" fill="#f9fafb"/>
            </svg>
        </div>

        <!-- Content -->
        <div ref="contentRef" class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <!-- Badge with blinking dot -->
                <div class="hero-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-4 hover:bg-white/15 hover:border-white/30 hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-pink-500"></span>
                    </span>
                    <span class="text-[10px] font-semibold tracking-wide">Cerita Pengunjung</span>
                    <Quote class="w-3 h-3 text-pink-400" />
                </div>

                <!-- Title -->
                <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-2">
                    Testimoni
                </h1>
                <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black bg-gradient-to-r from-pink-300 via-fuchsia-200 to-purple-300 bg-clip-text text-transparent mb-4">
                    Pengunjung
                </h1>

                <!-- Description -->
                <p class="hero-item text-xs sm:text-sm text-white/60 max-w-xl mx-auto mb-8 leading-relaxed">
                    Dengarkan pengalaman menakjubkan dari pengunjung yang telah menjelajahi keindahan Taman Nasional Lore Lindu
                </p>

                <!-- Stats Cards -->
                <div class="hero-item grid grid-cols-2 sm:grid-cols-3 gap-3 max-w-md mx-auto mb-8">
                    <!-- Average Rating -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-amber-500/15 hover:border-amber-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-default">
                        <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-amber-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/30 transition-all duration-300">
                            <Star class="w-4 h-4 text-amber-300 fill-amber-300" />
                        </div>
                        <p class="text-lg sm:text-xl font-black text-white group-hover:text-amber-200 transition-colors">{{ counters.rating.toFixed(1) }}</p>
                        <p class="text-[9px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Rating</p>
                    </div>

                    <!-- Total Reviews -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-pink-500/15 hover:border-pink-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-pink-500/10 transition-all duration-300 cursor-default">
                        <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-pink-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-pink-500/30 transition-all duration-300">
                            <MessageSquare class="w-4 h-4 text-pink-300" />
                        </div>
                        <p class="text-lg sm:text-xl font-black text-white group-hover:text-pink-200 transition-colors">{{ Math.round(counters.reviews) }}</p>
                        <p class="text-[9px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Ulasan</p>
                    </div>

                    <!-- Satisfaction -->
                    <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-purple-500/15 hover:border-purple-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300 cursor-default col-span-2 sm:col-span-1">
                        <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-purple-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-purple-500/30 transition-all duration-300">
                            <Heart class="w-4 h-4 text-purple-300 fill-purple-300" />
                        </div>
                        <p class="text-lg sm:text-xl font-black text-white group-hover:text-purple-200 transition-colors">{{ parsedRating >= 4 ? '98%' : '95%' }}</p>
                        <p class="text-[9px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Puas</p>
                    </div>
                </div>

                <!-- Star Rating Display -->
                <div class="hero-item flex items-center justify-center gap-1 mb-8">
                    <div class="flex">
                        <Star v-for="i in 5" :key="i" :class="['w-5 h-5 sm:w-6 sm:h-6 transition-all duration-300', i <= Math.round(parsedRating) ? 'text-yellow-400 fill-yellow-400' : 'text-white/30']" />
                    </div>
                    <span class="text-white/80 text-sm ml-2">{{ parsedRating.toFixed(1) }} dari 5</span>
                </div>

                <!-- Scroll Hint -->
                <button @click="scrollToTestimonials" class="hero-item flex flex-col items-center gap-1.5 group cursor-pointer mx-auto">
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
