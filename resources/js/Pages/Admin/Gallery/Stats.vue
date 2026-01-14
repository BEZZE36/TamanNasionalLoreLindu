<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { BarChart3, Image, Video, Eye, Calendar, TrendingUp, Star, Download, ArrowLeft, FolderOpen, Clock, Award } from 'lucide-vue-next';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    topViewed: Array,
    totalGalleries: Number,
    totalViews: Number,
    totalMedia: Number,
    totalImages: Number,
    totalVideos: Number,
    featuredCount: Number,
    activeCount: Number,
    inactiveCount: Number,
    recentGalleries: Array,
    months: Array,
    viewsData: Array,
    galleriesCreated: Array,
    byCategory: Array,
    topByMediaCount: Array,
    avgViewsPerGallery: Number,
    avgMediaPerGallery: Number,
    thisWeekGalleries: Number,
    thisWeekViews: Number,
    thisMonthGalleries: Number,
    thisMonthViews: Number
});

const viewsChart = ref(null);
const mediaChart = ref(null);

onMounted(() => {
    // Views & Created Chart
    if (viewsChart.value) {
        new Chart(viewsChart.value, {
            type: 'line',
            data: {
                labels: props.months,
                datasets: [
                    { label: 'Views', data: props.viewsData, borderColor: '#ec4899', backgroundColor: 'rgba(236,72,153,0.1)', tension: 0.4, fill: true },
                    { label: 'Gallery Dibuat', data: props.galleriesCreated, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', tension: 0.4, fill: true }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
    // Media Type Chart
    if (mediaChart.value) {
        new Chart(mediaChart.value, {
            type: 'doughnut',
            data: {
                labels: ['Gambar', 'Video'],
                datasets: [{ data: [props.totalImages, props.totalVideos], backgroundColor: ['#f472b6', '#a78bfa'], borderWidth: 0 }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-pink-400 via-rose-500 to-red-500 rounded-xl flex items-center justify-center shadow-xl shadow-pink-500/30">
                    <BarChart3 class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Statistik <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-600">Gallery</span></h1>
                    <p class="text-gray-500 mt-1">Analisis performa galeri</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link href="/admin/gallery" class="px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" /> Kembali
                </Link>
                <a href="/admin/gallery/export/csv" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-pink-500 to-rose-600 rounded-xl hover:from-pink-600 hover:to-rose-700 shadow-lg shadow-pink-500/25 transition-all flex items-center gap-2">
                    <Download class="w-5 h-5" /> Export CSV
                </a>
            </div>
        </div>

        <!-- Period Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/25">
                <div class="flex items-center gap-3 mb-2"><Calendar class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Minggu ini</span></div>
                <div class="text-xl font-bold">{{ thisWeekGalleries }}</div>
                <div class="text-sm opacity-80">{{ formatNumber(thisWeekViews) }} views</div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl p-5 text-white shadow-lg shadow-purple-500/25">
                <div class="flex items-center gap-3 mb-2"><Calendar class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Bulan ini</span></div>
                <div class="text-xl font-bold">{{ thisMonthGalleries }}</div>
                <div class="text-sm opacity-80">{{ formatNumber(thisMonthViews) }} views</div>
            </div>
            <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg shadow-pink-500/25">
                <div class="flex items-center gap-3 mb-2"><TrendingUp class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Avg Views</span></div>
                <div class="text-xl font-bold">{{ avgViewsPerGallery }}</div>
                <div class="text-sm opacity-80">per gallery</div>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/25">
                <div class="flex items-center gap-3 mb-2"><Image class="w-5 h-5 opacity-80" /><span class="text-sm font-medium opacity-90">Avg Media</span></div>
                <div class="text-xl font-bold">{{ avgMediaPerGallery }}</div>
                <div class="text-sm opacity-80">per gallery</div>
            </div>
        </div>

        <!-- Main Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-pink-500 mb-3"><FolderOpen class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ totalGalleries }}</div><div class="text-[11px] text-gray-500">Total Gallery</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-green-500 mb-3"><Eye class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ formatNumber(totalViews) }}</div><div class="text-[11px] text-gray-500">Total Views</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-blue-500 mb-3"><Image class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ totalImages }}</div><div class="text-[11px] text-gray-500">Total Gambar</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-purple-500 mb-3"><Video class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ totalVideos }}</div><div class="text-[11px] text-gray-500">Total Video</div></div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm"><div class="flex items-center gap-3 text-amber-500 mb-3"><Star class="w-6 h-6" /></div><div class="text-2xl font-black text-gray-900">{{ featuredCount }}</div><div class="text-[11px] text-gray-500">Unggulan</div></div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Tren 6 Bulan Terakhir</h3>
                <canvas ref="viewsChart"></canvas>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Distribusi Media</h3>
                <canvas ref="mediaChart"></canvas>
            </div>
        </div>

        <!-- Lists Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Top Viewed -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-pink-50 to-rose-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Award class="w-5 h-5 text-pink-500" /> Top 10 Gallery</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="(g, i) in topViewed" :key="g.id" class="px-6 py-3 flex items-center gap-4 hover:bg-gray-50 transition-all">
                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm', i < 3 ? 'bg-gradient-to-br from-pink-500 to-rose-600 text-white' : 'bg-gray-100 text-gray-600']">{{ i + 1 }}</div>
                        <div class="flex-1 min-w-0"><div class="font-semibold text-gray-900 truncate">{{ g.title }}</div><div class="text-xs text-gray-400">{{ formatDate(g.created_at) }}</div></div>
                        <div class="text-right"><div class="font-bold text-pink-600">{{ formatNumber(g.view_count) }}</div><div class="text-xs text-gray-400">views</div></div>
                    </div>
                </div>
            </div>

            <!-- By Category -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-violet-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><FolderOpen class="w-5 h-5 text-purple-500" /> Per Kategori</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="cat in byCategory" :key="cat.gallery_category_id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition-all">
                        <span class="font-medium text-gray-700">{{ cat.category?.name || 'Tanpa Kategori' }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-purple-600">{{ cat.count }} gallery</span>
                            <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-purple-500 to-violet-600 rounded-full" :style="{width: `${(cat.count/totalGalleries)*100}%`}"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Galleries -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Clock class="w-5 h-5 text-blue-500" /> Gallery Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 text-left">
                        <tr><th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Gallery</th><th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Media</th><th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Views</th><th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th><th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="g in recentGalleries" :key="g.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ g.title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ g.media_count }} item</td>
                            <td class="px-6 py-4 text-pink-600 font-semibold">{{ formatNumber(g.view_count) }}</td>
                            <td class="px-4 py-3"><span :class="g.is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 text-xs font-bold rounded-full">{{ g.is_active ? 'Aktif' : 'Draft' }}</span></td>
                            <td class="px-4 py-3 text-gray-500 text-sm">{{ formatDate(g.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
