<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { TrendingUp, DollarSign, Receipt, Calendar, Download, MapPin, BarChart3 } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    dailyRevenue: { type: Array, default: () => [] },
    revenueByDestination: { type: Array, default: () => [] },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' }
});

const startDate = ref(props.startDate?.split('T')[0] || new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0]);
const endDate = ref(props.endDate?.split('T')[0] || new Date().toISOString().split('T')[0]);

const applyFilter = () => { router.get('/admin/reports/revenue', { start_date: startDate.value, end_date: endDate.value }); };
const exportPdf = () => { window.open(`/admin/reports/export?start_date=${startDate.value}&end_date=${endDate.value}&action=preview`, '_blank'); };

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);
const maxDailyRevenue = computed(() => Math.max(...props.dailyRevenue.map(d => d.total || 0), 1));
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Laporan Pendapatan</h1>
                        <p class="mt-1 text-emerald-100/90 text-lg">Analisis pendapatan dan transaksi</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Link href="/admin/reports/visitors" class="px-5 py-3 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition-all">Pengunjung</Link>
                    <Link href="/admin/reports/destinations" class="px-5 py-3 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition-all">Destinasi</Link>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <Calendar class="w-5 h-5 text-gray-400" />
                    <input v-model="startDate" type="date" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    <span class="text-gray-500">s/d</span>
                    <input v-model="endDate" type="date" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <button @click="applyFilter" class="px-5 py-2.5 bg-emerald-500 text-white rounded-xl font-bold hover:bg-emerald-600 transition-colors">Terapkan</button>
                <button @click="exportPdf" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-colors flex items-center gap-2">
                    <Download class="w-4 h-4" />Export PDF
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                        <DollarSign class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Pendapatan</p>
                        <p class="text-2xl font-extrabold text-gray-900">{{ formatCurrency(stats.total_revenue) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 shadow-lg">
                        <Receipt class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Transaksi</p>
                        <p class="text-2xl font-extrabold text-gray-900">{{ stats.total_transactions?.toLocaleString() || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg">
                        <BarChart3 class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Rata-rata Transaksi</p>
                        <p class="text-2xl font-extrabold text-gray-900">{{ formatCurrency(stats.average_transaction) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart (Simple CSS Bar) -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2"><BarChart3 class="w-5 h-5 text-emerald-500" />Pendapatan Harian</h2>
            </div>
            <div class="p-6">
                <div v-if="dailyRevenue.length" class="flex items-end gap-1 h-48 overflow-x-auto">
                    <div v-for="day in dailyRevenue" :key="day.date" class="flex-1 min-w-[20px] flex flex-col items-center group">
                        <div class="w-full bg-gradient-to-t from-emerald-500 to-teal-400 rounded-t-lg transition-all hover:from-emerald-600 hover:to-teal-500 relative"
                            :style="{ height: (day.total / maxDailyRevenue * 100) + '%', minHeight: '4px' }">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ formatCurrency(day.total) }}
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 transform -rotate-45 origin-left">{{ new Date(day.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}</p>
                    </div>
                </div>
                <div v-else class="h-48 flex items-center justify-center text-gray-500">Tidak ada data</div>
            </div>
        </div>

        <!-- Top Destinations -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2"><MapPin class="w-5 h-5 text-emerald-500" />Top 5 Destinasi</h2>
            </div>
            <div class="p-6 space-y-4">
                <div v-for="(item, idx) in revenueByDestination" :key="idx" class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">{{ idx + 1 }}</div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ item.destination?.name || 'Unknown' }}</p>
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-1">
                            <div class="bg-emerald-500 h-2 rounded-full" :style="{ width: (item.total / (revenueByDestination[0]?.total || 1) * 100) + '%' }"></div>
                        </div>
                    </div>
                    <p class="font-bold text-emerald-600">{{ formatCurrency(item.total) }}</p>
                </div>
                <div v-if="!revenueByDestination.length" class="text-center py-8 text-gray-500">Tidak ada data</div>
            </div>
        </div>
    </div>
</template>
