<script setup>
/**
 * FAQ.vue - Redesigned Premium FAQ Page
 * Matches Destination Hero Design with GSAP animations
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    HelpCircle, Ticket, Compass, Building, CreditCard, Headphones, 
    Mail, ArrowRight, ChevronDown, MessageCircleQuestion, Sparkles,
    Search, X
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    faqItems: { type: Array, default: () => [] },
    faqCategories: { type: Object, default: () => ({}) }
});

// Refs
const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const sectionRef = ref(null);
const activeTab = ref(Object.keys(props.faqCategories)[0] || 'Umum');
const openFaq = ref(null);
const searchQuery = ref('');
const counters = ref({ faq: 0, categories: 0 });
let ctx;

// Category configuration with emerald/teal theme
const categoryConfig = {
    'Tiket': { icon: Ticket, gradient: 'from-violet-500 to-purple-600', bgLight: 'bg-violet-500/20', textLight: 'text-violet-300', hoverBg: 'hover:bg-violet-500/15' },
    'Wisata': { icon: Compass, gradient: 'from-emerald-500 to-teal-600', bgLight: 'bg-emerald-500/20', textLight: 'text-emerald-300', hoverBg: 'hover:bg-emerald-500/15' },
    'Fasilitas': { icon: Building, gradient: 'from-amber-500 to-orange-600', bgLight: 'bg-amber-500/20', textLight: 'text-amber-300', hoverBg: 'hover:bg-amber-500/15' },
    'Pembayaran': { icon: CreditCard, gradient: 'from-blue-500 to-indigo-600', bgLight: 'bg-blue-500/20', textLight: 'text-blue-300', hoverBg: 'hover:bg-blue-500/15' },
    'Umum': { icon: HelpCircle, gradient: 'from-slate-500 to-gray-600', bgLight: 'bg-slate-500/20', textLight: 'text-slate-300', hoverBg: 'hover:bg-slate-500/15' }
};

const getConfig = (cat) => categoryConfig[cat] || categoryConfig['Umum'];
const toggleFaq = (id) => { openFaq.value = openFaq.value === id ? null : id; };
const totalFaq = computed(() => props.faqItems?.length || 0);
const totalCategories = computed(() => Object.keys(props.faqCategories).length);

// Filtered FAQ items based on search
const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) return props.faqCategories;
    
    const query = searchQuery.value.toLowerCase();
    const filtered = {};
    
    Object.entries(props.faqCategories).forEach(([category, items]) => {
        const matchedItems = items.filter(item => 
            item.question.toLowerCase().includes(query) || 
            item.answer.toLowerCase().includes(query)
        );
        if (matchedItems.length > 0) {
            filtered[category] = matchedItems;
        }
    });
    
    return filtered;
});

// Scroll to FAQ section
const scrollToFaq = () => {
    gsap.to(window, { duration: 1, scrollTo: { y: '#faq-content', offsetY: 80 }, ease: 'power3.inOut' });
};

onMounted(() => {
    import('gsap/ScrollToPlugin').then(({ ScrollToPlugin }) => {
        gsap.registerPlugin(ScrollToPlugin);
    });

    ctx = gsap.context(() => {
        // Hero content entrance
        const tl = gsap.timeline({ delay: 0.3 });
        tl.fromTo('.hero-item', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );

        // Counter animations
        gsap.to(counters.value, {
            faq: totalFaq.value,
            categories: totalCategories.value,
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

        // FAQ section entrance
        gsap.fromTo('.faq-header > *', 
            { opacity: 0, y: 25 }, 
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', 
                scrollTrigger: { trigger: sectionRef.value, start: 'top 85%', toggleActions: 'play none none reverse' } 
            }
        );

        gsap.fromTo('.faq-item', 
            { opacity: 0, x: -30, scale: 0.98 }, 
            { opacity: 1, x: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'power2.out', 
                scrollTrigger: { trigger: sectionRef.value, start: 'top 80%', toggleActions: 'play none none reverse' } 
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
                        <span class="text-[9px] sm:text-[10px] font-semibold tracking-wide">Pusat Bantuan</span>
                        <MessageCircleQuestion class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-400" />
                    </div>

                    <!-- Title -->
                    <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black text-white leading-tight mb-1 sm:mb-2">
                        Pertanyaan
                    </h1>
                    <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl font-black bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-300 bg-clip-text text-transparent mb-3 sm:mb-4">
                        Umum (FAQ)
                    </h1>

                    <!-- Description -->
                    <p class="hero-item text-white/60 text-[11px] sm:text-xs md:text-sm max-w-xl mx-auto mb-5 sm:mb-6 leading-relaxed px-2 sm:px-0">
                        Temukan jawaban dari pertanyaan yang sering diajukan seputar wisata di Taman Nasional Lore Lindu
                    </p>

                    <!-- Stats Cards -->
                    <div class="hero-item grid grid-cols-3 gap-2 sm:gap-3 max-w-sm sm:max-w-md mx-auto mb-6 sm:mb-8 px-2 sm:px-0">
                        <!-- Total FAQ -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-emerald-500/15 hover:border-emerald-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 cursor-default">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500/30 transition-all duration-300">
                                <MessageCircleQuestion class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-300" />
                            </div>
                            <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-emerald-200 transition-colors">{{ Math.round(counters.faq) }}</p>
                            <p class="text-[8px] sm:text-[9px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Pertanyaan</p>
                        </div>

                        <!-- Categories -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-teal-500/15 hover:border-teal-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-teal-500/10 transition-all duration-300 cursor-default">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-teal-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-teal-500/30 transition-all duration-300">
                                <HelpCircle class="w-3 h-3 sm:w-4 sm:h-4 text-teal-300" />
                            </div>
                            <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-teal-200 transition-colors">{{ Math.round(counters.categories) }}</p>
                            <p class="text-[8px] sm:text-[9px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Kategori</p>
                        </div>

                        <!-- Support -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2 sm:p-3 hover:bg-cyan-500/15 hover:border-cyan-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 cursor-default">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 mx-auto mb-1 sm:mb-1.5 rounded-md sm:rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-cyan-500/30 transition-all duration-300">
                                <Headphones class="w-3 h-3 sm:w-4 sm:h-4 text-cyan-300" />
                            </div>
                            <p class="text-base sm:text-lg lg:text-xl font-black text-white group-hover:text-cyan-200 transition-colors">24/7</p>
                            <p class="text-[8px] sm:text-[9px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Support</p>
                        </div>
                    </div>

                    <!-- Scroll Hint -->
                    <button @click="scrollToFaq" class="hero-item flex flex-col items-center gap-1 sm:gap-1.5 group cursor-pointer mx-auto">
                        <span class="text-[8px] sm:text-[9px] text-white/30 uppercase tracking-[0.15em] font-medium group-hover:text-white/50 transition-colors">Scroll</span>
                        <div class="relative w-4 h-6 sm:w-5 sm:h-8 rounded-full border border-white/20 group-hover:border-white/40 flex justify-center pt-1 sm:pt-1.5 transition-colors">
                            <div class="w-0.5 h-1.5 sm:w-1 sm:h-2 bg-white/40 group-hover:bg-white/60 rounded-full animate-scroll-pulse transition-colors"></div>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <!-- FAQ Content Section -->
        <section id="faq-content" ref="sectionRef" class="relative py-12 sm:py-16 lg:py-20 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
            <!-- Background decorations -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-20 -right-32 w-64 h-64 bg-emerald-100/40 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 -left-32 w-56 h-56 bg-teal-100/30 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="faq-header text-center mb-8 sm:mb-10">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                        <HelpCircle class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">TEMUKAN JAWABAN</span>
                        <Sparkles class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-500" />
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-900 tracking-tight mb-2">
                        Cari Pertanyaan 
                        <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">Anda</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 max-w-lg mx-auto">
                        Pilih kategori atau gunakan pencarian untuk menemukan jawaban yang Anda butuhkan
                    </p>
                </div>

                <!-- Search Bar -->
                <div class="faq-header relative max-w-xl mx-auto mb-6 sm:mb-8">
                    <div class="relative">
                        <Search class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari pertanyaan..."
                            class="w-full pl-10 sm:pl-12 pr-10 py-2.5 sm:py-3 text-sm sm:text-base bg-white border border-gray-200 rounded-xl sm:rounded-2xl shadow-sm focus:border-emerald-300 focus:ring-2 focus:ring-emerald-100 transition-all placeholder:text-gray-400"
                        >
                        <button 
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 p-1 rounded-full hover:bg-gray-100 transition-colors"
                        >
                            <X class="w-4 h-4 text-gray-400" />
                        </button>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="flex flex-wrap justify-center gap-1.5 sm:gap-2 mb-8 sm:mb-10 px-2 sm:px-0">
                    <button 
                        v-for="(items, category) in faqCategories" 
                        :key="category"
                        @click="activeTab = category; searchQuery = ''"
                        :class="[
                            'group inline-flex items-center gap-1 sm:gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl font-semibold text-[10px] sm:text-xs transition-all hover:-translate-y-0.5 duration-300',
                            activeTab === category 
                                ? `bg-gradient-to-r ${getConfig(category).gradient} text-white shadow-lg scale-105` 
                                : 'bg-white text-gray-600 border border-gray-200 hover:border-emerald-300 hover:shadow-md'
                        ]"
                    >
                        <component :is="getConfig(category).icon" class="w-3 h-3 sm:w-4 sm:h-4" />
                        <span>{{ category }}</span>
                        <span :class="['px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold', activeTab === category ? 'bg-white/20' : 'bg-gray-100 text-gray-500']">
                            {{ items.length }}
                        </span>
                    </button>
                </div>

                <!-- FAQ Accordion -->
                <div class="max-w-3xl mx-auto">
                    <template v-for="(items, category) in filteredCategories" :key="category">
                        <div v-show="activeTab === category || searchQuery" class="space-y-3 sm:space-y-4">
                            <!-- Category Header (when searching) -->
                            <div v-if="searchQuery" class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-6">
                                <div :class="['w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg bg-gradient-to-br', getConfig(category).gradient]">
                                    <component :is="getConfig(category).icon" class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </div>
                                <div>
                                    <h3 class="text-sm sm:text-base font-bold text-gray-800">{{ category }}</h3>
                                    <p class="text-[10px] sm:text-xs text-gray-500">{{ items.length }} pertanyaan ditemukan</p>
                                </div>
                            </div>

                            <!-- FAQ Items -->
                            <div 
                                v-for="(item, index) in items" 
                                :key="`${category}-${index}`"
                                class="faq-item group"
                            >
                                <div :class="[
                                    'relative bg-white rounded-xl sm:rounded-2xl overflow-hidden transition-all duration-300',
                                    openFaq === `${category}-${index}` 
                                        ? 'shadow-xl ring-2 ring-emerald-200' 
                                        : 'shadow-md hover:shadow-lg hover:-translate-y-0.5 border border-gray-100'
                                ]">
                                    <!-- Question Button -->
                                    <button 
                                        @click="toggleFaq(`${category}-${index}`)" 
                                        class="w-full p-3 sm:p-4 md:p-5 flex items-center justify-between text-left"
                                    >
                                        <div class="flex items-center gap-2 sm:gap-3 md:gap-4 flex-1 min-w-0">
                                            <!-- Number Badge -->
                                            <div :class="[
                                                'flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 rounded-lg flex items-center justify-center text-[10px] sm:text-xs font-bold transition-all duration-300',
                                                openFaq === `${category}-${index}` 
                                                    ? `bg-gradient-to-br ${getConfig(category).gradient} text-white shadow-md` 
                                                    : 'bg-gray-100 text-gray-500 group-hover:bg-emerald-50 group-hover:text-emerald-600'
                                            ]">
                                                {{ String(index + 1).padStart(2, '0') }}
                                            </div>
                                            
                                            <!-- Question Text -->
                                            <h3 :class="[
                                                'text-xs sm:text-sm md:text-base font-bold transition-colors pr-2 line-clamp-2',
                                                openFaq === `${category}-${index}` ? 'text-emerald-600' : 'text-gray-900 group-hover:text-emerald-600'
                                            ]">
                                                {{ item.question }}
                                            </h3>
                                        </div>
                                        
                                        <!-- Expand Icon -->
                                        <div :class="[
                                            'flex-shrink-0 w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-lg sm:rounded-xl flex items-center justify-center transition-all duration-300',
                                            openFaq === `${category}-${index}` 
                                                ? 'bg-emerald-500 text-white rotate-180 shadow-lg' 
                                                : 'bg-gray-100 text-gray-500 group-hover:bg-emerald-50 group-hover:text-emerald-600'
                                        ]">
                                            <ChevronDown class="w-3 h-3 sm:w-4 sm:h-4" />
                                        </div>
                                    </button>
                                    
                                    <!-- Answer Content -->
                                    <Transition 
                                        enter-active-class="transition-all duration-300 ease-out overflow-hidden" 
                                        enter-from-class="max-h-0 opacity-0" 
                                        enter-to-class="max-h-[500px] opacity-100" 
                                        leave-active-class="transition-all duration-200 ease-in overflow-hidden" 
                                        leave-from-class="max-h-[500px] opacity-100" 
                                        leave-to-class="max-h-0 opacity-0"
                                    >
                                        <div v-if="openFaq === `${category}-${index}`">
                                            <div class="px-3 sm:px-4 md:px-5 pb-3 sm:pb-4 md:pb-5">
                                                <div class="pt-3 sm:pt-4 border-t border-gray-100">
                                                    <p class="text-gray-600 leading-relaxed text-xs sm:text-sm">
                                                        {{ item.answer }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </Transition>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div v-if="Object.keys(filteredCategories).length === 0" class="text-center py-12 sm:py-16">
                        <div class="relative inline-flex mb-4 sm:mb-6">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-200 to-teal-100 rounded-2xl sm:rounded-3xl blur-xl opacity-50 animate-pulse"></div>
                            <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-emerald-100 to-white flex items-center justify-center border border-emerald-100 shadow-lg">
                                <MessageCircleQuestion class="w-8 h-8 sm:w-10 sm:h-10 text-emerald-300" />
                            </div>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-700 mb-2">{{ searchQuery ? 'Tidak Ditemukan' : 'Belum Ada FAQ' }}</h3>
                        <p class="text-gray-500 text-xs sm:text-sm max-w-sm mx-auto">
                            {{ searchQuery ? 'Coba kata kunci lain atau periksa ejaan Anda' : 'Pertanyaan yang sering diajukan akan ditampilkan di sini.' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact CTA Section -->
        <section class="relative py-12 sm:py-16 lg:py-20 overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900">
                <div class="absolute top-0 right-1/4 w-64 h-64 sm:w-96 sm:h-96 bg-emerald-500/20 rounded-full blur-[100px] animate-pulse"></div>
                <div class="absolute bottom-0 left-1/4 w-48 h-48 sm:w-72 sm:h-72 bg-teal-500/20 rounded-full blur-[80px] animate-pulse" style="animation-delay: 1.5s"></div>
            </div>

            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-2xl mx-auto text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 mb-4 sm:mb-6 rounded-xl sm:rounded-2xl bg-white/10 backdrop-blur-md border border-white/20">
                        <Headphones class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-2 sm:mb-3">Masih ada pertanyaan?</h2>
                    <p class="text-white/60 text-xs sm:text-sm md:text-base mb-6 sm:mb-8 max-w-lg mx-auto">
                        Tim kami siap membantu menjawab semua pertanyaan Anda. Jangan ragu untuk menghubungi kami.
                    </p>
                    <Link 
                        href="/contact" 
                        class="group relative inline-flex items-center gap-2 sm:gap-3 px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl text-white font-semibold text-sm shadow-xl hover:shadow-2xl transition-all hover:-translate-y-1 hover:scale-105 overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <Mail class="relative w-4 h-4 sm:w-5 sm:h-5" />
                        <span class="relative text-xs sm:text-sm">Hubungi Kami</span>
                        <ArrowRight class="relative w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-1 transition-transform" />
                    </Link>
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
.hero-item, .float-shape {
    will-change: transform, opacity;
}
</style>
