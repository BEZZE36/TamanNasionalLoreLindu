<script setup>
/**
 * Index.vue - My Tickets Page
 * Premium redesign matching destination hero design
 * Modern, animated, responsive
 */
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { 
    Ticket, Calendar, ChevronRight, MapPin, CheckCircle, Clock, XCircle, 
    ChevronDown, Star, CreditCard, Sparkles, ArrowRight, Eye, Download,
    Search, Filter, CalendarDays, TrendingUp, AlertCircle
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

defineOptions({ layout: PublicLayout });

const props = defineProps({
    bookings: Object
});

const page = usePage();
const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const counters = ref({ total: 0, active: 0, pending: 0 });
let ctx;

// Computed stats
const stats = computed(() => {
    const data = props.bookings?.data || [];
    return {
        total: data.length,
        active: data.filter(b => b.status === 'paid' || b.status === 'confirmed').length,
        pending: data.filter(b => b.status === 'pending').length
    };
});

const scrollToContent = () => {
    gsap.to(window, { duration: 1, scrollTo: { y: '#tickets-content', offsetY: 80 }, ease: 'power3.inOut' });
};

const getStatusConfig = (status) => {
    const configs = {
        paid: { 
            class: 'bg-emerald-100 text-emerald-700 border-emerald-200', 
            icon: CheckCircle,
            gradient: 'from-emerald-500 to-teal-500'
        },
        confirmed: { 
            class: 'bg-emerald-100 text-emerald-700 border-emerald-200', 
            icon: CheckCircle,
            gradient: 'from-emerald-500 to-teal-500'
        },
        pending: { 
            class: 'bg-amber-100 text-amber-700 border-amber-200', 
            icon: Clock,
            gradient: 'from-amber-500 to-orange-500'
        },
        cancelled: { 
            class: 'bg-red-100 text-red-700 border-red-200', 
            icon: XCircle,
            gradient: 'from-red-500 to-rose-500'
        },
        expired: { 
            class: 'bg-red-100 text-red-700 border-red-200', 
            icon: XCircle,
            gradient: 'from-red-500 to-rose-500'
        },
        used: { 
            class: 'bg-blue-100 text-blue-700 border-blue-200', 
            icon: CheckCircle,
            gradient: 'from-blue-500 to-indigo-500'
        }
    };
    return configs[status] || { 
        class: 'bg-gray-100 text-gray-700 border-gray-200', 
        icon: AlertCircle,
        gradient: 'from-gray-500 to-slate-500'
    };
};

onMounted(() => {
    ctx = gsap.context(() => {
        const tl = gsap.timeline({ delay: 0.3 });
        
        // Hero items entrance
        tl.fromTo('.hero-item', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );

        // Counter animations
        gsap.to(counters.value, {
            total: stats.value.total,
            active: stats.value.active,
            pending: stats.value.pending,
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

        // Content section animations
        gsap.fromTo('.content-card', 
            { opacity: 0, y: 40 }, 
            { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out',
              scrollTrigger: { trigger: '#tickets-content', start: 'top 85%', toggleActions: 'play none none none' }
            }
        );

        // Table rows stagger animation
        gsap.fromTo('.ticket-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out',
              scrollTrigger: { trigger: '#tickets-content', start: 'top 80%', toggleActions: 'play none none none' }
            }
        );
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <Head title="Tiket Saya" />

    <div class="min-h-screen bg-gray-50">
        <!-- Premium Hero Section -->
        <section ref="heroRef" class="relative min-h-[70vh] flex items-center justify-center overflow-hidden pt-16 pb-12">
            <!-- Premium Gradient Background -->
            <div ref="backgroundRef" class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900"></div>
                
                <!-- Animated Mesh Gradient -->
                <div class="absolute inset-0 opacity-60">
                    <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(16,185,129,0.15),transparent_50%)]"></div>
                    <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(6,182,212,0.12),transparent_50%)]"></div>
                    <div class="absolute bottom-0 left-1/2 w-full h-full bg-[radial-gradient(ellipse_at_50%_100%,rgba(59,130,246,0.08),transparent_50%)]"></div>
                </div>

                <!-- Floating Geometric Shapes -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="float-shape absolute top-[15%] left-[8%] w-20 h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                    <div class="float-shape absolute top-[25%] right-[12%] w-14 h-14 border border-emerald-500/10 rounded-full"></div>
                    <div class="float-shape absolute top-[55%] left-[15%] w-10 h-10 bg-gradient-to-br from-cyan-500/5 to-teal-500/5 rounded-lg rotate-45"></div>
                    <div class="float-shape absolute bottom-[25%] right-[8%] w-16 h-16 border border-cyan-500/[0.08] rounded-xl -rotate-12"></div>
                    <div class="float-shape absolute top-[40%] left-[60%] w-12 h-12 border border-teal-500/10 rounded-lg rotate-6"></div>
                    <div class="float-shape absolute bottom-[40%] left-[25%] w-8 h-8 bg-gradient-to-br from-emerald-500/5 to-cyan-500/5 rounded-full"></div>
                </div>

                <!-- Subtle Grid Pattern -->
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

                <!-- Premium Wave SVG -->
                <svg class="wave-layer absolute -bottom-1 left-0 right-0 w-full h-44" viewBox="0 0 1440 200" fill="none" preserveAspectRatio="none">
                    <path d="M0,120L60,110C120,100,240,80,360,85C480,90,600,120,720,130C840,140,960,130,1080,115C1200,100,1320,80,1380,70L1440,60L1440,200L0,200Z" fill="rgba(255,255,255,0.02)"/>
                    <path d="M0,140L48,135C96,130,192,120,288,115C384,110,480,110,576,120C672,130,768,150,864,155C960,160,1056,150,1152,135C1248,120,1344,100,1392,90L1440,80L1440,200L0,200Z" fill="rgba(16,185,129,0.03)"/>
                    <path d="M0,160L60,155C120,150,240,140,360,145C480,150,600,170,720,175C840,180,960,170,1080,160C1200,150,1320,140,1380,135L1440,130L1440,200L0,200Z" fill="#f9fafb"/>
                </svg>
            </div>

            <!-- Content -->
            <div ref="contentRef" class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <!-- Badge -->
                    <div class="hero-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-4 hover:bg-white/15 hover:border-white/30 hover:scale-105 transition-all duration-300">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] font-semibold tracking-wide">Kelola Tiket Anda</span>
                        <Ticket class="w-3 h-3 text-emerald-400" />
                    </div>

                    <!-- Title -->
                    <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-2">
                        Tiket
                    </h1>
                    <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-300 bg-clip-text text-transparent mb-4">
                        Saya
                    </h1>

                    <!-- Description -->
                    <p class="hero-item text-xs sm:text-sm text-white/60 max-w-xl mx-auto mb-8 leading-relaxed">
                        Kelola semua tiket dan pemesanan wisata Anda di satu tempat. Lihat status, download tiket, dan track kunjungan.
                    </p>

                    <!-- Stats Cards -->
                    <div class="hero-item grid grid-cols-3 gap-3 max-w-md mx-auto mb-8">
                        <!-- Total Tiket -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-blue-500/15 hover:border-blue-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 cursor-default">
                            <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-blue-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-500/30 transition-all duration-300">
                                <Ticket class="w-4 h-4 text-blue-300" />
                            </div>
                            <p class="text-lg sm:text-xl font-black text-white group-hover:text-blue-200 transition-colors">{{ Math.round(counters.total) }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Total Tiket</p>
                        </div>

                        <!-- Tiket Aktif -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-emerald-500/15 hover:border-emerald-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300 cursor-default">
                            <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-emerald-500/30 transition-all duration-300">
                                <CheckCircle class="w-4 h-4 text-emerald-300" />
                            </div>
                            <p class="text-lg sm:text-xl font-black text-white group-hover:text-emerald-200 transition-colors">{{ Math.round(counters.active) }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Tiket Aktif</p>
                        </div>

                        <!-- Menunggu -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-3 hover:bg-amber-500/15 hover:border-amber-400/30 hover:-translate-y-1 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300 cursor-default">
                            <div class="w-8 h-8 mx-auto mb-1.5 rounded-lg bg-amber-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-amber-500/30 transition-all duration-300">
                                <Clock class="w-4 h-4 text-amber-300" />
                            </div>
                            <p class="text-lg sm:text-xl font-black text-white group-hover:text-amber-200 transition-colors">{{ Math.round(counters.pending) }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/60 uppercase tracking-wider group-hover:text-white/80 transition-colors">Menunggu</p>
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

        <!-- Content Section -->
        <section id="tickets-content" class="py-12 sm:py-16 lg:py-20 bg-gradient-to-b from-gray-50 to-white min-h-[60vh] relative overflow-hidden">
            <!-- Background Elements -->
            <div class="absolute top-0 right-0 w-72 h-72 bg-emerald-500/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
            
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Quick Actions -->
                <div class="content-card mb-8">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-1">Daftar Tiket</h2>
                            <p class="text-xs sm:text-sm text-gray-500">Kelola semua pemesanan tiket Anda</p>
                        </div>
                        <Link 
                            href="/destinations" 
                            class="group inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-xs sm:text-sm shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300"
                        >
                            <Sparkles class="w-4 h-4" />
                            <span>Pesan Tiket Baru</span>
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                        </Link>
                    </div>
                </div>

                <!-- Tickets Card -->
                <div class="content-card bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Table with data -->
                    <template v-if="bookings?.data?.length > 0">
                        <!-- Desktop Table -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Order</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Destinasi</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Kunjungan</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(booking, index) in bookings.data" :key="booking.id"
                                        class="ticket-row group hover:bg-gradient-to-r hover:from-gray-50/50 hover:to-emerald-50/30 transition-all duration-300">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                                    <Ticket class="w-5 h-5 text-white" />
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 text-sm">{{ booking.order_number }}</p>
                                                    <p class="text-[10px] text-gray-400">{{ new Date(booking.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <img :src="booking.destination?.primary_image_url || '/assets/logo.png'" 
                                                     :alt="booking.destination?.name"
                                                     class="w-12 h-12 rounded-xl object-cover ring-2 ring-gray-100 shadow-sm group-hover:ring-emerald-200 transition-all">
                                                <div>
                                                    <span class="font-semibold text-gray-900 text-sm group-hover:text-emerald-600 transition-colors">{{ booking.destination?.name }}</span>
                                                    <p class="text-[10px] text-gray-400 flex items-center gap-1 mt-0.5">
                                                        <MapPin class="w-3 h-3" />
                                                        Lore Lindu
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                                                    <CalendarDays class="w-4 h-4 text-gray-500 group-hover:text-emerald-600 transition-colors" />
                                                </div>
                                                <span class="text-gray-900 text-sm font-medium">{{ booking.formatted_visit_date }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold border', getStatusConfig(booking.status).class]">
                                                <component :is="getStatusConfig(booking.status).icon" class="w-3 h-3" />
                                                {{ booking.status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <p class="font-bold text-gray-900 text-sm">{{ booking.formatted_total_amount }}</p>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center justify-end gap-2">
                                                <Link :href="`/my-bookings/${booking.order_number}`" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-emerald-50 to-teal-50 text-emerald-600 rounded-lg hover:from-emerald-100 hover:to-teal-100 border border-emerald-100 transition-all font-semibold text-xs group/btn">
                                                    <Eye class="w-3.5 h-3.5" />
                                                    <span>Detail</span>
                                                    <ChevronRight class="w-3.5 h-3.5 group-hover/btn:translate-x-0.5 transition-transform" />
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="md:hidden divide-y divide-gray-100">
                            <div v-for="booking in bookings.data" :key="booking.id"
                                 class="ticket-row p-4 hover:bg-gray-50/80 transition-colors">
                                <div class="flex items-start gap-3 mb-3">
                                    <img :src="booking.destination?.primary_image_url || '/assets/logo.png'" 
                                         :alt="booking.destination?.name"
                                         class="w-16 h-16 rounded-xl object-cover ring-2 ring-gray-100 shadow-sm">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 text-sm truncate">{{ booking.destination?.name }}</p>
                                        <p class="text-[10px] text-gray-500 font-medium">{{ booking.order_number }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold border', getStatusConfig(booking.status).class]">
                                                <component :is="getStatusConfig(booking.status).icon" class="w-2.5 h-2.5" />
                                                {{ booking.status_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-3 text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <CalendarDays class="w-3.5 h-3.5" />
                                            {{ booking.formatted_visit_date }}
                                        </span>
                                        <span class="font-bold text-gray-900">{{ booking.formatted_total_amount }}</span>
                                    </div>
                                    <Link :href="`/my-bookings/${booking.order_number}`" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg font-semibold text-[10px]">
                                        Detail
                                        <ChevronRight class="w-3 h-3" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="bookings.links && bookings.links.length > 3" class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gradient-to-r from-gray-50 to-emerald-50/30 flex flex-wrap justify-center gap-1.5 sm:gap-2">
                            <template v-for="link in bookings.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url"
                                    :class="['px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all duration-300', 
                                             link.active 
                                                ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25' 
                                                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 hover:border-emerald-200']"
                                    v-html="link.label" />
                                <span v-else class="px-3 sm:px-4 py-2 text-gray-300 text-xs sm:text-sm font-medium" v-html="link.label" />
                            </template>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16 sm:py-20 lg:py-24 px-4">
                        <div class="relative w-24 h-24 sm:w-28 sm:h-28 mx-auto mb-6">
                            <!-- Animated rings -->
                            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-emerald-400 to-teal-400 opacity-20 animate-ping"></div>
                            <div class="absolute inset-2 rounded-full bg-gradient-to-r from-emerald-100 to-teal-100"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-2xl shadow-emerald-500/30 rotate-3">
                                    <Ticket class="w-10 h-10 sm:w-12 sm:h-12 text-white" />
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Belum Ada Tiket</h3>
                        <p class="text-gray-500 text-sm mb-8 max-w-md mx-auto leading-relaxed">
                            Anda belum memiliki tiket yang dipesan. Mulai jelajahi destinasi wisata menakjubkan di Taman Nasional Lore Lindu!
                        </p>
                        
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <Link href="/destinations" 
                                class="group inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:shadow-xl hover:shadow-emerald-500/30 hover:-translate-y-0.5 transition-all duration-300">
                                <MapPin class="w-5 h-5" />
                                <span>Jelajahi Destinasi</span>
                                <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                            </Link>
                            <Link href="/dashboard" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 hover:border-emerald-200 transition-all duration-300">
                                <TrendingUp class="w-5 h-5" />
                                <span>Ke Dashboard</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes scroll-pulse {
    0%, 100% { opacity: 1; transform: translateY(0); }
    50% { opacity: 0.3; transform: translateY(6px); }
}
.animate-scroll-pulse { animation: scroll-pulse 1.5s ease-in-out infinite; }
</style>
