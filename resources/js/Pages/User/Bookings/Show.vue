<script setup>
/**
 * Show.vue - Ticket Detail Page
 * Premium redesign matching destination hero design
 * Modern, animated, responsive
 */
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { 
    Ticket, Calendar, MapPin, Users, CreditCard, CheckCircle, Clock, 
    XCircle, Download, Printer, ArrowLeft, QrCode, User, Mail, Phone,
    AlertCircle, Loader2, Sparkles, FileText, ChevronRight, Shield,
    CalendarDays, DollarSign, Receipt, Share2, Copy, Check
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

defineOptions({ layout: PublicLayout });

const props = defineProps({
    booking: Object
});

// Refs
const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const isDownloadingPdf = ref(false);
const copied = ref(false);
let ctx;

// Normalize legacy statuses to new 4 statuses
const normalizeStatus = (status) => {
    const mapping = {
        'pending': 'pending',
        'awaiting_cash': 'pending',
        'paid': 'confirmed',
        'confirmed': 'confirmed',
        'used': 'used',
        'cancelled': 'cancelled',
        'expired': 'cancelled',
        'refunded': 'cancelled'
    };
    return mapping[status] || status;
};

const normalizedStatus = computed(() => normalizeStatus(props.booking?.status));

// Computed
const statusConfig = computed(() => {
    const status = normalizedStatus.value;
    const configs = {
        confirmed: { 
            class: 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30', 
            icon: CheckCircle,
            label: 'Terkonfirmasi',
            color: 'emerald'
        },
        pending: { 
            class: 'bg-amber-500/20 text-amber-300 border-amber-400/30', 
            icon: Clock,
            label: 'Menunggu Pembayaran',
            color: 'amber'
        },
        cancelled: { 
            class: 'bg-red-500/20 text-red-300 border-red-400/30', 
            icon: XCircle,
            label: 'Dibatalkan',
            color: 'red'
        },
        used: { 
            class: 'bg-blue-500/20 text-blue-300 border-blue-400/30', 
            icon: Check,
            label: 'Sudah Digunakan',
            color: 'blue'
        }
    };
    return configs[status] || { 
        class: 'bg-gray-500/20 text-gray-300 border-gray-400/30', 
        icon: AlertCircle,
        label: props.booking?.status,
        color: 'gray'
    };
});

const isPaid = computed(() => ['confirmed', 'paid'].includes(props.booking?.status));

// Functions
const scrollToContent = () => {
    gsap.to(window, { duration: 1, scrollTo: { y: '#ticket-content', offsetY: 80 }, ease: 'power3.inOut' });
};

const downloadTicketPdf = async () => {
    isDownloadingPdf.value = true;
    try {
        // Use backend route for PDF generation - correct route is /booking/{order}/ticket
        window.location.href = `/booking/${props.booking.order_number}/ticket`;
    } catch (error) {
        console.error('Error downloading PDF:', error);
        alert('Gagal mengunduh PDF. Silakan coba lagi.');
    } finally {
        setTimeout(() => {
            isDownloadingPdf.value = false;
        }, 2000);
    }
};

const printInvoice = () => {
    window.location.href = `/booking/${props.booking.order_number}/invoice`;
};

const copyOrderNumber = async () => {
    try {
        await navigator.clipboard.writeText(props.booking?.order_number);
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { 
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' 
    });
};

onMounted(() => {
    if (!props.booking) return;
    
    ctx = gsap.context(() => {
        const tl = gsap.timeline({ delay: 0.3 });
        
        tl.fromTo('.hero-item', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );

        // Floating shapes animation
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

        // Content cards entrance
        gsap.fromTo('.content-card', 
            { opacity: 0, y: 30 }, 
            { 
                opacity: 1, y: 0, 
                duration: 0.6, 
                stagger: 0.15, 
                ease: 'power3.out',
                scrollTrigger: { trigger: '#ticket-content', start: 'top 85%' }
            }
        );
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <Head :title="`Detail Tiket ${booking?.order_number || ''}`" />

    <div v-if="booking" class="min-h-screen bg-gray-50">
        <!-- Premium Hero Section -->
        <section ref="heroRef" class="relative min-h-[75vh] sm:min-h-[80vh] flex items-center justify-center overflow-hidden">
            <!-- Background with gradient and effects -->
            <div ref="backgroundRef" class="absolute inset-0">
                <!-- Dark gradient base - BLUE/INDIGO theme for detail page -->
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900"></div>
                
                <!-- Animated Mesh Gradient - Blue/Purple tones -->
                <div class="absolute inset-0 opacity-60">
                    <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(99,102,241,0.15),transparent_50%)]"></div>
                    <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(139,92,246,0.12),transparent_50%)]"></div>
                    <div class="absolute bottom-0 left-1/2 w-full h-full bg-[radial-gradient(ellipse_at_50%_100%,rgba(59,130,246,0.08),transparent_50%)]"></div>
                </div>

                <!-- Floating Geometric Shapes - Blue/Indigo theme -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="float-shape absolute top-[15%] left-[8%] w-16 h-16 sm:w-20 sm:h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                    <div class="float-shape absolute top-[25%] right-[12%] w-10 h-10 sm:w-14 sm:h-14 border border-indigo-500/10 rounded-full"></div>
                    <div class="float-shape absolute top-[55%] left-[15%] w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-violet-500/5 to-indigo-500/5 rounded-lg rotate-45"></div>
                    <div class="float-shape absolute bottom-[25%] right-[8%] w-12 h-12 sm:w-16 sm:h-16 border border-blue-500/[0.08] rounded-xl -rotate-12"></div>
                    <div class="float-shape absolute top-[40%] left-[50%] w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-indigo-500/5 to-transparent rounded-full"></div>
                </div>

                <!-- Subtle Grid Pattern -->
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

                <!-- Premium Wave SVG - Indigo tint -->
                <svg class="wave-layer absolute -bottom-1 left-0 right-0 w-full h-32 sm:h-40 md:h-44" viewBox="0 0 1440 200" fill="none" preserveAspectRatio="none">
                    <path d="M0,120L60,110C120,100,240,80,360,85C480,90,600,120,720,130C840,140,960,130,1080,115C1200,100,1320,80,1380,70L1440,60L1440,200L0,200Z" fill="rgba(255,255,255,0.02)"/>
                    <path d="M0,140L48,135C96,130,192,120,288,115C384,110,480,110,576,120C672,130,768,150,864,155C960,160,1056,150,1152,135C1248,120,1344,100,1392,90L1440,80L1440,200L0,200Z" fill="rgba(99,102,241,0.03)"/>
                    <path d="M0,160L60,155C120,150,240,140,360,145C480,150,600,170,720,175C840,180,960,170,1080,160C1200,150,1320,140,1380,135L1440,130L1440,200L0,200Z" fill="#f9fafb"/>
                </svg>
            </div>

            <!-- Hero Content -->
            <div ref="contentRef" class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-24">
                <div class="text-center">
                    <!-- Breadcrumb -->
                    <nav class="hero-item flex items-center justify-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs text-white/50 mb-4 sm:mb-5">
                        <Link href="/my-bookings" class="hover:text-white/80 transition-colors flex items-center gap-1">
                            <Ticket class="w-3 h-3" />
                            Tiket Saya
                        </Link>
                        <ChevronRight class="w-3 h-3" />
                        <span class="text-white/80">{{ booking.order_number }}</span>
                    </nav>

                    <!-- Status Badge -->
                    <div class="hero-item inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full border backdrop-blur-md mb-4 sm:mb-5" 
                         :class="statusConfig.class">
                        <component :is="statusConfig.icon" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        <span class="text-[10px] sm:text-xs font-bold tracking-wide">{{ statusConfig.label }}</span>
                    </div>

                    <!-- Title -->
                    <h1 class="hero-item text-lg sm:text-xl md:text-2xl lg:text-3xl font-black text-white leading-tight mb-2 sm:mb-3">
                        Detail Tiket
                    </h1>
                    
                    <!-- Order Number with Copy -->
                    <div class="hero-item flex items-center justify-center gap-2 mb-4 sm:mb-5">
                        <span class="text-sm sm:text-base md:text-lg font-mono font-bold bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-300 bg-clip-text text-transparent">
                            {{ booking.order_number }}
                        </span>
                        <button @click="copyOrderNumber" 
                                class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors"
                                :title="copied ? 'Tersalin!' : 'Salin'">
                            <Check v-if="copied" class="w-3.5 h-3.5 text-emerald-400" />
                            <Copy v-else class="w-3.5 h-3.5 text-white/60" />
                        </button>
                    </div>

                    <!-- Destination Name -->
                    <p class="hero-item text-white/60 text-xs sm:text-sm mb-6 sm:mb-8 flex items-center justify-center gap-1.5">
                        <MapPin class="w-3.5 h-3.5 text-emerald-400" />
                        {{ booking.destination?.name }}
                    </p>

                    <!-- Quick Info Cards -->
                    <div class="hero-item grid grid-cols-3 gap-2 sm:gap-3 max-w-sm sm:max-w-md mx-auto mb-8 sm:mb-10">
                        <!-- Visit Date -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2.5 sm:p-3 hover:bg-emerald-500/15 hover:border-emerald-400/30 hover:-translate-y-1 transition-all duration-300 cursor-default">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 mx-auto mb-1.5 rounded-lg bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <CalendarDays class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-300" />
                            </div>
                            <p class="text-[9px] sm:text-[10px] text-white font-bold truncate">{{ booking.formatted_visit_date?.split(',')?.[1]?.trim() || '-' }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/50 uppercase tracking-wider">Kunjungan</p>
                        </div>

                        <!-- Visitors -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2.5 sm:p-3 hover:bg-teal-500/15 hover:border-teal-400/30 hover:-translate-y-1 transition-all duration-300 cursor-default">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 mx-auto mb-1.5 rounded-lg bg-teal-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <Users class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-teal-300" />
                            </div>
                            <p class="text-[9px] sm:text-[10px] text-white font-bold">{{ booking.total_visitors }} Orang</p>
                            <p class="text-[7px] sm:text-[8px] text-white/50 uppercase tracking-wider">Pengunjung</p>
                        </div>

                        <!-- Total -->
                        <div class="group bg-white/10 backdrop-blur-md border border-white/15 rounded-lg sm:rounded-xl p-2.5 sm:p-3 hover:bg-cyan-500/15 hover:border-cyan-400/30 hover:-translate-y-1 transition-all duration-300 cursor-default">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 mx-auto mb-1.5 rounded-lg bg-cyan-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <Receipt class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-cyan-300" />
                            </div>
                            <p class="text-[9px] sm:text-[10px] text-white font-bold truncate">{{ booking.formatted_total_amount }}</p>
                            <p class="text-[7px] sm:text-[8px] text-white/50 uppercase tracking-wider">Total</p>
                        </div>
                    </div>

                    <!-- Scroll Indicator -->
                    <button @click="scrollToContent" class="hero-item flex flex-col items-center gap-1.5 group cursor-pointer mx-auto">
                        <span class="text-[8px] sm:text-[9px] text-white/30 uppercase tracking-[0.15em] font-medium group-hover:text-white/50 transition-colors">Detail</span>
                        <div class="relative w-4 h-6 sm:w-5 sm:h-8 rounded-full border border-white/20 group-hover:border-white/40 flex justify-center pt-1.5 transition-colors">
                            <div class="w-0.5 h-1.5 sm:w-1 sm:h-2 bg-white/40 group-hover:bg-white/60 rounded-full animate-scroll-pulse transition-colors"></div>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section id="ticket-content" class="py-10 sm:py-14 lg:py-16 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
            <!-- Background decoration -->
            <div class="absolute top-0 right-0 w-64 sm:w-96 h-64 sm:h-96 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 sm:w-80 h-64 sm:h-80 bg-teal-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Main Column -->
                    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                        <!-- Cash Payment QR Code (if pending) -->
                        <div v-if="normalizedStatus === 'pending'" 
                             class="content-card bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl sm:rounded-3xl shadow-xl border-2 border-amber-200 p-4 sm:p-6 lg:p-8">
                            <div class="flex items-center justify-between mb-4 sm:mb-6">
                                <h3 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25">
                                        <QrCode class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                    </div>
                                    QR Code Pembayaran Tunai
                                </h3>
                                <span class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-[9px] sm:text-[10px] font-bold border border-amber-200">
                                    <Clock class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                    Menunggu Bayar
                                </span>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 bg-white rounded-xl sm:rounded-2xl border-2 border-amber-100 p-4 sm:p-6">
                                <!-- QR Code for Order -->
                                <div class="flex-shrink-0 bg-white p-3 sm:p-4 rounded-xl shadow-lg border border-gray-100">
                                    <!-- Generate QR code from order_number -->
                                    <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(booking.order_number)}`" 
                                         :alt="`QR Code ${booking.order_number}`"
                                         class="w-28 h-28 sm:w-32 sm:h-32 object-contain">
                                    <p class="mt-2 text-center text-[10px] sm:text-xs font-mono font-bold text-amber-600 bg-amber-50 rounded-lg py-1">
                                        {{ booking.order_number }}
                                    </p>
                                </div>

                                <!-- Instructions -->
                                <div class="flex-1 text-center sm:text-left space-y-2 sm:space-y-3">
                                    <h4 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">{{ booking.destination?.name }}</h4>
                                    <div class="space-y-1.5">
                                        <p class="text-xs sm:text-sm text-gray-600 flex items-center justify-center sm:justify-start gap-1.5">
                                            <CreditCard class="w-3.5 h-3.5 text-amber-500" />
                                            Total: <span class="font-bold text-gray-900">{{ booking.formatted_total_amount }}</span>
                                        </p>
                                        <p class="text-xs sm:text-sm text-gray-600 flex items-center justify-center sm:justify-start gap-1.5">
                                            <CalendarDays class="w-3.5 h-3.5 text-amber-500" />
                                            Kunjungan: <span class="font-semibold text-gray-900">{{ formatDate(booking.visit_date) }}</span>
                                        </p>
                                    </div>
                                    <div class="pt-2 sm:pt-3 border-t border-amber-100">
                                        <p class="text-[10px] sm:text-xs text-amber-700 flex items-center justify-center sm:justify-start gap-1.5 bg-amber-50 rounded-lg px-3 py-2">
                                            <AlertCircle class="w-3.5 h-3.5" />
                                            Tunjukkan QR Code ini ke petugas kasir untuk pembayaran
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- E-Ticket Card (if paid) -->
                        <div v-if="isPaid && booking.tickets?.length > 0" 
                             class="content-card bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl sm:rounded-3xl shadow-xl border-2 border-emerald-100 p-4 sm:p-6 lg:p-8">
                            <div class="flex items-center justify-between mb-4 sm:mb-6">
                                <h3 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                        <QrCode class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                    </div>
                                    E-Ticket Anda
                                </h3>
                                <span class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[9px] sm:text-[10px] font-bold border border-emerald-200">
                                    <Shield class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                    Terverifikasi
                                </span>
                            </div>

                            <div v-for="ticket in booking.tickets" :key="ticket.id"
                                 :class="['relative rounded-xl sm:rounded-2xl border-2 p-4 sm:p-6 mb-4 last:mb-0 transition-all duration-300',
                                         ticket.status === 'used' ? 'bg-gray-100 border-gray-200 opacity-75' : 'bg-white border-emerald-100 hover:border-emerald-300 hover:shadow-lg']">
                                
                                <!-- Used Overlay -->
                                <div v-if="ticket.status === 'used'" class="absolute inset-0 flex items-center justify-center bg-gray-100/80 rounded-xl sm:rounded-2xl">
                                    <span class="px-4 py-2 rounded-full bg-gray-200 text-gray-600 text-xs font-bold">Sudah Digunakan</span>
                                </div>

                                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                                    <!-- QR Code -->
                                    <div class="flex-shrink-0 bg-white p-3 sm:p-4 rounded-xl shadow-lg border border-gray-100">
                                        <img v-if="ticket.qr_code_url" 
                                             :src="ticket.qr_code_url" 
                                             :alt="`QR Code ${ticket.ticket_code}`"
                                             class="w-28 h-28 sm:w-32 sm:h-32 object-contain">
                                        <div v-else class="w-28 h-28 sm:w-32 sm:h-32 bg-gray-100 flex items-center justify-center rounded-lg">
                                            <QrCode class="w-12 h-12 text-gray-300" />
                                        </div>
                                        <p class="mt-2 text-center text-[10px] sm:text-xs font-mono font-bold text-emerald-600 bg-emerald-50 rounded-lg py-1">
                                            {{ ticket.ticket_code }}
                                        </p>
                                    </div>

                                    <!-- Ticket Info -->
                                    <div class="flex-1 text-center sm:text-left space-y-2 sm:space-y-3">
                                        <h4 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">{{ booking.destination?.name }}</h4>
                                        <div class="space-y-1.5">
                                            <p class="text-xs sm:text-sm text-gray-600 flex items-center justify-center sm:justify-start gap-1.5">
                                                <CalendarDays class="w-3.5 h-3.5 text-emerald-500" />
                                                Berlaku: <span class="font-semibold text-gray-900">{{ formatDate(ticket.valid_date) }}</span>
                                            </p>
                                            <p class="text-xs sm:text-sm text-gray-600 flex items-center justify-center sm:justify-start gap-1.5">
                                                <Users class="w-3.5 h-3.5 text-emerald-500" />
                                                Pengunjung: <span class="font-semibold text-gray-900">{{ ticket.total_persons }} Orang</span>
                                            </p>
                                        </div>
                                        <div class="pt-2 sm:pt-3 border-t border-emerald-100">
                                            <p class="text-[10px] sm:text-xs text-emerald-600 flex items-center justify-center sm:justify-start gap-1.5 bg-emerald-50 rounded-lg px-3 py-2">
                                                <AlertCircle class="w-3.5 h-3.5" />
                                                Scan QR Code ini pada petugas di gerbang masuk
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visitor Information -->
                        <div class="content-card bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 p-4 sm:p-6 lg:p-8">
                            <h3 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900 mb-4 sm:mb-6 flex items-center gap-2">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                                    <User class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </div>
                                Informasi Pengunjung
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div class="group p-3 sm:p-4 rounded-xl bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 transition-all duration-300">
                                    <p class="text-[10px] sm:text-xs text-gray-500 mb-1 flex items-center gap-1">
                                        <User class="w-3 h-3" />
                                        Nama Ketua Rombongan
                                    </p>
                                    <p class="text-xs sm:text-sm font-semibold text-gray-900">{{ booking.leader_name }}</p>
                                </div>
                                <div class="group p-3 sm:p-4 rounded-xl bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 transition-all duration-300">
                                    <p class="text-[10px] sm:text-xs text-gray-500 mb-1 flex items-center gap-1">
                                        <Mail class="w-3 h-3" />
                                        Email
                                    </p>
                                    <p class="text-xs sm:text-sm font-semibold text-gray-900 truncate">{{ booking.leader_email }}</p>
                                </div>
                                <div class="group p-3 sm:p-4 rounded-xl bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 transition-all duration-300">
                                    <p class="text-[10px] sm:text-xs text-gray-500 mb-1 flex items-center gap-1">
                                        <Phone class="w-3 h-3" />
                                        Nomor HP
                                    </p>
                                    <p class="text-xs sm:text-sm font-semibold text-gray-900">{{ booking.leader_phone }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Summary -->
                        <div class="content-card bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 p-4 sm:p-6 lg:p-8">
                            <h3 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900 mb-4 sm:mb-6 flex items-center gap-2">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg shadow-purple-500/25">
                                    <Receipt class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </div>
                                Rincian Pemesanan
                            </h3>

                            <div class="space-y-3 sm:space-y-4">
                                <!-- Visit Details -->
                                <div class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                    <span class="text-xs sm:text-sm text-gray-500">Tanggal Kunjungan</span>
                                    <span class="text-xs sm:text-sm font-semibold text-gray-900">{{ booking.formatted_visit_date }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                    <span class="text-xs sm:text-sm text-gray-500">Jumlah Pengunjung</span>
                                    <span class="text-xs sm:text-sm font-semibold text-gray-900">{{ booking.total_visitors }} orang</span>
                                </div>
                                <div v-if="booking.total_adults > 0" class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                    <span class="text-xs sm:text-sm text-gray-500 ml-4">• Dewasa</span>
                                    <span class="text-xs sm:text-sm text-gray-700">{{ booking.total_adults }} orang</span>
                                </div>
                                <div v-if="booking.total_children > 0" class="flex items-center justify-between py-2 sm:py-3 border-b border-gray-100">
                                    <span class="text-xs sm:text-sm text-gray-500 ml-4">• Anak-anak</span>
                                    <span class="text-xs sm:text-sm text-gray-700">{{ booking.total_children }} orang</span>
                                </div>
                                
                                <!-- Price Breakdown -->
                                <div class="pt-2">
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-xs sm:text-sm text-gray-500">Subtotal</span>
                                        <span class="text-xs sm:text-sm text-gray-700">{{ booking.formatted_subtotal }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-xs sm:text-sm text-gray-500">Biaya Layanan</span>
                                        <span class="text-xs sm:text-sm text-gray-700">{{ booking.formatted_service_fee }}</span>
                                    </div>
                                    <div v-if="booking.discount > 0" class="flex items-center justify-between py-2">
                                        <span class="text-xs sm:text-sm text-emerald-600">Diskon</span>
                                        <span class="text-xs sm:text-sm text-emerald-600">-Rp {{ booking.discount?.toLocaleString('id-ID') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-3 sm:py-4 mt-2 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl px-3 sm:px-4 border border-emerald-100">
                                        <span class="text-sm sm:text-base font-bold text-gray-900">Total Pembayaran</span>
                                        <span class="text-base sm:text-lg font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ booking.formatted_total_amount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-4 sm:space-y-6">
                        <!-- Destination Card -->
                        <div class="content-card bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="relative h-32 sm:h-40">
                                <img :src="booking.destination?.primary_image_url || '/assets/logo.png'" 
                                     :alt="booking.destination?.name"
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-3 left-3 right-3">
                                    <h4 class="font-bold text-white text-sm sm:text-base drop-shadow-lg">{{ booking.destination?.name }}</h4>
                                    <p class="text-white/80 text-[10px] sm:text-xs flex items-center gap-1 mt-0.5">
                                        <MapPin class="w-3 h-3" />
                                        Lore Lindu, Sulawesi Tengah
                                    </p>
                                </div>
                            </div>
                            <div class="p-3 sm:p-4">
                                <Link :href="`/destinations/${booking.destination?.slug}`"
                                      class="block w-full text-center px-4 py-2 sm:py-2.5 rounded-xl bg-gray-100 hover:bg-emerald-100 text-gray-700 hover:text-emerald-700 text-xs sm:text-sm font-semibold transition-colors">
                                    Lihat Destinasi
                                </Link>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div v-if="booking.payment" class="content-card bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 p-4 sm:p-5">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 mb-3 sm:mb-4 flex items-center gap-2">
                                <CreditCard class="w-4 h-4 text-emerald-500" />
                                Info Pembayaran
                            </h4>
                            <div class="space-y-2 sm:space-y-3 text-xs sm:text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Metode</span>
                                    <span class="font-semibold text-gray-900">{{ booking.payment.payment_type_label || booking.payment.payment_type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Status</span>
                                    <span class="font-semibold text-emerald-600 capitalize">{{ booking.payment.status }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Tanggal</span>
                                    <span class="font-semibold text-gray-900">{{ new Date(booking.payment.created_at).toLocaleDateString('id-ID') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="content-card bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 p-4 sm:p-5 space-y-2 sm:space-y-3">
                            <!-- Pending Actions -->
                            <template v-if="normalizedStatus === 'pending'">
                                <Link :href="`/booking/${booking.order_number}/edit`" 
                                      class="flex items-center justify-center gap-2 w-full px-4 py-2.5 sm:py-3 border-2 border-blue-200 text-blue-700 bg-blue-50 font-semibold text-xs sm:text-sm rounded-xl hover:bg-blue-100 hover:border-blue-300 transition-all mb-2">
                                    ✏️ Edit Booking
                                </Link>
                                <Link :href="`/booking/${booking.order_number}/payment`" 
                                      class="flex items-center justify-center gap-2 w-full px-4 py-2.5 sm:py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all">
                                    <CreditCard class="w-4 h-4" />
                                    Lanjut ke Pembayaran
                                </Link>
                            </template>

                            <!-- Confirmed Actions -->
                            <template v-else-if="isPaid">
                                <button disabled
                                        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 sm:py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-xs sm:text-sm rounded-xl cursor-not-allowed">
                                    <CheckCircle class="w-4 h-4" />
                                    Pembayaran Berhasil
                                </button>
                                <button @click="downloadTicketPdf" :disabled="isDownloadingPdf"
                                        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 sm:py-3 border-2 border-emerald-200 text-emerald-700 bg-emerald-50 font-semibold text-xs sm:text-sm rounded-xl hover:bg-emerald-100 hover:border-emerald-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                    <Loader2 v-if="isDownloadingPdf" class="w-4 h-4 animate-spin" />
                                    <Download v-else class="w-4 h-4" />
                                    {{ isDownloadingPdf ? 'Membuat PDF...' : 'Download E-Ticket' }}
                                </button>
                                <button @click="printInvoice"
                                        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 sm:py-3 border-2 border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl hover:bg-gray-100 hover:border-gray-300 transition-all">
                                    <Printer class="w-4 h-4" />
                                    Cetak Invoice
                                </button>
                            </template>

                            <Link href="/my-bookings" 
                                  class="flex items-center justify-center gap-2 w-full px-4 py-2.5 sm:py-3 border-2 border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl hover:bg-gray-100 hover:border-gray-300 transition-all">
                                <ArrowLeft class="w-4 h-4" />
                                Kembali ke Daftar Tiket
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Loading State -->
    <div v-else class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center animate-pulse shadow-lg shadow-emerald-500/25">
                <Ticket class="w-8 h-8 text-white" />
            </div>
            <p class="text-white/60 text-sm">Memuat data tiket...</p>
        </div>
    </div>
</template>

<style scoped>
@keyframes scroll-pulse {
    0%, 100% { opacity: 1; transform: translateY(0); }
    50% { opacity: 0.3; transform: translateY(4px); }
}
.animate-scroll-pulse { animation: scroll-pulse 1.5s ease-in-out infinite; }

/* Performance optimizations */
.hero-item, .float-shape, .content-card {
    will-change: transform, opacity;
}

@media print {
    .no-print { display: none !important; }
}
</style>
