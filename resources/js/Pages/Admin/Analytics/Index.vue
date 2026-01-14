<script setup>
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { Chart, registerables } from 'chart.js';
import { 
    TrendingUp, TrendingDown, DollarSign, ShoppingBag, Users, Percent,
    Download, BarChart3, Calendar, CreditCard, MapPin, ExternalLink,
    Loader2, CheckCircle, Sparkles, Activity, PieChart, Clock,
    ChevronDown, ArrowUpRight, ArrowDownRight, Eye, Ticket
} from 'lucide-vue-next';

// Register Chart.js components
Chart.register(...registerables);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stats: Object,
    revenueChart: Array,
    bookingsChart: Array,
    paymentMethods: Array,
    topDestinations: Array,
    recentBookings: Array,
    period: [String, Number],
    totalPaymentRevenue: Number
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const selectedPeriod = ref(props.period || '30');
const isExporting = ref(false);
let chartInstance = null;
let bookingsChartInstance = null;
let ctx;

const changePeriod = () => {
    router.get('/admin/analytics', { period: selectedPeriod.value }, { preserveState: true });
};

const formatCurrency = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const formatCompactCurrency = (amount) => {
    if (amount >= 1000000000) {
        return 'Rp ' + (amount / 1000000000).toFixed(1) + 'M';
    } else if (amount >= 1000000) {
        return 'Rp ' + (amount / 1000000).toFixed(1) + 'jt';
    } else if (amount >= 1000) {
        return 'Rp ' + (amount / 1000).toFixed(0) + 'rb';
    }
    return formatCurrency(amount);
};

const getStatusColor = (status) => {
    const colors = {
        paid: 'from-emerald-500 to-teal-600 text-white',
        confirmed: 'from-blue-500 to-indigo-600 text-white',
        pending: 'from-amber-500 to-orange-600 text-white',
        cancelled: 'from-red-500 to-rose-600 text-white',
        expired: 'from-gray-500 to-gray-600 text-white',
        used: 'from-violet-500 to-purple-600 text-white',
    };
    return colors[status] || 'from-gray-500 to-gray-600 text-white';
};

const getStatusLabel = (status) => {
    const labels = {
        paid: 'Lunas',
        confirmed: 'Confirmed',
        pending: 'Pending',
        cancelled: 'Dibatalkan',
        expired: 'Expired',
        used: 'Digunakan',
    };
    return labels[status] || status;
};

const statCards = computed(() => [
    {
        label: 'Total Pendapatan',
        value: formatCompactCurrency(props.stats?.revenue?.current),
        fullValue: formatCurrency(props.stats?.revenue?.current),
        growth: props.stats?.revenue?.growth,
        icon: DollarSign,
        gradient: 'from-emerald-500 to-teal-600',
        bgGradient: 'from-emerald-100 to-teal-100',
        shadowColor: 'shadow-emerald-500/30'
    },
    {
        label: 'Total Pemesanan',
        value: formatNumber(props.stats?.bookings?.current),
        growth: props.stats?.bookings?.growth,
        icon: ShoppingBag,
        gradient: 'from-blue-500 to-indigo-600',
        bgGradient: 'from-blue-100 to-indigo-100',
        shadowColor: 'shadow-blue-500/30'
    },
    {
        label: 'Total Pengunjung',
        value: formatNumber(props.stats?.visitors?.current),
        growth: props.stats?.visitors?.growth,
        icon: Users,
        gradient: 'from-violet-500 to-purple-600',
        bgGradient: 'from-violet-100 to-purple-100',
        shadowColor: 'shadow-violet-500/30'
    },
    {
        label: 'Conversion Rate',
        value: (props.stats?.conversion_rate || 0) + '%',
        sub: 'Avg: ' + formatCompactCurrency(props.stats?.avg_order_value),
        icon: Percent,
        gradient: 'from-amber-500 to-orange-600',
        bgGradient: 'from-amber-100 to-orange-100',
        shadowColor: 'shadow-amber-500/30'
    }
]);

const totalMethodRevenue = computed(() => {
    // Use the totalPaymentRevenue from backend, fallback to calculation
    if (props.totalPaymentRevenue && props.totalPaymentRevenue > 0) {
        return props.totalPaymentRevenue;
    }
    const sum = (props.paymentMethods || []).reduce((acc, pm) => acc + (Number(pm.total) || 0), 0);
    return sum > 0 ? sum : 1; // Return 1 as minimum to prevent division by zero
});

