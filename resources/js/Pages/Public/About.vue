<script setup>
/**
 * About.vue - Redesigned Premium About Page
 * Matches Destination/FAQ Hero Design with GSAP animations
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    Mountain, Trophy, Bird, Dna, Award, Globe, BadgeCheck, 
    Lightbulb, MapPin, Ticket, ArrowRight, Heart, Users, 
    Leaf, Shield, BookOpen, Zap, TreePine, Compass, Sparkles,
    Building2, Phone, Mail, ExternalLink
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

// Refs
const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const storyRef = ref(null);
const featuresRef = ref(null);
const counters = ref({ hektar: 0, year: 0, species: 0, endemic: 0 });
let ctx;

// Stats data
const stats = [
    { value: 217000, suffix: '', label: 'Hektar', icon: Mountain, color: 'emerald' },
    { value: 1977, suffix: '', label: 'UNESCO', icon: Trophy, color: 'amber' },
    { value: 267, suffix: '+', label: 'Spesies', icon: Bird, color: 'cyan' },
    { value: 90, suffix: '%', label: 'Endemik', icon: Dna, color: 'rose' },
];

// Certifications
const certifications = [
    { title: 'UNESCO', subtitle: 'Biosphere Reserve', icon: Award, gradient: 'from-blue-500 to-indigo-600' },
    { title: 'ASEAN', subtitle: 'Heritage Park', icon: Globe, gradient: 'from-teal-500 to-emerald-600' },
    { title: 'Official', subtitle: 'E-Ticketing', icon: BadgeCheck, gradient: 'from-emerald-500 to-green-600' },
];

// Features/Values
const features = [
    { 
        title: 'Konservasi Alam', 
        description: 'Mendukung pelestarian ekosistem hutan hujan tropis dan keanekaragaman hayati Sulawesi',
        icon: Leaf, 
        gradient: 'from-emerald-500 to-green-600' 
    },
    { 
        title: 'Edukasi Lingkungan', 
        description: 'Menyediakan informasi edukatif tentang flora, fauna, dan ekosistem Taman Nasional',
        icon: BookOpen, 
        gradient: 'from-blue-500 to-indigo-600' 
    },
    { 
        title: 'Pemberdayaan Lokal', 
        description: 'Mendorong keterlibatan masyarakat lokal dalam pengelolaan wisata berkelanjutan',
        icon: Users, 
        gradient: 'from-amber-500 to-orange-600' 
    },
    { 
        title: 'Teknologi Modern', 
        description: 'Menghadirkan pengalaman booking tiket digital yang mudah dan transparan',
        icon: Zap, 
        gradient: 'from-violet-500 to-purple-600' 
    },
];

// Scroll to content
const scrollToContent = () => {
    import('gsap/ScrollToPlugin').then(({ ScrollToPlugin }) => {
        gsap.registerPlugin(ScrollToPlugin);
        gsap.to(window, { duration: 1, scrollTo: { y: '#about-story', offsetY: 80 }, ease: 'power3.inOut' });
    });
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Hero content entrance
        const tl = gsap.timeline({ delay: 0.3 });
        tl.fromTo('.hero-item', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );

        // Counter animations
        gsap.to(counters.value, {
            hektar: 217,
            year: 1977,
            species: 267,
            endemic: 90,
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

        // Story section entrance
        gsap.fromTo('.story-item', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', 
                scrollTrigger: { trigger: storyRef.value, start: 'top 85%', toggleActions: 'play none none reverse' } 
            }
        );

        // Features section entrance
        gsap.fromTo('.feature-card', 
            { opacity: 0, y: 40, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'power2.out', 
                scrollTrigger: { trigger: featuresRef.value, start: 'top 85%', toggleActions: 'play none none reverse' } 
            }
        );
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <PublicLayout>
        <!-- Premium Hero Section (Matching Destination Design) -->
        <section ref="heroRef" class="relative min-h-[70vh] flex items-center justify-center overflow-hidden pt-16 pb-12">
            <!-- Background with gradient and effects -->
            <div ref="backgroundRef" class="absolute inset-0">
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
            <div ref="contentRef" class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <!-- Badge with blinking dot -->
                    <div class="hero-item inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-3 sm:mb-4 hover:bg-white/15 hover:border-white/30 hover:scale-105 transition-all duration-300 cursor-default">
                        <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-semibold tracking-wide">Tentang Kami</span>
                        <Heart class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-rose-400" />
                    </div>

                    <!-- Title -->
                    <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black text-white leading-tight mb-1 sm:mb-2">
                        Mengenal
                    </h1>
                    <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-300 bg-clip-text text-transparent mb-3 sm:mb-4">
                        TNLL Explore
                    </h1>

                    <!-- Description -->
                    <p class="hero-item text-white/60 text-[11px] sm:text-xs md:text-sm max-w-xl mx-auto mb-5 sm:mb-6 leading-relaxed px-2 sm:px-0">
                        Platform digital resmi untuk eksplorasi wisata alam di 
                        <span class="text-emerald-400 font-semibold">Taman Nasional Lore Lindu</span> — 
                        Surga Biodiversitas di Jantung Sulawesi Tengah
                    </p>

                    <!-- Stats Cards -->
                    <div class="hero-item grid grid-cols-4 gap-1.5 sm:gap-2 md:gap-3 max-w-lg sm:max-w-xl mx-auto mb-6 sm:mb-8 px-2 sm:px-0">
                        <!-- Hektar -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-emerald-500/15 hover:border-emerald-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 cursor-default">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500/30 transition-all duration-300">
                                <Mountain class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-300" />
                            </div>
                            <p class="text-sm sm:text-base lg:text-lg font-black text-white group-hover:text-emerald-200 transition-colors">{{ Math.round(counters.hektar) }}K</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Hektar</p>
                        </div>

                        <!-- UNESCO -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-amber-500/15 hover:border-amber-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-default">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-amber-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/30 transition-all duration-300">
                                <Trophy class="w-3 h-3 sm:w-4 sm:h-4 text-amber-300" />
                            </div>
                            <p class="text-sm sm:text-base lg:text-lg font-black text-white group-hover:text-amber-200 transition-colors">{{ Math.round(counters.year) }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">UNESCO</p>
                        </div>

                        <!-- Species -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-cyan-500/15 hover:border-cyan-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 cursor-default">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-cyan-500/30 transition-all duration-300">
                                <Bird class="w-3 h-3 sm:w-4 sm:h-4 text-cyan-300" />
                            </div>
                            <p class="text-sm sm:text-base lg:text-lg font-black text-white group-hover:text-cyan-200 transition-colors">{{ Math.round(counters.species) }}+</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Spesies</p>
                        </div>

                        <!-- Endemic -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-rose-500/15 hover:border-rose-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-rose-500/10 transition-all duration-300 cursor-default">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-rose-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-rose-500/30 transition-all duration-300">
                                <Dna class="w-3 h-3 sm:w-4 sm:h-4 text-rose-300" />
                            </div>
                            <p class="text-sm sm:text-base lg:text-lg font-black text-white group-hover:text-rose-200 transition-colors">{{ Math.round(counters.endemic) }}%</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Endemik</p>
                        </div>
                    </div>

                    <!-- Scroll Hint -->
                    <button @click="scrollToContent" class="hero-item flex flex-col items-center gap-1 sm:gap-1.5 group cursor-pointer mx-auto">
                        <span class="text-[8px] sm:text-[9px] text-white/30 uppercase tracking-[0.15em] font-medium group-hover:text-white/50 transition-colors">Scroll</span>
                        <div class="relative w-4 h-6 sm:w-5 sm:h-8 rounded-full border border-white/20 group-hover:border-white/40 flex justify-center pt-1 sm:pt-1.5 transition-colors">
                            <div class="w-0.5 h-1.5 sm:w-1 sm:h-2 bg-white/40 group-hover:bg-white/60 rounded-full animate-scroll-pulse transition-colors"></div>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <!-- Story Section -->
        <section id="about-story" ref="storyRef" class="relative py-12 sm:py-16 lg:py-20 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
            <!-- Background decorations -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-20 -right-32 w-64 h-64 bg-emerald-100/40 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 -left-32 w-56 h-56 bg-teal-100/30 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="story-item text-center mb-8 sm:mb-12">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                        <Lightbulb class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">CERITA KAMI</span>
                        <Sparkles class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-500" />
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-900 tracking-tight mb-2">
                        Menjelajahi 
                        <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">Keindahan</span>
                        Sulawesi Tengah
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 max-w-lg mx-auto">
                        Dari hutan hujan tropis hingga puncak pegunungan, temukan surga biodiversitas yang tersembunyi
                    </p>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <!-- Left: Featured Image -->
                    <div class="story-item relative">
                        <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl group">
                            <img src="/assets/danautambing.jpg" alt="Danau Tambing - Taman Nasional Lore Lindu" class="w-full aspect-[4/3] sm:aspect-[4/5] object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6">
                                <div class="bg-white/95 backdrop-blur rounded-xl p-3 sm:p-4 shadow-lg">
                                    <div class="flex items-center gap-3 sm:gap-4">
                                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md flex-shrink-0">
                                            <MapPin class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm sm:text-base">Danau Tambing</h4>
                                            <p class="text-gray-500 text-[10px] sm:text-xs">Ikon Wisata Taman Nasional</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- UNESCO Badge -->
                        <div class="absolute -top-3 -right-3 sm:-top-4 sm:-right-4 p-3 sm:p-4 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl sm:rounded-2xl shadow-xl text-white transform rotate-6 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-xl sm:text-2xl font-black">1977</div>
                                <div class="text-[8px] sm:text-[10px] font-bold opacity-90 uppercase tracking-wide">UNESCO</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Story Content -->
                    <div class="space-y-4 sm:space-y-6">
                        <div class="story-item space-y-3 sm:space-y-4">
                            <p class="text-sm sm:text-base text-gray-700 leading-relaxed">
                                <span class="font-black text-emerald-600 text-base sm:text-lg">TNLL Explore</span> adalah platform e-ticketing resmi untuk kawasan wisata di Taman Nasional Lore Lindu, Sulawesi Tengah.
                            </p>
                            <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                                Taman Nasional Lore Lindu merupakan <strong class="text-gray-700">cagar biosfer UNESCO</strong> sejak tahun 1977, dengan luas lebih dari <strong class="text-emerald-600">217.000 hektar</strong>. Kawasan ini menyimpan kekayaan biodiversitas yang luar biasa dengan <strong class="text-cyan-600">267+ spesies</strong> satwa, dimana <strong class="text-rose-600">90% adalah endemik</strong> Sulawesi.
                            </p>
                        </div>

                        <!-- Certifications -->
                        <div class="story-item flex flex-wrap gap-2 sm:gap-3">
                            <div v-for="cert in certifications" :key="cert.title" class="inline-flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-white border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                <div :class="['w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center shadow bg-gradient-to-br', cert.gradient]">
                                    <component :is="cert.icon" class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900 text-xs sm:text-sm">{{ cert.title }}</div>
                                    <div class="text-[9px] sm:text-[10px] text-gray-500">{{ cert.subtitle }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Mission Quote -->
                        <div class="story-item relative p-4 sm:p-6 rounded-xl sm:rounded-2xl bg-gradient-to-br from-emerald-50 to-cyan-50 border border-emerald-100">
                            <div class="absolute -top-2.5 sm:-top-3 left-4 sm:left-6">
                                <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 bg-emerald-500 text-white text-[10px] sm:text-xs font-bold rounded-full shadow">
                                    <Lightbulb class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                    Misi Kami
                                </span>
                            </div>
                            <p class="text-gray-600 leading-relaxed italic mt-2 sm:mt-3 text-xs sm:text-sm">
                                "Meningkatkan pengalaman wisatawan sekaligus mendukung upaya pelestarian alam dan pemberdayaan masyarakat lokal di sekitar Taman Nasional Lore Lindu."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features/Values Section -->
        <section ref="featuresRef" class="relative py-12 sm:py-16 lg:py-20 bg-white overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-emerald-50 to-cyan-50 rounded-full blur-3xl opacity-50"></div>
            </div>

            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-8 sm:mb-12">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                        <Shield class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">NILAI-NILAI KAMI</span>
                        <Sparkles class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-500" />
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-900 tracking-tight mb-2">
                        Komitmen 
                        <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">TNLL Explore</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 max-w-lg mx-auto">
                        Empat pilar utama yang menjadi fondasi layanan kami
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    <div v-for="feature in features" :key="feature.title" class="feature-card group relative bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <!-- Icon -->
                        <div :class="['w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg mb-3 sm:mb-4 bg-gradient-to-br group-hover:scale-110 transition-transform duration-300', feature.gradient]">
                            <component :is="feature.icon" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                        </div>
                        <!-- Content -->
                        <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-1.5 sm:mb-2 group-hover:text-emerald-600 transition-colors">{{ feature.title }}</h3>
                        <p class="text-gray-500 text-[11px] sm:text-xs leading-relaxed">{{ feature.description }}</p>
                        <!-- Hover Glow -->
                        <div class="absolute inset-0 rounded-xl sm:rounded-2xl bg-gradient-to-br from-emerald-500/5 to-cyan-500/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Organization Section -->
        <section class="relative py-12 sm:py-16 lg:py-20 bg-gradient-to-b from-white to-gray-50 overflow-hidden">
            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <!-- Left: Content -->
                        <div class="p-6 sm:p-8 lg:p-10 flex flex-col justify-center">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-4 shadow-sm w-fit">
                                <Building2 class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                                <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">PENGELOLA</span>
                            </div>
                            <h2 class="text-lg sm:text-xl md:text-2xl font-black text-gray-900 mb-3 sm:mb-4">
                                Balai Besar Taman Nasional Lore Lindu
                            </h2>
                            <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-4 sm:mb-6">
                                TNLL Explore dikelola secara resmi oleh Balai Besar Taman Nasional Lore Lindu di bawah naungan Kementerian Lingkungan Hidup dan Kehutanan Republik Indonesia. Kami berkomitmen untuk menyediakan layanan terbaik bagi wisatawan sambil menjaga kelestarian alam.
                            </p>
                            
                            <!-- Contact Info -->
                            <div class="space-y-2 sm:space-y-3">
                                <div class="flex items-center gap-2 sm:gap-3 text-gray-600">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                        <MapPin class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <span class="text-[11px] sm:text-xs">Jl. Prof. Moh. Yamin No. 32, Palu, Sulawesi Tengah</span>
                                </div>
                                <div class="flex items-center gap-2 sm:gap-3 text-gray-600">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                        <Phone class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <span class="text-[11px] sm:text-xs">(0451) 421575</span>
                                </div>
                                <div class="flex items-center gap-2 sm:gap-3 text-gray-600">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                        <Mail class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <span class="text-[11px] sm:text-xs">info@tnlorelindu.id</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right: Image/Map -->
                        <div class="relative min-h-[200px] sm:min-h-[280px] lg:min-h-0 bg-gradient-to-br from-emerald-500 to-teal-600">
                            <img src="/assets/tentang.jpg" alt="Balai Besar TNLL" class="w-full h-full object-cover opacity-80">
                            <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/80 via-transparent to-transparent flex items-end justify-center pb-6 sm:pb-8">
                                <div class="text-center text-white">
                                    <TreePine class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 opacity-80" />
                                    <p class="text-xs sm:text-sm font-semibold opacity-90">Menjaga Warisan Alam Indonesia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-12 sm:py-16 lg:py-20 overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900">
                <div class="absolute top-0 right-1/4 w-64 h-64 sm:w-96 sm:h-96 bg-emerald-500/20 rounded-full blur-[100px] animate-pulse"></div>
                <div class="absolute bottom-0 left-1/4 w-48 h-48 sm:w-72 sm:h-72 bg-teal-500/20 rounded-full blur-[80px] animate-pulse" style="animation-delay: 1.5s"></div>
            </div>

            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-2xl mx-auto text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 mb-4 sm:mb-6 rounded-xl sm:rounded-2xl bg-white/10 backdrop-blur-md border border-white/20">
                        <Compass class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-2 sm:mb-3">Mulai Petualangan Anda</h2>
                    <p class="text-white/60 text-xs sm:text-sm md:text-base mb-6 sm:mb-8 max-w-lg mx-auto">
                        Jelajahi keindahan Taman Nasional Lore Lindu dengan mudah melalui platform resmi kami
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                        <Link 
                            href="/destinations" 
                            class="group relative inline-flex items-center justify-center gap-2 sm:gap-3 px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl text-white font-semibold text-xs sm:text-sm shadow-xl hover:shadow-2xl transition-all hover:-translate-y-1 hover:scale-105 overflow-hidden"
                        >
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <Ticket class="relative w-4 h-4 sm:w-5 sm:h-5" />
                            <span class="relative">Jelajahi Destinasi</span>
                            <ArrowRight class="relative w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-1 transition-transform" />
                        </Link>
                        <Link 
                            href="/contact" 
                            class="inline-flex items-center justify-center gap-2 sm:gap-3 px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl bg-white/10 text-white font-semibold text-xs sm:text-sm border border-white/20 hover:bg-white/20 transition-all"
                        >
                            <Mail class="w-4 h-4 sm:w-5 sm:h-5" />
                            <span>Hubungi Kami</span>
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
@keyframes scroll-pulse {
    0%, 100% { opacity: 1; transform: translateY(0); }
    50% { opacity: 0.3; transform: translateY(4px); }
}
.animate-scroll-pulse { animation: scroll-pulse 1.5s ease-in-out infinite; }

/* Performance optimizations */
.hero-item, .float-shape, .story-item, .feature-card {
    will-change: transform, opacity;
}
</style>
