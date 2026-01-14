<script setup>
/**
 * BookingSuccess.vue - Premium Success Page
 * Celebration design with confetti, GSAP animations, and modern UI
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
    CheckCircle, Clock, Banknote, Ticket, Download, Home,
    MapPin, Calendar, Users, QrCode, Mail, Copy, ExternalLink,
    Sparkles, PartyPopper, ArrowRight, Shield, Phone
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    booking: Object,
});

// Refs
const pageRef = ref(null);
const confettiRef = ref(null);
const copiedCode = ref(false);
let ctx;

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { 
        weekday: 'long',
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
};

// Copy order number
const copyOrderNumber = async () => {
    try {
        await navigator.clipboard.writeText(props.booking.order_number);
        copiedCode.value = true;
        setTimeout(() => copiedCode.value = false, 2000);
    } catch (e) {
        console.error('Failed to copy:', e);
    }
};

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

// Status display helpers
const statusConfig = computed(() => {
    const status = normalizedStatus.value;
    if (status === 'confirmed') {
        return {
            icon: 'check',
            bgClass: 'from-emerald-500 to-teal-500',
            glowClass: 'shadow-emerald-500/30',
            ringClass: 'ring-emerald-400/30',
            title: 'Pembayaran Berhasil!',
            subtitle: 'Terima kasih! E-Ticket Anda sudah siap digunakan.',
            emoji: '🎉'
        };
    } else if (status === 'pending') {
        return {
            icon: 'banknote',
            bgClass: 'from-amber-500 to-orange-500',
            glowClass: 'shadow-amber-500/30',
            ringClass: 'ring-amber-400/30',
            title: 'Menunggu Pembayaran',
            subtitle: 'Silakan selesaikan pembayaran untuk mengaktifkan tiket Anda.',
            emoji: '🏧'
        };
    } else {
        return {
            icon: 'clock',
            bgClass: 'from-blue-500 to-indigo-500',
            glowClass: 'shadow-blue-500/30',
            ringClass: 'ring-blue-400/30',
            title: 'Pesanan Dikonfirmasi',
            subtitle: 'Terima kasih telah memesan tiket di TNLL Explore.',
            emoji: '✨'
        };
    }
});

// Create confetti particles
const createConfetti = () => {
    if (!confettiRef.value) return;
    
    const colors = ['#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899', '#f97316', '#eab308'];
    const container = confettiRef.value;
    
    for (let i = 0; i < 60; i++) {
        const particle = document.createElement('div');
        const size = Math.random() * 8 + 4;
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        particle.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
            left: ${Math.random() * 100}%;
            top: -20px;
            opacity: 0;
        `;
        container.appendChild(particle);
        
        // Animate particle
        gsap.to(particle, {
            y: window.innerHeight + 100,
            x: (Math.random() - 0.5) * 200,
            rotation: Math.random() * 720,
            opacity: 1,
            duration: Math.random() * 2 + 2,
            delay: Math.random() * 1.5,
            ease: 'power1.out',
            onComplete: () => particle.remove()
        });
    }
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Main timeline
        const tl = gsap.timeline({ delay: 0.3 });
        
        // Icon entrance with bounce
        tl.fromTo('.success-icon', 
            { scale: 0, rotation: -180 }, 
            { scale: 1, rotation: 0, duration: 0.8, ease: 'elastic.out(1, 0.5)' }
        )
        // Icon glow pulse
        .fromTo('.success-icon-ring', 
            { scale: 0.8, opacity: 0 }, 
            { scale: 1.5, opacity: 0, duration: 1.5, repeat: -1, ease: 'power2.out' },
            '-=0.5'
        )
        // Title entrance
        .fromTo('.success-title', 
            { opacity: 0, y: 30, filter: 'blur(10px)' }, 
            { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.6, ease: 'power3.out' },
            '-=1'
        )
        // Subtitle entrance
        .fromTo('.success-subtitle', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' },
            '-=0.3'
        )
        // Cards entrance
        .fromTo('.info-card', 
            { opacity: 0, y: 40, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'power2.out' },
            '-=0.2'
        )
        // Buttons entrance
        .fromTo('.action-btn', 
            { opacity: 0, y: 20, scale: 0.9 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.1, ease: 'back.out(2)' },
            '-=0.2'
        );

        // Floating shapes
        gsap.to('.float-shape', {
            y: -20,
            duration: 3,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: { each: 0.4, from: 'random' }
        });

        // Sparkle animation
        gsap.to('.sparkle', {
            scale: 1.2,
            opacity: 0.5,
            duration: 1,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: { each: 0.2, from: 'random' }
        });
    }, pageRef.value);

    // Trigger confetti for successful payments
    if (props.booking?.status === 'confirmed') {
        setTimeout(createConfetti, 500);
    }
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <Head :title="`Booking ${booking.order_number} - ${booking.status_label}`" />

    <div ref="pageRef" class="min-h-screen relative overflow-hidden">
        <!-- Confetti Container -->
        <div ref="confettiRef" class="fixed inset-0 pointer-events-none z-50"></div>

        <!-- Background -->
        <div class="fixed inset-0 bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900">
            <!-- Animated Mesh Gradient -->
            <div class="absolute inset-0 opacity-60">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(16,185,129,0.15),transparent_50%)]"></div>
                <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(6,182,212,0.12),transparent_50%)]"></div>
                <div class="absolute bottom-0 left-1/2 w-full h-full bg-[radial-gradient(ellipse_at_50%_100%,rgba(59,130,246,0.08),transparent_50%)]"></div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="float-shape absolute top-[10%] left-[8%] w-16 h-16 sm:w-24 sm:h-24 border border-white/[0.06] rounded-2xl rotate-12"></div>
                <div class="float-shape absolute top-[20%] right-[12%] w-10 h-10 sm:w-16 sm:h-16 border border-emerald-500/10 rounded-full"></div>
                <div class="float-shape absolute top-[50%] left-[5%] w-8 h-8 sm:w-12 sm:h-12 bg-gradient-to-br from-cyan-500/10 to-teal-500/10 rounded-lg rotate-45"></div>
                <div class="float-shape absolute bottom-[20%] right-[8%] w-12 h-12 sm:w-20 sm:h-20 border border-cyan-500/[0.08] rounded-xl -rotate-12"></div>
                <div class="float-shape absolute bottom-[40%] left-[15%] w-6 h-6 sm:w-10 sm:h-10 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full"></div>
                
                <!-- Sparkles -->
                <Sparkles class="sparkle absolute top-[15%] right-[20%] w-4 h-4 sm:w-6 sm:h-6 text-emerald-400/40" />
                <Sparkles class="sparkle absolute top-[35%] left-[10%] w-3 h-3 sm:w-5 sm:h-5 text-cyan-400/40" />
                <Sparkles class="sparkle absolute bottom-[30%] right-[15%] w-4 h-4 sm:w-6 sm:h-6 text-blue-400/40" />
            </div>

            <!-- Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.02]" 
                style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 min-h-screen flex items-center justify-center py-12 sm:py-16 px-4 sm:px-6">
            <div class="max-w-2xl w-full">
                <div class="text-center mb-6 sm:mb-8">
                    <!-- Success Icon -->
                    <div class="relative inline-flex mb-4 sm:mb-6">
                        <!-- Pulse Ring -->
                        <div :class="['success-icon-ring absolute inset-0 rounded-full', statusConfig.ringClass]" 
                            style="background: radial-gradient(circle, currentColor 0%, transparent 70%);"></div>
                        
                        <!-- Icon Container -->
                        <div :class="['success-icon relative w-20 h-20 sm:w-28 sm:h-28 rounded-full flex items-center justify-center bg-gradient-to-br shadow-2xl', statusConfig.bgClass, statusConfig.glowClass]">
                            <Banknote v-if="statusConfig.icon === 'banknote'" class="w-10 h-10 sm:w-14 sm:h-14 text-white" />
                            <CheckCircle v-else-if="statusConfig.icon === 'check'" class="w-10 h-10 sm:w-14 sm:h-14 text-white" />
                            <Clock v-else class="w-10 h-10 sm:w-14 sm:h-14 text-white" />
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="success-title text-2xl sm:text-3xl md:text-4xl font-black text-white mb-2 sm:mb-3">
                        {{ statusConfig.title }} {{ statusConfig.emoji }}
                    </h1>
                    <p class="success-subtitle text-white/60 text-sm sm:text-base max-w-md mx-auto leading-relaxed">
                        {{ statusConfig.subtitle }}
                    </p>
                </div>

                <!-- Order Summary Card -->
                <div class="info-card bg-white/10 backdrop-blur-xl rounded-2xl sm:rounded-3xl border border-white/15 p-5 sm:p-6 mb-4 sm:mb-6">
                    <!-- Order Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-white/10">
                        <div>
                            <p class="text-white/50 text-[10px] sm:text-xs mb-1">Nomor Pesanan</p>
                            <div class="flex items-center gap-2">
                                <p class="text-lg sm:text-xl font-black text-white font-mono">{{ booking.order_number }}</p>
                                <button @click="copyOrderNumber" 
                                    class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                                    <Copy v-if="!copiedCode" class="w-3.5 h-3.5 text-white/60" />
                                    <CheckCircle v-else class="w-3.5 h-3.5 text-emerald-400" />
                                </button>
                            </div>
                        </div>
                        <span :class="['px-3 py-1.5 rounded-full text-[10px] sm:text-xs font-bold',
                            normalizedStatus === 'confirmed' 
                                ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' 
                                : normalizedStatus === 'pending' 
                                    ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' 
                                    : 'bg-blue-500/20 text-blue-300 border border-blue-500/30']">
                            {{ booking.status_label }}
                        </span>
                    </div>

                    <!-- Order Details -->
                    <div class="py-4 space-y-3 text-xs sm:text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-white/50 flex items-center gap-2">
                                <MapPin class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                Destinasi
                            </span>
                            <span class="font-medium text-white text-right">{{ booking.destination?.name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/50 flex items-center gap-2">
                                <Calendar class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                Tanggal Kunjungan
                            </span>
                            <span class="font-medium text-white text-right">{{ formatDate(booking.visit_date) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/50 flex items-center gap-2">
                                <Users class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                Jumlah Pengunjung
                            </span>
                            <span class="font-medium text-white">{{ booking.total_visitors }} orang</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/50">Ketua Rombongan</span>
                            <span class="font-medium text-white text-right">{{ booking.leader_name }}</span>
                        </div>
                        <div v-if="booking.discount > 0" class="flex justify-between items-center text-emerald-400">
                            <span>Diskon ({{ booking.discount_code }})</span>
                            <span class="font-bold">-Rp {{ booking.discount.toLocaleString('id-ID') }}</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="pt-4 border-t border-white/10">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-white text-sm sm:text-base">Total Pembayaran</span>
                            <span class="text-xl sm:text-2xl font-black bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">
                                {{ booking.formatted_total_amount }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pending Payment QR Code -->
                <div v-if="normalizedStatus === 'pending'" 
                    class="info-card bg-amber-500/10 backdrop-blur-xl rounded-2xl border border-amber-500/20 p-5 sm:p-6 mb-4 sm:mb-6">
                    <h3 class="font-bold text-amber-300 mb-4 flex items-center gap-2 text-sm sm:text-base">
                        <QrCode class="w-4 h-4 sm:w-5 sm:h-5" />
                        QR Code Pembayaran Tunai
                    </h3>
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                        <!-- QR Code -->
                        <div class="bg-white p-3 sm:p-4 rounded-xl shadow-lg">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(booking.order_number)}`" 
                                 :alt="`QR Code ${booking.order_number}`"
                                 class="w-28 h-28 sm:w-32 sm:h-32 object-contain">
                            <p class="mt-2 text-center text-[10px] sm:text-xs font-mono font-bold text-amber-600 bg-amber-50 rounded-lg py-1">
                                {{ booking.order_number }}
                            </p>
                        </div>
                        <!-- Instructions -->
                        <div class="flex-1 text-center sm:text-left space-y-3 text-amber-200/80 text-xs sm:text-sm">
                            <p><strong class="text-amber-200">Lokasi Pembayaran:</strong> Loket Kasir TNLL</p>
                            <p><strong class="text-amber-200">Jam Operasional:</strong> 08:00 - 16:00 WITA</p>
                            <p><strong class="text-amber-200">Total Bayar:</strong> <span class="font-bold text-amber-100">{{ booking.formatted_total_amount }}</span></p>
                            <div class="pt-3 border-t border-amber-500/20">
                                <p class="flex items-start gap-2">
                                    <Clock class="w-4 h-4 flex-shrink-0 mt-0.5" />
                                    <span>Tunjukkan QR Code ini ke petugas kasir untuk pembayaran</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- E-Ticket Preview (for confirmed bookings) -->
                <div v-if="normalizedStatus === 'confirmed' && booking.tickets?.length > 0" 
                    class="info-card bg-white/10 backdrop-blur-xl rounded-2xl border border-emerald-500/20 p-5 sm:p-6 mb-4 sm:mb-6">
                    <h3 class="font-bold text-white mb-4 flex items-center gap-2 text-sm sm:text-base">
                        <QrCode class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400" />
                        E-Ticket Anda
                    </h3>
                    <div class="space-y-3">
                        <div v-for="ticket in booking.tickets" :key="ticket.id"
                            class="flex items-center gap-3 sm:gap-4 p-3 sm:p-4 bg-white/5 rounded-xl border border-white/10 hover:border-emerald-500/30 transition-colors">
                            <div v-if="ticket.qr_code_url" 
                                class="w-14 h-14 sm:w-20 sm:h-20 rounded-lg bg-white p-1.5 sm:p-2 shadow-lg shadow-emerald-500/20">
                                <img :src="ticket.qr_code_url" :alt="ticket.ticket_code" class="w-full h-full object-contain">
                            </div>
                            <div v-else class="w-14 h-14 sm:w-20 sm:h-20 rounded-lg bg-white/10 flex items-center justify-center">
                                <QrCode class="w-6 h-6 sm:w-8 sm:h-8 text-white/40" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-mono font-bold text-white text-xs sm:text-sm">{{ ticket.ticket_code }}</p>
                                <p class="text-[10px] sm:text-xs text-white/50">{{ ticket.total_persons }} pengunjung</p>
                                <p class="text-[10px] sm:text-xs text-emerald-400 mt-1">Berlaku: {{ formatDate(ticket.valid_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <!-- View Detail Button - for all statuses -->
                    <Link :href="`/my-bookings/${booking.order_number}`" 
                        class="action-btn group inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 sm:py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm rounded-xl hover:from-blue-400 hover:to-indigo-500 transition-all shadow-xl shadow-blue-500/30 hover:shadow-blue-500/40 hover:-translate-y-0.5">
                        <Ticket class="w-4 h-4 sm:w-5 sm:h-5 group-hover:rotate-12 transition-transform" />
                        Lihat Detail Tiket
                    </Link>
                    
                    <a v-if="normalizedStatus === 'confirmed'" 
                        :href="`/booking/${booking.order_number}/ticket`" 
                        target="_blank"
                        class="action-btn group inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 sm:py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-sm rounded-xl hover:from-emerald-400 hover:to-teal-500 transition-all shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/40 hover:-translate-y-0.5">
                        <Download class="w-4 h-4 sm:w-5 sm:h-5 group-hover:animate-bounce" />
                        Download E-Ticket (PDF)
                    </a>
                    <Link href="/my-bookings" 
                        class="action-btn group inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 sm:py-3.5 bg-white/10 backdrop-blur-xl border border-white/20 text-white font-semibold text-sm rounded-xl hover:bg-white/20 hover:border-white/30 transition-all hover:-translate-y-0.5">
                        <Ticket class="w-4 h-4 sm:w-5 sm:h-5 group-hover:rotate-12 transition-transform" />
                        Lihat Semua Tiket
                    </Link>
                    <Link href="/" 
                        class="action-btn group inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3 sm:py-3.5 bg-white/10 backdrop-blur-xl border border-white/20 text-white font-semibold text-sm rounded-xl hover:bg-white/20 hover:border-white/30 transition-all hover:-translate-y-0.5">
                        <Home class="w-4 h-4 sm:w-5 sm:h-5 group-hover:scale-110 transition-transform" />
                        Kembali ke Beranda
                    </Link>
                </div>

                <!-- Email Notification -->
                <p class="info-card text-[10px] sm:text-xs text-white/40 mt-6 sm:mt-8 text-center flex items-center justify-center gap-1.5 flex-wrap">
                    <Mail class="w-3.5 h-3.5" />
                    Detail pesanan juga telah dikirim ke email 
                    <strong class="text-white/60">{{ booking.leader_email }}</strong>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.float-shape {
    will-change: transform;
}

.sparkle {
    will-change: transform, opacity;
}
</style>
