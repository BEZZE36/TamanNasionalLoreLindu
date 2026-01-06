<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { BarChart3, MapPin, Users, DollarSign, Calendar, TrendingUp, Star, Download, ArrowLeft, Image, Eye, Award, Building } from 'lucide-vue-next';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    topByBookings: Array,
    topByRevenue: Array,
    totalDestinations: Number,
    activeCount: Number,
    featuredCount: Number,
    inactiveCount: Number,
    totalBookings: Number,
    totalRevenue: Number,
    totalVisitors: Number,
    totalViews: Number,
    totalImages: Number,
    avgRating: Number,
    recentDestinations: Array,
    months: Array,
    bookingsData: Array,
    revenueData: Array,
    destinationsCreated: Array,
    byCategory: Array,
    byCity: Array,
    thisWeekBookings: Number,
    thisWeekRevenue: Number,
    thisMonthBookings: Number,
    thisMonthRevenue: Number,
    avgBookingsPerDestination: Number,
    avgRevenuePerDestination: Number
});

const bookingsChart = ref(null);
const revenueChart = ref(null);

onMounted(() => {
    if (bookingsChart.value) {
        new Chart(bookingsChart.value, {
            type: 'line',
            data: {
                labels: props.months,
                datasets: [
                    { label: 'Booking', data: props.bookingsData, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.4, fill: true },
                    { label: 'Destinasi Dibuat', data: props.destinationsCreated, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', tension: 0.4, fill: true }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
    if (revenueChart.value) {
        new Chart(revenueChart.value, {
            type: 'bar',
            data: {
                labels: props.months,
                datasets: [{ label: 'Pendapatan', data: props.revenueData, backgroundColor: 'rgba(16,185,129,0.8)', borderRadius: 8 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const formatCurrency = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 via-green-500 to-teal-500 rounded-xl flex items-center justify-center shadow-xl shadow-emerald-500/30">
                    <BarChart3 class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Statistik <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-green-600">Destinasi</span></h1>
                    <p class="text-gray-500 mt-1">Analisis performa destinasi wisata</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link href="/admin/destinations" class="px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" /> Kembali
                </Link>
                <a href="/admin/destinations/export/csv" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl hover:from-emerald-600 hover:to-green-700 shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
                    <Download class="w-5 h-5" /> Export CSV
                </a>
            </div>
        </div>

        <!-- Period Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/25">
                <div class="flex items-center gap-3 mb-2"><Calendar class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Minggu ini</span></div>
                <div class="text-xl font-bold">{{ thisWeekBookings }}</div>
                <div class="text-sm opacity-80">{{ formatCurrency(thisWeekRevenue) }}</div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl p-5 text-white shadow-lg shadow-purple-500/25">
                <div class="flex items-center gap-3 mb-2"><Calendar class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Bulan ini</span></div>
                <div class="text-xl font-bold">{{ thisMonthBookings }}</div>
                <div class="text-sm opacity-80">{{ formatCurrency(thisMonthRevenue) }}</div>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/25">
                <div class="flex items-center gap-3 mb-2"><TrendingUp class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Avg Booking</span></div>
                <div class="text-xl font-bold">{{ avgBookingsPerDestination }}</div>
                <div class="text-sm opacity-80">per destinasi</div>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/25">
                <div class="flex items-center gap-3 mb-2"><Star class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Avg Rating</span></div>
                <div class="text-xl font-bold">{{ Number(avgRating).toFixed(1) }}</div>
                <div class="text-sm opacity-80">dari 5.0</div>
            </div>
        </div>

        <!-- Main Stats -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-emerald-500 mb-3"><MapPin class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ totalDestinations }}</div><div class="text-[11px] text-gray-500">Total Destinasi</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-blue-500 mb-3"><Users class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ formatNumber(totalBookings) }}</div><div class="text-[11px] text-gray-500">Total Booking</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-green-500 mb-3"><DollarSign class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ formatCurrency(totalRevenue) }}</div><div class="text-[11px] text-gray-500">Total Pendapatan</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-purple-500 mb-3"><Users class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ formatNumber(totalVisitors) }}</div><div class="text-[11px] text-gray-500">Total Pengunjung</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-pink-500 mb-3"><Eye class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ formatNumber(totalViews) }}</div><div class="text-[11px] text-gray-500">Total Views</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-amber-500 mb-3"><Image class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ totalImages }}</div><div class="text-[11px] text-gray-500">Total Gambar</div></div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Booking & Destinasi (6 Bulan)</h3>
                <canvas ref="bookingsChart"></canvas>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Pendapatan per Bulan</h3>
                <canvas ref="revenueChart"></canvas>
            </div>
        </div>

        <!-- Top Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Top by Bookings -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-green-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Award class="w-5 h-5 text-emerald-500" /> Top 10 Booking Terbanyak</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="(d, i) in topByBookings" :key="d.id" class="px-6 py-3 flex items-center gap-4 hover:bg-gray-50 transition-all">
                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm', i < 3 ? 'bg-gradient-to-br from-emerald-500 to-green-600 text-white' : 'bg-gray-100 text-gray-600']">{{ i + 1 }}</div>
                        <div class="flex-1 min-w-0"><div class="font-semibold text-gray-900 truncate">{{ d.name }}</div><div class="text-xs text-gray-400 flex items-center gap-1"><Star class="w-3 h-3 text-yellow-500" /> {{ Number(d.rating || 0).toFixed(1) }}</div></div>
                        <div class="text-right"><div class="font-bold text-emerald-600">{{ d.bookings_count }}</div><div class="text-xs text-gray-400">booking</div></div>
                    </div>
                </div>
            </div>

            <!-- Top by Revenue -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-violet-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><DollarSign class="w-5 h-5 text-purple-500" /> Top 10 Pendapatan Tertinggi</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="(d, i) in topByRevenue" :key="d.id" class="px-6 py-3 flex items-center gap-4 hover:bg-gray-50 transition-all">
                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm', i < 3 ? 'bg-gradient-to-br from-purple-500 to-violet-600 text-white' : 'bg-gray-100 text-gray-600']">{{ i + 1 }}</div>
                        <div class="flex-1 min-w-0"><div class="font-semibold text-gray-900 truncate">{{ d.name }}</div></div>
                        <div class="text-right"><div class="font-bold text-purple-600">{{ formatCurrency(d.total_revenue) }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- By Category & City -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><MapPin class="w-5 h-5 text-blue-500" /> Per Kategori</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="cat in byCategory" :key="cat.category_id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition-all">
                        <span class="font-medium text-gray-700">{{ cat.category?.name || 'Tanpa Kategori' }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-blue-600">{{ cat.count }}</span>
                            <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full" :style="{width: `${(cat.count/totalDestinations)*100}%`}"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Building class="w-5 h-5 text-amber-500" /> Top 10 Kota</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="city in byCity" :key="city.city" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition-all">
                        <span class="font-medium text-gray-700">{{ city.city || 'Tidak diketahui' }}</span>
                        <span class="text-sm font-semibold text-amber-600">{{ city.count }} destinasi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
