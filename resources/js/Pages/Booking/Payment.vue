<script setup>
/**
 * BookingPayment.vue - Premium Payment Page
 * Modern design with GSAP animations, glassmorphism, and responsive layout
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
    CreditCard, Banknote, Clock, MapPin, Check, ChevronRight,
    Users, Calendar, AlertCircle, Wallet, Building2, Shield,
    QrCode, Smartphone, ArrowLeft, Timer, Copy, CheckCircle
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    booking: Object,
    snapToken: String,
    errorMessage: String,
});

const paymentMethod = ref(props.snapToken ? 'online' : 'cash');
const isProcessing = ref(false);
const copiedCode = ref(false);

// Refs for animations
const heroRef = ref(null);
let ctx;

// Countdown timer state
const countdown = ref({ hours: 23, minutes: 59, seconds: 59 });

// Format WITA date for display
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { 
        weekday: 'long',
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
};

// Copy order number to clipboard
const copyOrderNumber = async () => {
    try {
        await navigator.clipboard.writeText(props.booking.order_number);
        copiedCode.value = true;
        setTimeout(() => copiedCode.value = false, 2000);
    } catch (e) {
        console.error('Failed to copy:', e);
    }
};

// Function to handle Midtrans payment
const payOnline = () => {
    if (window.snap && props.snapToken) {
        isProcessing.value = true;
        window.snap.pay(props.snapToken, {
            onSuccess: function(result) {
                window.location.replace(`/booking/${props.booking.order_number}/success`);
            },
            onPending: function(result) {
                window.location.replace(`/booking/${props.booking.order_number}/success`);
            },
            onError: function(result) {
                isProcessing.value = false;
                alert('Pembayaran gagal. Silakan coba lagi.');
            },
            onClose: function() {
                isProcessing.value = false;
            }
        });
    }
};

// Confirm cash payment
const confirmCash = () => {
    isProcessing.value = true;
    router.post(`/booking/${props.booking.order_number}/confirm-cash`);
};

// Start countdown timer
const startCountdown = () => {
    setInterval(() => {
        if (countdown.value.seconds > 0) {
            countdown.value.seconds--;
        } else if (countdown.value.minutes > 0) {
            countdown.value.minutes--;
            countdown.value.seconds = 59;
        } else if (countdown.value.hours > 0) {
            countdown.value.hours--;
            countdown.value.minutes = 59;
            countdown.value.seconds = 59;
        }
    }, 1000);
};

// Load Midtrans Snap on mount
onMounted(() => {
    if (props.snapToken) {
        const script = document.createElement('script');
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', 'YOUR_CLIENT_KEY');
        document.body.appendChild(script);
    }

    startCountdown();

    ctx = gsap.context(() => {
        // Hero animations
        const tl = gsap.timeline({ delay: 0.2 });
        tl.fromTo('.hero-breadcrumb', 
            { opacity: 0, y: -15 }, 
            { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }
        )
        .fromTo('.hero-badge', 
            { opacity: 0, scale: 0.8 }, 
            { opacity: 1, scale: 1, duration: 0.4, ease: 'back.out(2)' }, 
            '-=0.2'
        )
        .fromTo('.hero-title', 
            { opacity: 0, y: 25, filter: 'blur(8px)' }, 
            { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.5, ease: 'power3.out' }, 
            '-=0.2'
        )
        .fromTo('.hero-step', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }, 
            '-=0.1'
        );

        // Ken Burns effect
        gsap.to('.hero-bg-image', {
            scale: 1.1,
            duration: 15,
            ease: 'none',
            repeat: -1,
            yoyo: true
        });

        // Floating shapes
        gsap.to('.float-shape', {
            y: -15,
            duration: 3,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: { each: 0.5, from: 'random' }
        });

        // Payment cards entrance
        gsap.fromTo('.payment-card', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power2.out', delay: 0.5 }
        );

        // Sidebar entrance
        gsap.fromTo('.sidebar-card', 
            { opacity: 0, x: 30 }, 
            { opacity: 1, x: 0, duration: 0.5, ease: 'power2.out', delay: 0.7 }
        );
    }, heroRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <Head :title="`Pembayaran - ${booking.order_number}`" />

    <div ref="heroRef" class="min-h-screen bg-gray-50">
        <!-- Premium Hero Section -->
        <section class="relative min-h-[45vh] sm:min-h-[50vh] overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img 
                    :src="booking.destination?.primary_image_url || '/images/placeholder-no-image.svg'" 
                    :alt="booking.destination?.name"
                    class="hero-bg-image w-full h-full object-cover"
                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/70 to-slate-900/40"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-900/60 to-transparent"></div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="float-shape absolute top-[15%] right-[10%] w-16 h-16 sm:w-20 sm:h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                <div class="float-shape absolute top-[35%] left-[5%] w-10 h-10 sm:w-14 sm:h-14 border border-blue-500/10 rounded-full"></div>
                <div class="float-shape absolute bottom-[30%] right-[15%] w-8 h-8 sm:w-12 sm:h-12 bg-gradient-to-br from-cyan-500/5 to-teal-500/5 rounded-lg rotate-45"></div>
            </div>

            <!-- Wave Transition -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-20 sm:h-28" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
                <path d="M0,50L60,45C120,40,240,30,360,35C480,40,600,60,720,65C840,70,960,60,1080,50C1200,40,1320,30,1380,25L1440,20L1440,100L0,100Z" fill="#f9fafb"/>
            </svg>

            <!-- Hero Content -->
            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 sm:pt-28 pb-20 sm:pb-24">
                <!-- Back Button -->
                <Link :href="`/book/${booking.destination?.slug}`" 
                    class="hero-breadcrumb inline-flex items-center gap-1.5 text-[10px] sm:text-[11px] text-white/60 mb-4 sm:mb-5 font-medium hover:text-white transition-colors">
                    <ArrowLeft class="w-3.5 h-3.5" />
                    Kembali ke Pemesanan
                </Link>

                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 lg:gap-6">
                    <div class="flex-1">
                        <!-- Badge -->
                        <div class="hero-badge inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white text-[10px] sm:text-[11px] font-semibold mb-3 sm:mb-4">
                            <CreditCard class="w-3.5 h-3.5 text-blue-400" />
                            <span>Pembayaran</span>
                        </div>

                        <!-- Title -->
                        <h1 class="hero-title text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-3 sm:mb-4">
                            Pilih Metode Pembayaran
                        </h1>

                        <!-- Order Number -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-white/60 text-xs sm:text-sm">Order:</span>
                            <button @click="copyOrderNumber" 
                                class="group flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm text-white font-mono text-xs sm:text-sm hover:bg-white/20 transition-all">
                                <span>{{ booking.order_number }}</span>
                                <Copy v-if="!copiedCode" class="w-3 h-3 sm:w-3.5 sm:h-3.5 opacity-60 group-hover:opacity-100 transition-opacity" />
                                <CheckCircle v-else class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-400" />
                            </button>
                        </div>
                    </div>

                    <!-- Progress Steps -->
                    <div class="hero-step flex items-center gap-2 sm:gap-3 bg-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-white/15">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] sm:text-xs font-bold">
                                <Check class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                            </div>
                            <span class="text-emerald-400 text-[10px] sm:text-xs font-medium hidden sm:inline">Isi Data</span>
                        </div>
                        <div class="w-6 sm:w-8 h-0.5 bg-emerald-500"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-[10px] sm:text-xs font-bold shadow-lg shadow-blue-500/30">2</div>
                            <span class="text-white text-[10px] sm:text-xs font-medium hidden sm:inline">Bayar</span>
                        </div>
                        <div class="w-6 sm:w-8 h-0.5 bg-white/20"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 flex items-center justify-center text-white/60 text-[10px] sm:text-xs font-bold">3</div>
                            <span class="text-white/60 text-[10px] sm:text-xs font-medium hidden sm:inline">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section class="py-8 sm:py-12 lg:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                    <!-- Payment Methods -->
                    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                        <!-- Cash Payment Option -->
                        <label class="payment-card block cursor-pointer">
                            <div :class="['relative group rounded-2xl border-2 transition-all duration-300 overflow-hidden',
                                paymentMethod === 'cash' 
                                    ? 'border-emerald-500 bg-emerald-50 shadow-lg shadow-emerald-500/10' 
                                    : 'border-gray-100 bg-white hover:border-gray-200 hover:shadow-md']">
                                <!-- Selection indicator -->
                                <div v-if="paymentMethod === 'cash'" 
                                    class="absolute top-3 right-3 sm:top-4 sm:right-4 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-500 flex items-center justify-center">
                                    <Check class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" />
                                </div>
                                
                                <div class="p-5 sm:p-6">
                                    <div class="flex items-start gap-4">
                                        <input type="radio" value="cash" v-model="paymentMethod" class="sr-only">
                                        <div :class="['w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center transition-all duration-300',
                                            paymentMethod === 'cash' 
                                                ? 'bg-emerald-500 shadow-lg shadow-emerald-500/30' 
                                                : 'bg-gray-100 group-hover:bg-emerald-100']">
                                            <Banknote :class="['w-6 h-6 sm:w-7 sm:h-7 transition-colors',
                                                paymentMethod === 'cash' ? 'text-white' : 'text-gray-500 group-hover:text-emerald-600']" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-1">Bayar Tunai di Lokasi</h3>
                                            <p class="text-gray-500 text-[11px] sm:text-xs leading-relaxed">
                                                Bayar langsung ke petugas kasir TNLL sebelum tanggal kunjungan
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Online Payment Option -->
                        <label v-if="snapToken" class="payment-card block cursor-pointer">
                            <div :class="['relative group rounded-2xl border-2 transition-all duration-300 overflow-hidden',
                                paymentMethod === 'online' 
                                    ? 'border-blue-500 bg-blue-50 shadow-lg shadow-blue-500/10' 
                                    : 'border-gray-100 bg-white hover:border-gray-200 hover:shadow-md']">
                                <div v-if="paymentMethod === 'online'" 
                                    class="absolute top-3 right-3 sm:top-4 sm:right-4 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-blue-500 flex items-center justify-center">
                                    <Check class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" />
                                </div>
                                
                                <div class="p-5 sm:p-6">
                                    <div class="flex items-start gap-4">
                                        <input type="radio" value="online" v-model="paymentMethod" class="sr-only">
                                        <div :class="['w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center transition-all duration-300',
                                            paymentMethod === 'online' 
                                                ? 'bg-blue-500 shadow-lg shadow-blue-500/30' 
                                                : 'bg-gray-100 group-hover:bg-blue-100']">
                                            <CreditCard :class="['w-6 h-6 sm:w-7 sm:h-7 transition-colors',
                                                paymentMethod === 'online' ? 'text-white' : 'text-gray-500 group-hover:text-blue-600']" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-1">Bayar Online</h3>
                                            <p class="text-gray-500 text-[11px] sm:text-xs leading-relaxed mb-3">
                                                Transfer Bank, QRIS, GoPay, ShopeePay, dan metode lainnya
                                            </p>
                                            <!-- Payment method icons -->
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200">
                                                    <QrCode class="w-3 h-3 text-gray-600" />
                                                    <span class="text-[9px] sm:text-[10px] font-medium text-gray-600">QRIS</span>
                                                </div>
                                                <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200">
                                                    <Building2 class="w-3 h-3 text-gray-600" />
                                                    <span class="text-[9px] sm:text-[10px] font-medium text-gray-600">Bank</span>
                                                </div>
                                                <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200">
                                                    <Wallet class="w-3 h-3 text-gray-600" />
                                                    <span class="text-[9px] sm:text-[10px] font-medium text-gray-600">E-Wallet</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Action Section -->
                        <div class="payment-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                            <!-- Cash Payment Instructions -->
                            <div v-if="paymentMethod === 'cash'" class="space-y-4">
                                <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 border border-amber-200">
                                    <Timer class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="font-semibold text-amber-800 text-xs sm:text-sm">Batas Waktu Pembayaran</h4>
                                        <p class="text-amber-700 text-[10px] sm:text-xs mt-1 leading-relaxed">
                                            Pembayaran tunai harus diselesaikan <strong>paling lambat 1 hari sebelum tanggal kunjungan</strong> 
                                            atau <strong>24 jam setelah pemesanan</strong> (mana yang lebih dulu).
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                                    <MapPin class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="font-semibold text-emerald-800 text-xs sm:text-sm mb-2">Lokasi Pembayaran</h4>
                                        <p class="text-emerald-700 text-[10px] sm:text-xs leading-relaxed">
                                            <strong>Loket Kasir TNLL</strong><br>
                                            Taman Nasional Lore Lindu<br>
                                            Jam Operasional: 08:00 - 16:00 WITA
                                        </p>
                                        <div class="mt-2 pt-2 border-t border-emerald-200">
                                            <p class="text-emerald-700 text-[10px] sm:text-xs">
                                                Tunjukkan kode: <span class="font-mono font-bold text-emerald-900">{{ booking.order_number }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <button @click="confirmCash" :disabled="isProcessing"
                                    class="w-full flex items-center justify-center gap-2 px-5 py-3 sm:py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-sm sm:text-base rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/30 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed group">
                                    <span v-if="isProcessing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    <template v-else>
                                        <Check class="w-4 h-4 sm:w-5 sm:h-5 group-hover:scale-110 transition-transform" />
                                        Konfirmasi Pembayaran Tunai
                                    </template>
                                </button>
                            </div>

                            <!-- Online Payment -->
                            <div v-else-if="paymentMethod === 'online' && snapToken" class="space-y-4">
                                <div class="flex items-start gap-3 p-4 rounded-xl bg-blue-50 border border-blue-200">
                                    <Shield class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="font-semibold text-blue-800 text-xs sm:text-sm">Pembayaran Aman</h4>
                                        <p class="text-blue-700 text-[10px] sm:text-xs mt-1 leading-relaxed">
                                            Transaksi Anda dilindungi dengan enkripsi SSL dan diproses oleh Midtrans.
                                        </p>
                                    </div>
                                </div>

                                <button @click="payOnline" :disabled="isProcessing"
                                    class="w-full flex items-center justify-center gap-2 px-5 py-3 sm:py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm sm:text-base rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all shadow-lg shadow-blue-500/30 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed group">
                                    <span v-if="isProcessing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    <template v-else>
                                        <CreditCard class="w-4 h-4 sm:w-5 sm:h-5 group-hover:scale-110 transition-transform" />
                                        Bayar Online {{ booking.formatted_total_amount }}
                                    </template>
                                </button>
                                <p class="text-center text-[10px] sm:text-xs text-gray-500">
                                    Anda akan diarahkan ke halaman pembayaran Midtrans
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="sidebar-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5 sm:p-6 sticky top-24">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <Wallet class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" />
                                Ringkasan Pesanan
                            </h3>

                            <!-- Destination Info -->
                            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                                <img :src="booking.destination?.primary_image_url || '/images/placeholder-no-image.svg'" 
                                    :alt="booking.destination?.name"
                                    class="w-16 h-12 sm:w-20 sm:h-14 rounded-xl object-cover shadow-md">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 text-xs sm:text-sm truncate">{{ booking.destination?.name }}</p>
                                    <p class="text-[10px] sm:text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                        <Calendar class="w-3 h-3" />
                                        {{ formatDate(booking.visit_date) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Visitor Breakdown -->
                            <div class="py-4 space-y-2 border-b border-gray-100 text-xs sm:text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 flex items-center gap-1.5">
                                        <Users class="w-3.5 h-3.5" />
                                        Pengunjung
                                    </span>
                                    <span class="font-medium text-gray-900">{{ booking.total_visitors }} orang</span>
                                </div>
                                <div v-if="booking.total_adults > 0" class="flex justify-between text-gray-500 pl-5">
                                    <span>• Dewasa</span>
                                    <span>{{ booking.total_adults }}x</span>
                                </div>
                                <div v-if="booking.total_children > 0" class="flex justify-between text-gray-500 pl-5">
                                    <span>• Anak-anak</span>
                                    <span>{{ booking.total_children }}x</span>
                                </div>
                                <div v-if="booking.total_seniors > 0" class="flex justify-between text-gray-500 pl-5">
                                    <span>• Pelajar/Mahasiswa</span>
                                    <span>{{ booking.total_seniors }}x</span>
                                </div>
                            </div>

                            <!-- Price Breakdown -->
                            <div class="py-4 space-y-2 border-b border-gray-100 text-xs sm:text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium text-gray-900">{{ booking.formatted_subtotal }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Biaya Layanan</span>
                                    <span class="font-medium text-gray-900">{{ booking.formatted_service_fee }}</span>
                                </div>
                                <div v-if="booking.discount > 0" class="flex justify-between text-emerald-600 font-medium">
                                    <span>Diskon ({{ booking.discount_code }})</span>
                                    <span>-Rp {{ booking.discount.toLocaleString('id-ID') }}</span>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-900 text-sm sm:text-base">Total</span>
                                    <span class="text-xl sm:text-2xl font-black bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                        {{ booking.formatted_total_amount }}
                                    </span>
                                </div>
                            </div>

                            <!-- Countdown Timer -->
                            <div class="mt-6 p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200">
                                <p class="text-[10px] sm:text-xs text-amber-800 mb-2 font-medium flex items-center gap-1.5">
                                    <Clock class="w-3.5 h-3.5" />
                                    Selesaikan pembayaran dalam:
                                </p>
                                <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg sm:text-xl font-black text-amber-700 bg-white px-2 py-1 rounded-lg shadow-sm">
                                            {{ String(countdown.hours).padStart(2, '0') }}
                                        </span>
                                        <span class="text-[8px] sm:text-[9px] text-amber-600 mt-1">JAM</span>
                                    </div>
                                    <span class="text-lg sm:text-xl font-bold text-amber-600">:</span>
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg sm:text-xl font-black text-amber-700 bg-white px-2 py-1 rounded-lg shadow-sm">
                                            {{ String(countdown.minutes).padStart(2, '0') }}
                                        </span>
                                        <span class="text-[8px] sm:text-[9px] text-amber-600 mt-1">MENIT</span>
                                    </div>
                                    <span class="text-lg sm:text-xl font-bold text-amber-600">:</span>
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg sm:text-xl font-black text-amber-700 bg-white px-2 py-1 rounded-lg shadow-sm">
                                            {{ String(countdown.seconds).padStart(2, '0') }}
                                        </span>
                                        <span class="text-[8px] sm:text-[9px] text-amber-600 mt-1">DETIK</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
