<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { BarChart3, Megaphone, Eye, MousePointer, XCircle, TrendingUp, ArrowLeft, Award } from 'lucide-vue-next';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    announcements: Array,
    totalViews: Number,
    totalClicks: Number,
    totalDismisses: Number,
    avgClickRate: Number,
    avgDismissRate: Number
});

const performanceChart = ref(null);

onMounted(() => {
    if (performanceChart.value && props.announcements?.length) {
        new Chart(performanceChart.value, {
            type: 'bar',
            data: {
                labels: props.announcements.slice(0, 10).map(a => a.title?.substring(0, 20) + '...'),
                datasets: [
                    { label: 'Views', data: props.announcements.slice(0, 10).map(a => a.view_count || 0), backgroundColor: '#8b5cf6' },
                    { label: 'Clicks', data: props.announcements.slice(0, 10).map(a => a.click_count || 0), backgroundColor: '#10b981' },
                    { label: 'Dismisses', data: props.announcements.slice(0, 10).map(a => a.dismiss_count || 0), backgroundColor: '#f59e0b' }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-600 rounded-xl flex items-center justify-center shadow-xl shadow-purple-500/30">
                    <BarChart3 class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Statistik <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-500 to-fuchsia-600">Pengumuman</span></h1>
                    <p class="text-gray-500 mt-1">Analisis performa pengumuman</p>
                </div>
            </div>
            <Link href="/admin/announcements" class="px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all flex items-center gap-2">
                <ArrowLeft class="w-4 h-4" /> Kembali
            </Link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-violet-500/25">
                <div class="flex items-center gap-3 mb-2"><Megaphone class="w-5 h-5 opacity-80" /></div>
                <div class="text-xl font-bold">{{ announcements?.length || 0 }}</div>
                <div class="text-sm opacity-80">Total Pengumuman</div>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/25">
                <div class="flex items-center gap-3 mb-2"><Eye class="w-5 h-5 opacity-80" /></div>
                <div class="text-xl font-bold">{{ formatNumber(totalViews) }}</div>
                <div class="text-sm opacity-80">Total Views</div>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/25">
                <div class="flex items-center gap-3 mb-2"><MousePointer class="w-5 h-5 opacity-80" /></div>
                <div class="text-xl font-bold">{{ formatNumber(totalClicks) }}</div>
                <div class="text-sm opacity-80">Total Clicks</div>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/25">
                <div class="flex items-center gap-3 mb-2"><XCircle class="w-5 h-5 opacity-80" /></div>
                <div class="text-xl font-bold">{{ formatNumber(totalDismisses) }}</div>
                <div class="text-sm opacity-80">Total Dismisses</div>
            </div>
            <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg shadow-pink-500/25">
                <div class="flex items-center gap-3 mb-2"><TrendingUp class="w-5 h-5 opacity-80" /></div>
                <div class="text-xl font-bold">{{ avgClickRate }}%</div>
                <div class="text-sm opacity-80">Click Rate</div>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Performa Top 10 Pengumuman</h3>
            <canvas ref="performanceChart"></canvas>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-violet-50 to-purple-50">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Award class="w-5 h-5 text-violet-500" /> Semua Pengumuman</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Judul</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Views</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Clicks</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Dismisses</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">CTR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(a, i) in announcements" :key="a.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ i + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ a.title }}</td>
                            <td class="px-4 py-3"><span :class="['px-2 py-1 text-xs font-bold rounded-full', a.type === 'banner' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700']">{{ a.type }}</span></td>
                            <td class="px-6 py-4 text-right text-violet-600 font-semibold">{{ formatNumber(a.view_count) }}</td>
                            <td class="px-6 py-4 text-right text-emerald-600 font-semibold">{{ formatNumber(a.click_count) }}</td>
                            <td class="px-6 py-4 text-right text-amber-600 font-semibold">{{ formatNumber(a.dismiss_count) }}</td>
                            <td class="px-6 py-4 text-right text-gray-700">{{ a.view_count > 0 ? ((a.click_count / a.view_count) * 100).toFixed(1) : 0 }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
