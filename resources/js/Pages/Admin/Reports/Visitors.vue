<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Users, Ticket, TrendingUp, Calendar, Download, MapPin, BarChart3, Percent } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    dailyVisitors: { type: Array, default: () => [] },
    visitorsByDestination: { type: Array, default: () => [] },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' }
});

const startDate = ref(props.startDate?.split('T')[0] || new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0]);
const endDate = ref(props.endDate?.split('T')[0] || new Date().toISOString().split('T')[0]);

const applyFilter = () => { router.get('/admin/reports/visitors', { start_date: startDate.value, end_date: endDate.value }); };
const maxDailyVisitors = computed(() => Math.max(...props.dailyVisitors.map(d => d.count || 0), 1));
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <Users class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Laporan Pengunjung</h1>
                        <p class="mt-1 text-blue-100/90 text-lg">Analisis traffic dan tiket</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Link href="/admin/reports/revenue" class="px-5 py-3 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition-all">Pendapatan</Link>
                    <Link href="/admin/reports/destinations" class="px-5 py-3 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition-all">Destinasi</Link>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <Calendar class="w-5 h-5 text-gray-400" />
                    <input v-model="startDate" type="date" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    <span class="text-gray-500">s/d</span>
                    <input v-model="endDate" type="date" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                </div>
                <button @click="applyFilter" class="px-5 py-2.5 bg-blue-500 text-white rounded-xl font-bold hover:bg-blue-600 transition-colors">Terapkan</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 shadow-lg">
                        <Users class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Pengunjung</p>
                        <p class="text-2xl font-extrabold text-gray-900">{{ stats.total_visitors?.toLocaleString() || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tiket Terjual</p>
                        <p class="text-2xl font-extrabold text-gray-900">{{ stats.total_tickets_sold?.toLocaleString() || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 hover:shadow-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg">
                        <Percent class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tingkat Penggunaan</p>
                        <p class="text-2xl font-extrabold text-gray-900">{{ stats.ticket_usage_rate || 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2"><BarChart3 class="w-5 h-5 text-blue-500" />Pengunjung Harian</h2>
            </div>
            <div class="p-6">
                <div v-if="dailyVisitors.length" class="flex items-end gap-1 h-48 overflow-x-auto">
                    <div v-for="day in dailyVisitors" :key="day.date" class="flex-1 min-w-[20px] flex flex-col items-center group">
                        <div class="w-full bg-gradient-to-t from-blue-500 to-indigo-400 rounded-t-lg transition-all hover:from-blue-600 hover:to-indigo-500 relative"
                            :style="{ height: (day.count / maxDailyVisitors * 100) + '%', minHeight: '4px' }">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ day.count.toLocaleString() }}
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
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2"><MapPin class="w-5 h-5 text-blue-500" />Top 5 Destinasi</h2>
            </div>
            <div class="p-6 space-y-4">
                <div v-for="(item, idx) in visitorsByDestination" :key="idx" class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">{{ idx + 1 }}</div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ item.destination?.name || 'Unknown' }}</p>
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-1">
                            <div class="bg-blue-500 h-2 rounded-full" :style="{ width: (item.count / (visitorsByDestination[0]?.count || 1) * 100) + '%' }"></div>
                        </div>
                    </div>
                    <p class="font-bold text-blue-600">{{ item.count?.toLocaleString() || 0 }} tiket</p>
                </div>
                <div v-if="!visitorsByDestination.length" class="text-center py-8 text-gray-500">Tidak ada data</div>
            </div>
        </div>
    </div>
</template>
