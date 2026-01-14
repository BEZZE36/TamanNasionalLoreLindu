<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    Zap, QrCode, Leaf, Landmark, ShieldCheck, Award, Check, Star, 
    ArrowRight, Users, Clock, Globe, Mountain, Bird, Sparkles,
    MapPin, Camera, Heart, Shield, TreePine
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
const counters = ref({ visitors: 0, species: 0, area: 0, rating: 0 });
let ctx;

// Main features of TNLL
const mainFeatures = [
    { 
        icon: QrCode, 
        title: 'Tiket Digital QR', 
        description: 'Scan QR code langsung dari smartphone untuk akses cepat tanpa antri.', 
        color: 'cyan',
        highlight: '< 2 menit'
    },
    { 
        icon: Leaf, 
        title: 'Keanekaragaman Hayati', 
        description: '267+ spesies burung dan mamalia endemik Sulawesi yang langka.', 
        color: 'emerald',
        highlight: '267+ spesies'
    },
    { 
        icon: Landmark, 
        title: 'Situs Megalitikum', 
        description: 'Patung batu purba misterius di Lembah Bada, Besoa, dan Napu.', 
        color: 'amber',
        highlight: '3 Lembah'
    },
    { 
        icon: ShieldCheck, 
        title: 'Platform Resmi', 
        description: 'Kerjasama resmi dengan Balai Besar TNLL untuk pelayanan terbaik.', 
        color: 'teal',
        highlight: 'Verified'
    }
];

// Quick benefits
const benefits = [
    { icon: Clock, text: 'Pemesanan Instan' },
    { icon: Shield, text: 'Pembayaran Aman' },
    { icon: Users, text: 'Support 24/7' },
    { icon: Check, text: 'Konfirmasi Langsung' }
];

// UNESCO info
const unescoInfo = {
    title: 'UNESCO Biosphere Reserve',
    year: '1977',
    area: '217.991 Ha',
    description: 'Cagar Biosfer yang dilindungi secara internasional'
};

// Stats for counters
const stats = [
    { label: 'Pengunjung', value: 15000, suffix: '+', icon: Users },
    { label: 'Spesies Fauna', value: 267, suffix: '+', icon: Bird },
    { label: 'Hektar Luas', value: 217, suffix: 'K', icon: TreePine },
    { label: 'Rating', value: 4.9, suffix: '★', icon: Star, decimal: true }
];

