<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    TrendingUp, TrendingDown, DollarSign, ShoppingBag, Users, Percent,
    Download, BarChart3, Calendar, CreditCard, MapPin, ExternalLink
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stats: Object,
    revenueChart: Array,
    bookingsChart: Array,
    paymentMethods: Array,
    topDestinations: Array,
    recentBookings: Array,
    period: [String, Number]
});

const selectedPeriod = ref(props.period || '30');
let chartInstance = null;

const changePeriod = () => {
    router.get('/admin/analytics', { period: selectedPeriod.value }, { preserveState: true });
};

const formatCurrency = (amount) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const getStatusColor = (status) => {
    const colors = {
        paid: 'bg-green-100 text-green-700',
        pending: 'bg-yellow-100 text-yellow-700',
        cancelled: 'bg-red-100 text-red-700',
        expired: 'bg-gray-100 text-gray-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const statCards = computed(() => [
    {
        label: 'Total Pendapatan',
        value: formatCurrency(props.stats?.revenue?.current),
        growth: props.stats?.revenue?.growth,
        icon: DollarSign,
        color: 'emerald',
        gradient: 'from-emerald-400 to-emerald-600'
    },
    {
        label: 'Total Pemesanan',
        value: formatNumber(props.stats?.bookings?.current),
        growth: props.stats?.bookings?.growth,
        icon: ShoppingBag,
        color: 'blue',
        gradient: 'from-blue-400 to-blue-600'
    },
    {
        label: 'Total Pengunjung',
        value: formatNumber(props.stats?.visitors?.current),
        growth: props.stats?.visitors?.growth,
        icon: Users,
        color: 'violet',
        gradient: 'from-violet-400 to-violet-600'
    },
    {
        label: 'Conversion Rate',
        value: (props.stats?.conversion_rate || 0) + '%',
        sub: 'Avg Order: ' + formatCurrency(props.stats?.avg_order_value),
        icon: Percent,
        color: 'amber',
        gradient: 'from-amber-400 to-amber-600'
    }
]);

// Chart initialization
onMounted(() => {
    if (typeof Chart !== 'undefined' && props.revenueChart?.length) {
        const ctx = document.getElementById('revenueChart')?.getContext('2d');
        if (ctx) {
            chartInstance = new Chart(ctx, {
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
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(0) + 'jt' : (v/1000).toFixed(0) + 'rb')
                            }
                        }
                    }
                }
            });
        }
    }
});

onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
});

const totalMethodRevenue = computed(() => {
    return (props.paymentMethods || []).reduce((sum, pm) => sum + (pm.total || 0), 0) || 1;
});
</script>

