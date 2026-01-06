<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Wallet, CalendarCheck, Users, UserCircle, TrendingUp, TrendingDown,
    Clock, MessageSquare, MapPin, Activity, ChevronRight, Calendar,
    AlertCircle, CheckCircle, XCircle, ArrowUpRight, ArrowDownRight,
    Eye, ShoppingCart, CreditCard, Sparkles, Flower2, Bird, Image,
    Target, Zap, Server, Database, HardDrive, Cpu
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    labels: Array,
    revenueData: Array,
    visitorData: Array,
    bookingStatus: Object,
    topDestinations: Array,
    recentBookings: Array,
    recentActivities: Array,
    goals: Object,
    systemStatus: Object,
    attention: Object,
    calendarBookings: Object,
    periodLabel: String,
    startDate: String,
    endDate: String,
});

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount || 0);
};

// Format number
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

// Stats cards configuration - Primary stats
const primaryStats = computed(() => [
    {
        title: `Pendapatan (${props.periodLabel || '7 Hari'})`,
        value: formatCurrency(props.stats?.periodRevenue),
        subLabel: 'Total',
        subValue: formatCurrency(props.stats?.totalRevenue),
        icon: Wallet,
        gradient: 'from-emerald-400 to-green-600',
        bgGradient: 'from-emerald-500/10 to-transparent',
        shadowColor: 'shadow-emerald-500/20',
        glowClass: 'glow-green',
    },
    {
        title: `Pemesanan (${props.periodLabel || '7 Hari'})`,
        value: formatNumber(props.stats?.periodBookings),
        subLabel: 'Total',
        subValue: formatNumber(props.stats?.totalBookings),
        icon: CalendarCheck,
        gradient: 'from-blue-400 to-indigo-600',
        bgGradient: 'from-blue-500/10 to-transparent',
        shadowColor: 'shadow-blue-500/20',
        glowClass: 'glow-blue',
    },
    {
        title: 'Menunggu Pembayaran',
        value: formatNumber(props.stats?.pendingBookings),
        linkHref: '/admin/bookings?status=pending',
        linkText: 'Lihat semua',
        icon: Clock,
        gradient: 'from-amber-400 to-orange-600',
        bgGradient: 'from-amber-500/10 to-transparent',
        shadowColor: 'shadow-amber-500/20',
        glowClass: 'glow-amber',
    },
    {
        title: 'Total Pengguna',
        value: formatNumber(props.stats?.totalUsers),
        subLabel: 'Pengguna terdaftar',
        icon: Users,
        gradient: 'from-purple-400 to-violet-600',
        bgGradient: 'from-purple-500/10 to-transparent',
        shadowColor: 'shadow-purple-500/20',
        glowClass: 'glow-purple',
    },
]);

// Content stats
const contentStats = computed(() => [
    {
        title: 'Destinasi',
        value: props.stats?.totalDestinations || 0,
        icon: MapPin,
        bgColor: 'from-emerald-50 to-green-50',
        iconBg: 'from-emerald-400/20 to-green-500/20',
        textColor: 'text-emerald-700',
        iconColor: 'text-emerald-600',
        borderColor: 'border-emerald-100/50',
    },
    {
        title: 'Flora',
        value: props.stats?.totalFlora || 0,
        icon: Flower2,
        bgColor: 'from-lime-50 to-green-50',
        iconBg: 'from-lime-400/20 to-green-500/20',
        textColor: 'text-lime-700',
        iconColor: 'text-lime-600',
        borderColor: 'border-lime-100/50',
    },
    {
        title: 'Fauna',
        value: props.stats?.totalFauna || 0,
        icon: Bird,
        bgColor: 'from-amber-50 to-yellow-50',
        iconBg: 'from-amber-400/20 to-orange-500/20',
        textColor: 'text-amber-700',
        iconColor: 'text-amber-600',
        borderColor: 'border-amber-100/50',
    },
    {
        title: 'Gallery',
        value: props.stats?.totalGallery || 0,
        icon: Image,
        bgColor: 'from-pink-50 to-rose-50',
        iconBg: 'from-pink-400/20 to-rose-500/20',
        textColor: 'text-pink-700',
        iconColor: 'text-pink-600',
        borderColor: 'border-pink-100/50',
    },
]);