const getColorClasses = (color) => {
    const colors = { 
        cyan: { 
            icon: 'bg-gradient-to-br from-cyan-500 to-blue-600', 
            iconLight: 'bg-gradient-to-br from-cyan-100 to-cyan-50',
            text: 'text-cyan-600',
            border: 'border-cyan-200',
            glow: 'shadow-cyan-500/20'
        }, 
        emerald: { 
            icon: 'bg-gradient-to-br from-emerald-500 to-teal-600', 
            iconLight: 'bg-gradient-to-br from-emerald-100 to-emerald-50',
            text: 'text-emerald-600',
            border: 'border-emerald-200',
            glow: 'shadow-emerald-500/20'
        }, 
        amber: { 
            icon: 'bg-gradient-to-br from-amber-500 to-orange-600', 
            iconLight: 'bg-gradient-to-br from-amber-100 to-amber-50',
            text: 'text-amber-600',
            border: 'border-amber-200',
            glow: 'shadow-amber-500/20'
        }, 
        teal: { 
            icon: 'bg-gradient-to-br from-teal-500 to-cyan-600', 
            iconLight: 'bg-gradient-to-br from-teal-100 to-teal-50',
            text: 'text-teal-600',
            border: 'border-teal-200',
            glow: 'shadow-teal-500/20'
        } 
    };
    return colors[color] || colors.emerald;
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Header entrance
        gsap.fromTo('.why-header > *', 
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
        
        // Stats cards
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 30, scale: 0.95 }, 
            { 
                opacity: 1, y: 0, scale: 1, 
                duration: 0.5, stagger: 0.08, 
                ease: 'back.out(1.2)', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse',
                    onEnter: () => animateCounters(),
                    onEnterBack: () => animateCounters()
                } 
            }
        );
        
        // Feature cards with staggered entrance
        gsap.fromTo('.feature-card', 
            { opacity: 0, y: 40, scale: 0.92 }, 
            { 
                opacity: 1, y: 0, scale: 1, 
                duration: 0.6, stagger: 0.1, 
                ease: 'power3.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 75%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );

        // Benefits entrance
        gsap.fromTo('.benefit-item', 
            { opacity: 0, x: -20 }, 
            { 
                opacity: 1, x: 0, 
                duration: 0.4, stagger: 0.06, 
                ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 70%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );

        // CTA entrance
        gsap.fromTo('.cta-section', 
            { opacity: 0, y: 25 }, 
            { 
                opacity: 1, y: 0, 
                duration: 0.6, 
                ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 60%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
    }, sectionRef.value);
});

const animateCounters = () => {
    gsap.to(counters.value, { visitors: 15000, duration: 2, ease: 'power2.out', snap: { visitors: 1 } });
    gsap.to(counters.value, { species: 267, duration: 1.8, ease: 'power2.out', snap: { species: 1 } });
    gsap.to(counters.value, { area: 217, duration: 2.2, ease: 'power2.out', snap: { area: 1 } });
    gsap.to(counters.value, { rating: 4.9, duration: 1.5, ease: 'power2.out' });
};

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-emerald-50/30 to-white overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-8 sm:-top-12 lg:-top-16 left-0 w-48 sm:w-64 lg:w-80 h-48 sm:h-64 lg:h-80 bg-gradient-to-br from-emerald-200/35 to-teal-200/15 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-44 sm:w-56 lg:w-72 h-44 sm:h-56 lg:h-72 bg-gradient-to-tl from-teal-200/30 to-cyan-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-r from-emerald-100/15 to-teal-100/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="why-header text-left mb-8 sm:mb-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-2 sm:mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Mengapa Kami</span>
                    <Award class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-500" />
                </div>
                
                <!-- Title -->
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                    Kenapa Harus 
                    <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        TNLL Explore?
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 max-w-lg">Platform resmi untuk pengalaman terbaik menjelajahi keindahan Taman Nasional Lore Lindu</p>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8 sm:mb-10">
                <div 
                    v-for="(stat, index) in stats" 
                    :key="index"
                    class="stat-card group relative p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white border border-gray-100 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
                >
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <component :is="stat.icon" class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-base sm:text-lg lg:text-xl font-black text-gray-900">
                                <template v-if="stat.decimal">{{ counters.rating.toFixed(1) }}</template>
                                <template v-else-if="stat.label === 'Pengunjung'">{{ Math.round(counters.visitors / 1000) }}K</template>
                                <template v-else-if="stat.label === 'Spesies Fauna'">{{ Math.round(counters.species) }}</template>
                                <template v-else-if="stat.label === 'Hektar Luas'">{{ Math.round(counters.area) }}K</template>
                                <span class="text-emerald-600">{{ stat.suffix }}</span>
                            </p>
                            <p class="text-[9px] sm:text-[10px] text-gray-500 font-medium">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6 lg:gap-8 mb-8 sm:mb-10">
                <!-- Left Side - Main Card -->
                <div class="order-2 lg:order-1">
                    <div class="h-full bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-xl sm:rounded-2xl p-5 sm:p-6 lg:p-7 text-white relative overflow-hidden shadow-xl hover:shadow-2xl shadow-emerald-600/20 transition-shadow duration-500">
                        <!-- Background Elements -->
                        <div class="absolute top-0 right-0 w-24 sm:w-32 h-24 sm:h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-20 sm:w-28 h-20 sm:h-28 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        <div class="absolute top-1/2 right-1/4 w-16 h-16 bg-cyan-400/10 rounded-full blur-xl"></div>
                        
                        <div class="relative z-10">
                            <!-- Icon -->
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center mb-4 shadow-lg">
                                <Zap class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-lg sm:text-xl lg:text-2xl font-bold mb-2 sm:mb-3">Pemesanan Instan</h3>
                            
                            <!-- Description -->
                            <p class="text-white/80 text-xs sm:text-sm leading-relaxed mb-4 sm:mb-5">
                                Tidak perlu antri di loket. Pesan tiket kapan saja, di mana saja. Proses cepat kurang dari 2 menit dengan pembayaran aman dan terenkripsi.
                            </p>
                            
                            <!-- Benefits Tags -->
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 mb-5 sm:mb-6">
                                <span 
                                    v-for="benefit in benefits" 
                                    :key="benefit.text" 
                                    class="benefit-item inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-lg bg-white/15 backdrop-blur-sm text-[9px] sm:text-[10px] font-medium ring-1 ring-white/10 hover:bg-white/20 transition-colors"
                                >
                                    <component :is="benefit.icon" class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-300" />
                                    {{ benefit.text }}
                                </span>
                            </div>
                            
                            <!-- Testimonial Preview -->
                            <div class="pt-4 sm:pt-5 border-t border-white/15">
                                <div class="flex items-center gap-3">
                                    <div class="flex -space-x-1.5 sm:-space-x-2">
                                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/25 ring-2 ring-white/30 flex items-center justify-center text-[8px] sm:text-[10px] font-bold">AS</div>
                                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/20 ring-2 ring-white/30 flex items-center justify-center text-[8px] sm:text-[10px] font-bold">SR</div>
                                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/15 ring-2 ring-white/30 flex items-center justify-center text-[8px] sm:text-[10px] font-bold">MH</div>
                                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-emerald-400/40 ring-2 ring-white/30 flex items-center justify-center text-[8px] sm:text-[10px] font-bold">+</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-0.5 mb-0.5">
                                            <Star v-for="i in 5" :key="i" class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-amber-400 fill-current" />
                                        </div>
                                        <p class="text-[9px] sm:text-[10px] text-white/60">15.000+ pengunjung puas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Feature Cards Grid -->
                <div class="order-1 lg:order-2 grid grid-cols-2 gap-3 sm:gap-4">
                    <div 
                        v-for="f in mainFeatures" 
                        :key="f.title" 
                        class="feature-card group relative p-3 sm:p-4 lg:p-5 rounded-xl sm:rounded-2xl bg-white border border-gray-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                        :class="[`hover:${getColorClasses(f.color).border}`]"
                    >
                        <!-- Icon & Highlight Row -->
                        <div class="flex items-start justify-between mb-2 sm:mb-3">
                            <div :class="['w-9 h-9 sm:w-10 sm:h-10 lg:w-11 lg:h-11 rounded-lg sm:rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300', getColorClasses(f.color).icon]">
                                <component :is="f.icon" class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                            </div>
                            <span :class="['px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md sm:rounded-lg text-[8px] sm:text-[9px] font-bold', getColorClasses(f.color).iconLight, getColorClasses(f.color).text]">
                                {{ f.highlight }}
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 mb-1 sm:mb-1.5 group-hover:text-emerald-600 transition-colors leading-tight">
                            {{ f.title }}
                        </h3>
                        
                        <!-- Description -->
                        <p class="text-gray-500 text-[10px] sm:text-xs leading-relaxed line-clamp-3">
                            {{ f.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- UNESCO Badge & CTA Row -->
            <div class="cta-section flex flex-col sm:flex-row items-center justify-between gap-4 sm:gap-6 p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50/50 to-white border border-emerald-100">
                <!-- UNESCO Badge -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                        <Globe class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h4 class="text-sm sm:text-base font-bold text-gray-900">{{ unescoInfo.title }}</h4>
                            <span class="px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-700 text-[8px] sm:text-[9px] font-bold">{{ unescoInfo.year }}</span>
                        </div>
                        <p class="text-[10px] sm:text-xs text-gray-500">{{ unescoInfo.description }} • {{ unescoInfo.area }}</p>
                    </div>
                </div>

                <!-- CTA Button -->
                <Link 
                    href="/about" 
                    class="group inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium text-[11px] sm:text-xs shadow-md shadow-emerald-500/25 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 whitespace-nowrap"
                >
                    <span>Pelajari Lebih Lanjut</span>
                    <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
    </section>
</template>
