<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Banknote, CheckCircle, Coins, QrCode, Wifi, WifiOff } from 'lucide-vue-next';

const props = defineProps({
    todayStats: Object,
    dbConnected: Boolean,
    ticketCount: Number,
    cameraReady: Boolean
});

const currentTime = ref('');
let timeInterval = null;

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
});

onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};
</script>

<template>
    <!-- Background Decoration -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-indigo-500/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
    </div>

    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <QrCode class="w-4 h-4 text-white" />
                    </div>
                    Kasir & Scan Tiket
                </h1>
                <p class="text-gray-500 mt-1">Scan QR Code atau input manual untuk pembayaran dan validasi masuk</p>
            </div>

            <!-- Today's Stats -->
            <div class="flex gap-4 flex-wrap">
                <div class="bg-white rounded-2xl px-5 py-3 shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <Banknote class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Pembayaran Hari Ini</p>
                        <p class="text-xl font-bold text-gray-900">{{ todayStats?.payments || 0 }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl px-5 py-3 shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <CheckCircle class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Validasi Masuk</p>
                        <p class="text-xl font-bold text-gray-900">{{ todayStats?.scans || 0 }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl px-5 py-3 shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                        <Coins class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Pendapatan Tunai</p>
                        <p class="text-lg font-bold text-gray-900">Rp {{ formatCurrency(todayStats?.revenue) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Bar -->
        <div class="mb-6 flex items-center justify-between bg-white/80 backdrop-blur-sm px-5 py-3 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span :class="['animate-ping absolute inline-flex h-full w-full rounded-full opacity-75', dbConnected ? 'bg-green-400' : 'bg-red-400']"></span>
                        <span :class="['relative inline-flex rounded-full h-3 w-3', dbConnected ? 'bg-green-500' : 'bg-red-500']"></span>
                    </span>
                    <component :is="dbConnected ? Wifi : WifiOff" :class="['w-4 h-4', dbConnected ? 'text-green-600' : 'text-red-600']" />
                    <span :class="['text-sm font-medium', dbConnected ? 'text-green-600' : 'text-red-600']">
                        {{ dbConnected ? 'System Online' : 'Database Disconnected' }}
                    </span>
                </div>
                <template v-if="dbConnected">
                    <span class="text-sm text-gray-400">•</span>
                    <span class="text-[11px] text-gray-500">{{ ticketCount }} tiket terdaftar</span>
                </template>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400">{{ currentTime }}</span>
                <span :class="['text-xs px-2 py-1 rounded-full', cameraReady ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                    {{ cameraReady ? '📷 Kamera Siap' : '⏳ Memuat...' }}
                </span>
            </div>
        </div>
    </div>
</template>