// Status badge color mapping
const getStatusClass = (status) => {
    const classes = {
        confirmed: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
        pending: 'bg-amber-100 text-amber-700 ring-amber-600/20',
        cancelled: 'bg-red-100 text-red-700 ring-red-600/20',
        completed: 'bg-blue-100 text-blue-700 ring-blue-600/20',
        paid: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    };
    return classes[status] || 'bg-gray-100 text-gray-700 ring-gray-600/20';
};

const getStatusLabel = (status) => {
    const labels = {
        confirmed: 'Dikonfirmasi',
        pending: 'Menunggu',
        cancelled: 'Dibatalkan',
        completed: 'Selesai',
        paid: 'Dibayar',
    };
    return labels[status] || status;
};

const getStatusIcon = (status) => {
    const icons = {
        confirmed: CheckCircle,
        pending: Clock,
        cancelled: XCircle,
        completed: CheckCircle,
        paid: CheckCircle,
    };
    return icons[status] || AlertCircle;
};

// Format date
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-5">
            <!-- Welcome Header with Animated Gradient -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-5 shadow-xl shadow-violet-500/25">
                <!-- Animated Background Elements -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                    <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
                </div>

                <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-white/20 backdrop-blur-xl ring-1 ring-white/30">
                                <Sparkles class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h1 class="text-xl lg:text-2xl font-bold text-white tracking-tight">Selamat Datang!</h1>
                                <p class="text-purple-100/90 text-xs font-medium">Dashboard Admin TNLL Explore</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 backdrop-blur-xl ring-1 ring-white/20">
                                <Calendar class="w-3.5 h-3.5 text-purple-200" />
                                <span class="text-purple-100 text-xs font-medium">{{ periodLabel }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-purple-100/80 text-[10px]">Hari ini</p>
                            <p class="text-white font-bold text-sm">{{ stats?.todayBookings || 0 }} Booking</p>
                        </div>
                        <div class="w-px h-8 bg-white/20"></div>
                        <div class="text-right">
                            <p class="text-purple-100/80 text-[10px]">Pending</p>
                            <p class="text-amber-300 font-bold text-sm">{{ stats?.pendingBookings || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Primary Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                <div 
                    v-for="(stat, index) in primaryStats" 
                    :key="index"
                    class="group relative overflow-hidden rounded-xl bg-white p-4 border border-gray-100 shadow-lg hover:shadow-xl transition-all duration-500 hover:-translate-y-1"
                >
                    <div :class="['absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl rounded-bl-full transition-all group-hover:scale-110', stat.bgGradient]"></div>
                    
                    <div class="relative z-10 flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-[11px] font-medium text-gray-500">{{ stat.title }}</p>
                            <p class="text-lg font-bold text-gray-900">{{ stat.value }}</p>
                            <div v-if="stat.subValue" class="flex items-center gap-1">
                                <div class="w-5 h-5 rounded-md bg-gray-100 flex items-center justify-center">
                                    <ArrowUpRight class="w-3 h-3 text-gray-600" />
                                </div>
                                <span class="text-[10px] font-medium text-gray-600">{{ stat.subLabel }}: {{ stat.subValue }}</span>
                            </div>
                            <Link v-if="stat.linkHref" :href="stat.linkHref" class="inline-flex items-center gap-1 text-[10px] font-medium text-amber-600 hover:text-amber-700 transition-colors">
                                {{ stat.linkText }}
                                <ChevronRight class="w-3 h-3" />
                            </Link>
                        </div>
                        
                        <div :class="[
                            'w-10 h-10 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform bg-gradient-to-br',
                            stat.gradient
                        ]">
                            <component :is="stat.icon" class="w-5 h-5 text-white" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div 
                    v-for="(stat, index) in contentStats"
                    :key="index"
                    :class="[
                        'group rounded-xl p-3.5 border hover:shadow-lg transition-all bg-gradient-to-br',
                        stat.bgColor,
                        stat.borderColor
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <div :class="[
                            'w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform bg-gradient-to-br',
                            stat.iconBg
                        ]">
                            <component :is="stat.icon" :class="['w-4 h-4', stat.iconColor]" />
                        </div>
                        <div>
                            <p :class="['text-lg font-bold', stat.textColor]">{{ stat.value }}</p>
                            <p :class="['text-[11px] font-medium opacity-80', stat.textColor]">{{ stat.title }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-3">
                <!-- Recent Bookings Table -->
                <div class="xl:col-span-2 bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30">
                                    <ShoppingCart class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900">Booking Terbaru</h2>
                                    <p class="text-[11px] text-gray-500">5 transaksi terakhir</p>
                                </div>
                            </div>
                            <Link href="/admin/bookings" class="group flex items-center gap-2 px-4 py-2 rounded-xl text-violet-600 hover:bg-violet-50 font-medium text-sm transition-all duration-300">
                                Lihat Semua
                                <ChevronRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Destinasi</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr 
                                    v-for="(booking, index) in recentBookings" 
                                    :key="booking.id" 
                                    class="group hover:bg-gradient-to-r hover:from-violet-50/50 hover:to-purple-50/50 transition-all duration-300"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-white text-sm font-bold shadow-lg">
                                                {{ booking.user?.name?.charAt(0) || '?' }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors">{{ booking.user?.name || 'Unknown' }}</p>
                                                <p class="text-[11px] text-gray-500">{{ booking.user?.email || '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <MapPin class="w-4 h-4 text-gray-400" />
                                            <p class="text-gray-700">{{ booking.destination?.name || 'N/A' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold ring-1', getStatusClass(booking.status)]">
                                            <component :is="getStatusIcon(booking.status)" class="w-3.5 h-3.5" />
                                            {{ getStatusLabel(booking.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="`/admin/bookings/${booking.id}`" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-violet-600 hover:bg-violet-100 text-sm font-medium transition-colors">
                                            <Eye class="w-4 h-4" />
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!recentBookings?.length">
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                                <ShoppingCart class="w-8 h-8 text-gray-400" />
                                            </div>
                                            <p class="text-gray-500 font-medium">Belum ada booking</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-5">
                    <!-- Need Attention Card -->
                    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30">
                                    <AlertCircle class="w-5 h-5 text-white" />
                                </div>
                                <h2 class="text-lg font-bold text-gray-900">Perlu Perhatian</h2>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <Link v-if="attention?.pending_bookings > 0" href="/admin/bookings?status=pending" class="group flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-amber-100/50 border border-amber-200/50 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                                        <Clock class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <span class="font-semibold text-amber-900">Booking Pending</span>
                                        <p class="text-sm text-amber-600">Butuh konfirmasi</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-amber-500/30">
                                    {{ attention.pending_bookings }}
                                </span>
                            </Link>

                            <Link v-if="attention?.unread_feedback > 0" href="/admin/feedback" class="group flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-100/50 border border-blue-200/50 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                                        <MessageSquare class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <span class="font-semibold text-blue-900">Feedback Baru</span>
                                        <p class="text-sm text-blue-600">Belum dibaca</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30">
                                    {{ attention.unread_feedback }}
                                </span>
                            </Link>

                            <div v-if="!attention?.pending_bookings && !attention?.unread_feedback" class="text-center py-8">
                                <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-100 flex items-center justify-center mb-4">
                                    <CheckCircle class="w-8 h-8 text-emerald-600" />
                                </div>
                                <p class="font-semibold text-gray-700">Semua Beres!</p>
                                <p class="text-[11px] text-gray-500">Tidak ada yang perlu ditangani</p>
                            </div>
                        </div>
                    </div>

                    <!-- Top Destinations -->
                    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-violet-50 to-purple-50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30">
                                    <MapPin class="w-5 h-5 text-white" />
                                </div>
                                <h2 class="text-lg font-bold text-gray-900">Destinasi Populer</h2>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <Link 
                                v-for="(dest, index) in topDestinations?.slice(0, 5)" 
                                :key="dest.id"
                                :href="`/admin/destinations/${dest.id}/edit`"
                                class="group flex items-center gap-4 p-3 rounded-xl hover:bg-gradient-to-r hover:from-violet-50 hover:to-purple-50 transition-all duration-300"
                            >
                                <div :class="[
                                    'flex items-center justify-center w-10 h-10 rounded-xl font-bold text-sm shadow-lg',
                                    index === 0 ? 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-amber-500/30' :
                                    index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-400 text-white shadow-gray-400/30' :
                                    index === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-700 text-white shadow-amber-600/30' :
                                    'bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-violet-500/30'
                                ]">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate group-hover:text-violet-600 transition-colors">{{ dest.name }}</p>
                                    <p class="text-[11px] text-gray-500">{{ dest.bookings_count }} booking</p>
                                </div>
                                <ChevronRight class="w-5 h-5 text-gray-300 group-hover:text-violet-500 group-hover:translate-x-1 transition-all" />
                            </Link>
                            <div v-if="!topDestinations?.length" class="text-center py-8">
                                <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                                    <MapPin class="w-8 h-8 text-gray-400" />
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada data</p>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Goals -->
                    <div v-if="goals" class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30">
                                    <Target class="w-5 h-5 text-white" />
                                </div>
                                <h2 class="text-lg font-bold text-gray-900">Target Bulanan</h2>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Booking Goal -->
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700">Booking</span>
                                    <span class="text-gray-500">{{ goals.monthly_bookings || 0 }} / {{ goals.monthly_booking_goal || 100 }}</span>
                                </div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-gradient-to-r from-violet-500 to-purple-600 rounded-full transition-all duration-1000 ease-out"
                                        :style="{ width: `${goals.booking_progress || 0}%` }"
                                    ></div>
                                </div>
                            </div>
                            <!-- Revenue Goal -->
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700">Pendapatan</span>
                                    <span class="text-gray-500">{{ goals.revenue_progress || 0 }}%</span>
                                </div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full transition-all duration-1000 ease-out"
                                        :style="{ width: `${goals.revenue_progress || 0}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30">
                            <Activity class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h2>
                            <p class="text-[11px] text-gray-500">Riwayat aktivitas sistem</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div 
                            v-for="(activity, index) in recentActivities" 
                            :key="index"
                            class="group flex items-start gap-4 p-4 rounded-2xl bg-gradient-to-br from-gray-50 to-white border border-gray-100 hover:shadow-lg hover:border-violet-200 transition-all duration-300"
                        >
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-violet-100 to-purple-100 text-violet-600 group-hover:from-violet-500 group-hover:to-purple-600 group-hover:text-white transition-all duration-300">
                                <Activity class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors">{{ activity.title }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ activity.description }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ formatDate(activity.time) }}</p>
                            </div>
                        </div>
                        <div v-if="!recentActivities?.length" class="col-span-full text-center py-12">
                            <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                                <Activity class="w-8 h-8 text-gray-400" />
                            </div>
                            <p class="text-gray-500 font-medium">Belum ada aktivitas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status (if available) -->
            <div v-if="systemStatus" class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-slate-500 to-gray-600 shadow-lg shadow-slate-500/30">
                            <Server class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Status Sistem</h2>
                            <p class="text-[11px] text-gray-500">Monitoring server dan database</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-100">
                        <div class="flex items-center gap-2 mb-2">
                            <Database class="w-4 h-4 text-emerald-600" />
                            <span class="text-sm font-medium text-emerald-700">Database</span>
                        </div>
                        <p class="text-lg font-bold text-emerald-700">{{ systemStatus.database || 'OK' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100">
                        <div class="flex items-center gap-2 mb-2">
                            <HardDrive class="w-4 h-4 text-blue-600" />
                            <span class="text-sm font-medium text-blue-700">Storage</span>
                        </div>
                        <p class="text-lg font-bold text-blue-700">{{ systemStatus.storage || 'OK' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gradient-to-br from-purple-50 to-violet-50 border border-purple-100">
                        <div class="flex items-center gap-2 mb-2">
                            <Cpu class="w-4 h-4 text-purple-600" />
                            <span class="text-sm font-medium text-purple-700">Cache</span>
                        </div>
                        <p class="text-lg font-bold text-purple-700">{{ systemStatus.cache || 'OK' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100">
                        <div class="flex items-center gap-2 mb-2">
                            <Zap class="w-4 h-4 text-amber-600" />
                            <span class="text-sm font-medium text-amber-700">Queue</span>
                        </div>
                        <p class="text-lg font-bold text-amber-700">{{ systemStatus.queue || 'OK' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>
