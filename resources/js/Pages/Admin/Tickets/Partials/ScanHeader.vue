<script setup>
/**
 * ScanHeader.vue - Premium Header Component
 * Matching Newsletter design system
 */
import { ref, onMounted, onUnmounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { Banknote, CheckCircle, Coins, QrCode, Wifi, WifiOff, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    todayStats: Object,
    dbConnected: Boolean,
    ticketCount: Number,
    cameraReady: Boolean
});

const currentTime = ref('');
let timeInterval = null;
let ctx;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'short',
        year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

onMounted(() => {
    updateTime();
    timeInterval = setInterval(updateTime, 1000);

    // GSAP Animations
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
    });
});

onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};
</script>

<template>
    <!-- Premium Header with Glassmorphism -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-fuchsia-600 p-6 shadow-2xl">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute top-10 right-40 w-32 h-32 bg-fuchsia-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-purple-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
        </div>
        
        <!-- Floating Particles -->
        <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-fuchsia-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
        <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
        
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <!-- Icon with Glow -->
                <div class="relative">
                    <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <QrCode class="h-6 w-6 text-white" />
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Kasir & Scan Tiket</h1>
                        <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm flex items-center gap-1">
                            <Sparkles class="w-3 h-3" />
                            Live
                        </span>
                    </div>
                    <p class="text-purple-100/80 text-xs">Scan QR Code atau input manual untuk pembayaran dan validasi</p>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="flex items-center gap-2">
                <span :class="['flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-bold backdrop-blur-sm', dbConnected ? 'bg-emerald-500/20 text-emerald-100 ring-1 ring-emerald-400/30' : 'bg-red-500/20 text-red-100 ring-1 ring-red-400/30']">
                    <span class="relative flex h-2 w-2">
                        <span :class="['animate-ping absolute inline-flex h-full w-full rounded-full opacity-75', dbConnected ? 'bg-emerald-400' : 'bg-red-400']"></span>
                        <span :class="['relative inline-flex rounded-full h-2 w-2', dbConnected ? 'bg-emerald-500' : 'bg-red-500']"></span>
                    </span>
                    {{ dbConnected ? 'System Online' : 'Offline' }}
                </span>
                <span :class="['px-3 py-1.5 rounded-full text-[10px] font-bold backdrop-blur-sm', cameraReady ? 'bg-white/20 text-white/90' : 'bg-amber-500/20 text-amber-100']">
                    {{ cameraReady ? '📷 Kamera Siap' : '⏳ Memuat...' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5">
        <!-- Pembayaran Hari Ini -->
        <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <Banknote class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ todayStats?.payments || 0 }}</p>
                    <p class="text-[10px] text-gray-500 font-medium">Pembayaran Hari Ini</p>
                </div>
            </div>
        </div>
        
        <!-- Validasi Masuk -->
        <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                    <CheckCircle class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ todayStats?.scans || 0 }}</p>
                    <p class="text-[10px] text-gray-500 font-medium">Validasi Masuk</p>
                </div>
            </div>
        </div>
        
        <!-- Pendapatan Tunai -->
        <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                    <Coins class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-lg font-black text-gray-900">Rp {{ formatCurrency(todayStats?.revenue) }}</p>
                    <p class="text-[10px] text-gray-500 font-medium">Pendapatan Tunai</p>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status Bar -->
    <div class="mt-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white px-5 py-3 rounded-xl shadow-lg border border-gray-100">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span :class="['animate-ping absolute inline-flex h-full w-full rounded-full opacity-75', dbConnected ? 'bg-green-400' : 'bg-red-400']"></span>
                    <span :class="['relative inline-flex rounded-full h-3 w-3', dbConnected ? 'bg-green-500' : 'bg-red-500']"></span>
                </span>
                <component :is="dbConnected ? Wifi : WifiOff" :class="['w-4 h-4', dbConnected ? 'text-green-600' : 'text-red-600']" />
                <span :class="['text-xs font-medium', dbConnected ? 'text-green-600' : 'text-red-600']">
                    {{ dbConnected ? 'Database Connected' : 'Database Disconnected' }}
                </span>
            </div>
            <template v-if="dbConnected">
                <span class="text-xs text-gray-300">•</span>
                <span class="text-[10px] text-gray-500">{{ ticketCount }} tiket terdaftar</span>
            </template>
        </div>
        <span class="text-[10px] text-gray-400">{{ currentTime }}</span>
    </div>
</template>