<template>
    <div class="space-y-5">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                        <BarChart3 class="w-7 h-7 text-white" />
                    </div>
                    Pendapatan & Analytics
                </h1>
                <p class="text-gray-500 mt-1">Pantau performa bisnis dan pendapatan secara real-time</p>
            </div>

            <div class="flex items-center gap-3 bg-white p-1.5 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center gap-2 pl-3">
                    <Calendar class="w-4 h-4 text-gray-400" />
                    <select v-model="selectedPeriod" @change="changePeriod"
                        class="border-0 bg-transparent text-sm font-semibold text-gray-700 focus:ring-0 cursor-pointer pr-8">
                        <option value="7">7 Hari Terakhir</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="90">3 Bulan Terakhir</option>
                        <option value="365">1 Tahun Ini</option>
                    </select>
                </div>
                <div class="h-6 w-px bg-gray-200"></div>
                <a :href="`/admin/export/revenue?from_date=${new Date(Date.now() - period * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}`"
                    class="px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition flex items-center gap-2">
                    <Download class="w-4 h-4" />
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <div v-for="(card, index) in statCards" :key="index"
                class="bg-white/60 backdrop-blur-xl border border-white/40 shadow-lg rounded-3xl p-6 relative overflow-hidden group hover:shadow-xl transition-all duration-300">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <component :is="card.icon" class="w-24 h-24" />
                </div>
                <div class="relative z-10">
                    <div :class="['w-12 h-12 rounded-2xl bg-gradient-to-br flex items-center justify-center text-white shadow-lg mb-4', card.gradient]">
                        <component :is="card.icon" class="w-6 h-6" />
                    </div>
                    <p class="text-sm font-medium text-gray-500 mb-1">{{ card.label }}</p>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ card.value }}</h3>
                    <div v-if="card.growth !== undefined && card.growth !== null" class="flex items-center gap-1 mt-2">
                        <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-bold', card.growth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                            <component :is="card.growth >= 0 ? TrendingUp : TrendingDown" class="w-3 h-3 mr-1" />
                            {{ Math.abs(card.growth) }}%
                        </span>
                        <span class="text-xs text-gray-400">vs periode lalu</span>
                    </div>
                    <p v-else-if="card.sub" class="text-xs text-gray-500 mt-2 font-medium">{{ card.sub }}</p>
                </div>
            </div>
        </div>

        <!-- Main Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                    Tren Pendapatan
                </h3>
                <div class="h-[300px]">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-indigo-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 opacity-30"></div>
                <h3 class="font-bold text-xl mb-6 relative z-10 flex items-center gap-2">
                    <CreditCard class="w-5 h-5" />
                    Metode Pembayaran
                </h3>
                <div class="space-y-6 relative z-10">
                    <div v-for="pm in paymentMethods" :key="pm.payment_type">
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-indigo-200 font-medium text-sm">{{ pm.payment_type }}</span>
                            <span class="text-white font-bold">{{ ((pm.total / totalMethodRevenue) * 100).toFixed(1) }}%</span>
                        </div>
                        <div class="w-full bg-indigo-700/50 rounded-full h-2.5">
                            <div class="bg-gradient-to-r from-emerald-400 to-teal-400 h-2.5 rounded-full transition-all duration-500"
                                :style="{ width: ((pm.total / totalMethodRevenue) * 100) + '%' }"></div>
                        </div>
                        <div class="flex justify-between mt-1 text-xs text-indigo-300">
                            <span>{{ pm.count }} transaksi</span>
                            <span>{{ formatCurrency(pm.total) }}</span>
                        </div>
                    </div>
                    <p v-if="!paymentMethods?.length" class="text-indigo-300 text-center py-8">Belum ada data transaksi</p>
                </div>
            </div>
        </div>

        <!-- Secondary Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Top Destinations -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                    <MapPin class="w-5 h-5 text-blue-500" />
                    Destinasi Terlaris
                </h3>
                <div class="space-y-4">
                    <div v-for="(dest, index) in topDestinations" :key="dest.id"
                        class="flex items-center gap-4 p-4 rounded-2xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100 group">
                        <span class="text-lg font-black text-gray-300 w-6 group-hover:text-blue-500 transition-colors">#{{ index + 1 }}</span>
                        <img :src="dest.primary_image_url" class="w-12 h-12 rounded-xl object-cover shadow-sm">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-900 truncate">{{ dest.name }}</h4>
                            <p class="text-xs text-gray-500">{{ dest.bookings_count }} pemesanan</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-emerald-600">{{ formatCurrency(dest.revenue) }}</p>
                        </div>
                    </div>
                    <div v-if="!topDestinations?.length" class="text-center py-12 text-gray-400 flex flex-col items-center">
                        <MapPin class="w-12 h-12 mb-2 opacity-50" />
                        <p>Belum ada data destinasi</p>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-2 h-6 bg-violet-500 rounded-full"></span>
                        Transaksi Terbaru
                    </h3>
                    <Link href="/admin/bookings" class="text-sm font-bold text-violet-600 hover:text-violet-700 flex items-center gap-1">
                        Lihat Semua <ExternalLink class="w-3 h-3" />
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-xs text-gray-400 uppercase border-b border-gray-100">
                                <th class="pb-3 pl-2">Customer</th>
                                <th class="pb-3">Destinasi</th>
                                <th class="pb-3 text-right">Total</th>
                                <th class="pb-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="booking in recentBookings" :key="booking.id" class="hover:bg-gray-50/50 transition">
                                <td class="py-3 pl-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">
                                            {{ (booking.user?.name || 'G').charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">{{ booking.user?.name || 'Guest' }}</p>
                                            <p class="text-xs text-gray-400">{{ booking.created_at_human }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-sm text-gray-600">{{ booking.destination?.name }}</td>
                                <td class="py-3 text-right font-medium text-gray-900">{{ formatCurrency(booking.total_amount) }}</td>
                                <td class="py-3 text-center">
                                    <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-bold', getStatusColor(booking.status)]">
                                        {{ booking.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!recentBookings?.length">
                                <td colspan="4" class="text-center py-8 text-gray-400">Belum ada transaksi terbaru</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