// Helper function for safe percentage calculation
const getPercentage = (value, total) => {
    const numValue = Number(value) || 0;
    const numTotal = Number(total) || 1;
    if (numTotal === 0) return 0;
    const percentage = (numValue / numTotal) * 100;
    return isNaN(percentage) ? 0 : percentage;
};

const paymentMethodColors = ['from-emerald-400 to-teal-500', 'from-blue-400 to-indigo-500', 'from-violet-400 to-purple-500', 'from-amber-400 to-orange-500', 'from-rose-400 to-red-500'];

const exportRevenue = () => {
    isExporting.value = true;
    const fromDate = new Date(Date.now() - selectedPeriod.value * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    window.location.href = `/admin/export/revenue?from_date=${fromDate}`;
    setTimeout(() => { isExporting.value = false; }, 2000);
};

// Animations on mount
onMounted(async () => {
    ctx = gsap.context(() => {
        // Animate stat cards
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 30, scale: 0.9 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.6, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        
        // Animate chart cards
        gsap.fromTo('.chart-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.15, delay: 0.4, ease: 'power2.out' }
        );
        
        // Animate data rows
        gsap.fromTo('.data-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.6, ease: 'power2.out' }
        );
    });

    // Initialize Charts after DOM is ready
    await nextTick();
    initCharts();
});

const initCharts = () => {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart')?.getContext('2d');
    if (revenueCtx && props.revenueChart?.length) {
        // Destroy existing chart if it exists
        if (chartInstance) {
            chartInstance.destroy();
        }
        chartInstance = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: props.revenueChart.map(d => d.date),
                datasets: [{
                    label: 'Pendapatan',
                    data: props.revenueChart.map(d => d.total),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgb(16, 185, 129)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(16, 185, 129, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false,
                        callbacks: {
                            label: (context) => 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: '#9CA3AF',
                            font: { size: 10 },
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { 
                            color: 'rgba(156, 163, 175, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#9CA3AF',
                            font: { size: 10 },
                            callback: (v) => {
                                if (v >= 1000000) return (v/1000000).toFixed(0) + 'jt';
                                if (v >= 1000) return (v/1000).toFixed(0) + 'rb';
                                return v;
                            }
                        }
                    }
                }
            }
        });
    }

    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart')?.getContext('2d');
    if (bookingsCtx && props.bookingsChart?.length) {
        // Destroy existing chart if it exists
        if (bookingsChartInstance) {
            bookingsChartInstance.destroy();
        }
        bookingsChartInstance = new Chart(bookingsCtx, {
            type: 'bar',
            data: {
                labels: props.bookingsChart.map(d => d.date),
                datasets: [{
                    label: 'Pemesanan',
                    data: props.bookingsChart.map(d => d.total),
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(99, 102, 241, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: '#9CA3AF',
                            font: { size: 10 },
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { 
                            color: 'rgba(156, 163, 175, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#9CA3AF',
                            font: { size: 10 },
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
};

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
    if (bookingsChartInstance) bookingsChartInstance.destroy();
});
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-pink-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-indigo-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-pink-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Icon with Glow -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <BarChart3 class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Analytics & Pendapatan</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm flex items-center gap-1">
                                <Activity class="w-3 h-3" />
                                Live
                            </span>
                        </div>
                        <p class="text-indigo-100/80 text-xs">Pantau performa bisnis dan pendapatan secara real-time</p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <!-- Period Selector -->
                    <div class="relative">
                        <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 backdrop-blur-xl border border-white/20 shadow-lg">
                            <Calendar class="w-4 h-4 text-white/70" />
                            <select 
                                v-model="selectedPeriod" 
                                @change="changePeriod"
                                class="bg-transparent text-white text-xs font-semibold focus:ring-0 focus:outline-none border-0 cursor-pointer pr-6 appearance-none"
                            >
                                <option value="7" class="text-gray-900">7 Hari</option>
                                <option value="30" class="text-gray-900">30 Hari</option>
                                <option value="90" class="text-gray-900">3 Bulan</option>
                                <option value="365" class="text-gray-900">1 Tahun</option>
                            </select>
                            <ChevronDown class="w-3 h-3 text-white/70 absolute right-3 pointer-events-none" />
                        </div>
                    </div>
                    
                    <!-- Export Button -->
                    <button 
                        @click="exportRevenue"
                        :disabled="isExporting"
                        class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-indigo-600 text-xs font-bold hover:bg-indigo-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg disabled:opacity-50"
                    >
                        <Loader2 v-if="isExporting" class="w-4 h-4 animate-spin" />
                        <Download v-else class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        <span>Export CSV</span>
                        <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Message with Animation -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <!-- Stats Cards with Premium Design -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div 
                v-for="(card, index) in statCards" 
                :key="index"
                class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
            >
                <!-- Background Decoration -->
                <div :class="['absolute top-0 right-0 w-20 h-20 bg-gradient-to-br rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity', card.bgGradient]"></div>
                
                <div class="relative flex items-center gap-3">
                    <div :class="['flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br shadow-lg group-hover:scale-110 transition-transform', card.gradient, card.shadowColor]">
                        <component :is="card.icon" class="w-5 h-5 text-white" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-black text-gray-900 truncate" :title="card.fullValue">{{ card.value }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">{{ card.label }}</p>
                    </div>
                </div>
                
                <!-- Growth indicator -->
                <div v-if="card.growth !== undefined && card.growth !== null" class="mt-3 flex items-center gap-1.5">
                    <span :class="[
                        'inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold',
                        card.growth >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'
                    ]">
                        <component :is="card.growth >= 0 ? ArrowUpRight : ArrowDownRight" class="w-3 h-3" />
                        {{ Math.abs(card.growth) }}%
                    </span>
                    <span class="text-[9px] text-gray-400">vs periode lalu</span>
                </div>
                <p v-else-if="card.sub" class="mt-3 text-[10px] text-gray-500 font-medium bg-gray-50 rounded-lg px-2 py-1 inline-block">{{ card.sub }}</p>
            </div>
        </div>

        <!-- Main Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Revenue Chart -->
            <div class="chart-card lg:col-span-2 bg-white rounded-xl p-5 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-6 bg-gradient-to-b from-emerald-500 to-teal-600 rounded-full"></div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Tren Pendapatan</h3>
                            <p class="text-[10px] text-gray-500">Grafik pendapatan harian</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700">
                        <TrendingUp class="w-3.5 h-3.5" />
                        <span class="text-[10px] font-bold">{{ selectedPeriod }} Hari</span>
                    </div>
                </div>
                <div class="h-[260px]">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Payment Methods Card -->
            <div class="chart-card relative overflow-hidden rounded-xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-5 text-white shadow-xl">
                <!-- Background effects -->
                <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-500 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 opacity-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3 opacity-20"></div>
                
                <div class="relative">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 backdrop-blur-sm">
                            <CreditCard class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm">Metode Pembayaran</h3>
                            <p class="text-[10px] text-slate-400">Distribusi transaksi</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div 
                            v-for="(pm, index) in paymentMethods" 
                            :key="pm.payment_type || index"
                            class="group"
                        >
                            <div class="flex justify-between items-center mb-1.5">
                                <div class="flex items-center gap-2">
                                    <div :class="['w-2 h-2 rounded-full bg-gradient-to-r', paymentMethodColors[index % paymentMethodColors.length]]"></div>
                                    <span class="text-slate-300 font-medium text-xs">{{ pm.payment_type || 'Cash' }}</span>
                                </div>
                                <span class="text-white font-bold text-xs">{{ getPercentage(pm.total, totalMethodRevenue).toFixed(1) }}%</span>
                            </div>
                            <div class="w-full bg-slate-700/50 rounded-full h-2 overflow-hidden">
                                <div 
                                    :class="['h-2 rounded-full bg-gradient-to-r transition-all duration-700 ease-out', paymentMethodColors[index % paymentMethodColors.length]]"
                                    :style="{ width: getPercentage(pm.total, totalMethodRevenue) + '%' }"
                                ></div>
                            </div>
                            <div class="flex justify-between mt-1.5 text-[10px] text-slate-400">
                                <span>{{ pm.count || 0 }} transaksi</span>
                                <span class="text-slate-300 font-medium">{{ formatCompactCurrency(pm.total || 0) }}</span>
                            </div>
                        </div>
                        
                        <div v-if="!paymentMethods?.length" class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="w-12 h-12 rounded-xl bg-slate-700/50 flex items-center justify-center mb-3">
                                <CreditCard class="w-6 h-6 text-slate-500" />
                            </div>
                            <p class="text-slate-400 text-xs">Belum ada data transaksi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Charts & Data -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Bookings Chart -->
            <div class="chart-card bg-white rounded-xl p-5 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-6 bg-gradient-to-b from-indigo-500 to-purple-600 rounded-full"></div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Tren Pemesanan</h3>
                            <p class="text-[10px] text-gray-500">Jumlah booking harian</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700">
                        <ShoppingBag class="w-3.5 h-3.5" />
                        <span class="text-[10px] font-bold">{{ formatNumber(props.stats?.bookings?.current) }} Total</span>
                    </div>
                </div>
                <div class="h-[200px]">
                    <canvas id="bookingsChart"></canvas>
                </div>
            </div>

            <!-- Top Destinations -->
            <div class="chart-card bg-white rounded-xl p-5 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-6 bg-gradient-to-b from-blue-500 to-cyan-600 rounded-full"></div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Destinasi Terlaris</h3>
                            <p class="text-[10px] text-gray-500">Top 5 destinasi periode ini</p>
                        </div>
                    </div>
                    <MapPin class="w-4 h-4 text-blue-500" />
                </div>
                
                <div class="space-y-2.5">
                    <div 
                        v-for="(dest, index) in topDestinations" 
                        :key="dest.id"
                        class="data-row group flex items-center gap-3 p-2.5 rounded-xl hover:bg-gradient-to-r hover:from-blue-50/80 hover:to-cyan-50/50 transition-all duration-200 border border-transparent hover:border-blue-100"
                    >
                        <!-- Rank Badge -->
                        <div :class="[
                            'flex-shrink-0 w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-black',
                            index === 0 ? 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-500/30' :
                            index === 1 ? 'bg-gradient-to-br from-slate-300 to-slate-400 text-white shadow-lg' :
                            index === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-700 text-white shadow-lg' :
                            'bg-gray-100 text-gray-500'
                        ]">
                            {{ index + 1 }}
                        </div>
                        
                        <!-- Image -->
                        <img 
                            :src="dest.primary_image_url" 
                            :alt="dest.name"
                            class="w-10 h-10 rounded-lg object-cover shadow-sm ring-2 ring-white flex-shrink-0"
                        />
                        
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-900 text-xs truncate group-hover:text-blue-700 transition-colors">{{ dest.name }}</h4>
                            <p class="text-[10px] text-gray-500">{{ dest.bookings_count }} pemesanan</p>
                        </div>
                        
                        <!-- Revenue -->
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-emerald-600 text-xs">{{ formatCompactCurrency(dest.revenue) }}</p>
                        </div>
                    </div>
                    
                    <div v-if="!topDestinations?.length" class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                            <MapPin class="w-7 h-7 text-gray-400" />
                        </div>
                        <p class="text-gray-500 text-xs font-medium">Belum ada data destinasi</p>
                        <p class="text-[10px] text-gray-400">Data akan muncul setelah ada booking</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="chart-card bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full"></div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Transaksi Terbaru</h3>
                        <p class="text-[10px] text-gray-500">8 transaksi terakhir</p>
                    </div>
                </div>
                <Link 
                    href="/admin/bookings" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-violet-50 text-violet-700 text-[10px] font-bold hover:bg-violet-100 transition-colors"
                >
                    Lihat Semua
                    <ExternalLink class="w-3 h-3" />
                </Link>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Destinasi</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-600 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="booking in recentBookings" 
                            :key="booking.id" 
                            class="data-row group hover:bg-gradient-to-r hover:from-violet-50/50 hover:to-purple-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-md ring-2 ring-white">
                                        {{ (booking.user?.name || 'G').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-xs">{{ booking.user?.name || 'Guest' }}</p>
                                        <p class="text-[10px] text-gray-500 flex items-center gap-1">
                                            <Clock class="w-3 h-3" />
                                            {{ booking.created_at_human }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <MapPin class="w-3 h-3" />
                                    {{ booking.destination?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold text-gray-900 text-xs">{{ formatCurrency(booking.total_amount) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="[
                                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm',
                                    getStatusColor(booking.status)
                                ]">
                                    {{ getStatusLabel(booking.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Link 
                                    :href="`/admin/bookings/${booking.id}`"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-violet-600 hover:bg-violet-50 transition-all duration-200"
                                    title="Lihat Detail"
                                >
                                    <Eye class="w-4 h-4" />
                                </Link>
                            </td>
                        </tr>
                        
                        <tr v-if="!recentBookings?.length">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-3">
                                        <Ticket class="w-7 h-7 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada transaksi</p>
                                    <p class="text-[10px] text-gray-400">Transaksi terbaru akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    transform: translateY(-16px);
}

/* Custom select arrow removal for webkit browsers */
select {
    -webkit-appearance: none;
    background-image: none;
}
</style>
