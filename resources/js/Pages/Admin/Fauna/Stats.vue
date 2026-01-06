<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { BarChart3, Bird, Image, Eye, Calendar, TrendingUp, Star, ArrowLeft, Award, Clock, Shield } from 'lucide-vue-next';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    topViewed: Array,
    totalFauna: Number,
    totalViews: Number,
    totalImages: Number,
    featuredCount: Number,
    activeCount: Number,
    inactiveCount: Number,
    recentFauna: Array,
    months: Array,
    viewsData: Array,
    faunaCreated: Array,
    byCategory: Array,
    byConservation: Array,
    topByMediaCount: Array,
    avgViewsPerFauna: Number,
    avgMediaPerFauna: Number,
    thisWeekFauna: Number,
    thisWeekViews: Number,
    thisMonthFauna: Number,
    thisMonthViews: Number
});

const viewsChart = ref(null);
const categoryChart = ref(null);

const categoryLabels = { umum: 'Umum', langka: 'Langka', endemik: 'Endemik' };
const conservationLabels = { LC: 'Risiko Rendah', NT: 'Hampir Terancam', VU: 'Rentan', EN: 'Terancam', CR: 'Kritis' };
const conservationColors = { LC: '#22c55e', NT: '#3b82f6', VU: '#eab308', EN: '#f97316', CR: '#ef4444' };

onMounted(() => {
    if (viewsChart.value) {
        new Chart(viewsChart.value, {
            type: 'line',
            data: {
                labels: props.months,
                datasets: [
                    { label: 'Views', data: props.viewsData, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', tension: 0.4, fill: true },
                    { label: 'Fauna Ditambahkan', data: props.faunaCreated, borderColor: '#eab308', backgroundColor: 'rgba(234,179,8,0.1)', tension: 0.4, fill: true }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
    if (categoryChart.value && props.byCategory?.length) {
        new Chart(categoryChart.value, {
            type: 'doughnut',
            data: {
                labels: props.byCategory.map(c => categoryLabels[c.category] || c.category || 'Lainnya'),
                datasets: [{ data: props.byCategory.map(c => c.count), backgroundColor: ['#f97316', '#eab308', '#ef4444'], borderWidth: 0 }]
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
        <!-- Premium Header -->
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-orange-500 to-rose-600 rounded-3xl p-8 shadow-2xl shadow-orange-500/25">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-xl rounded-xl flex items-center justify-center ring-4 ring-white/20">
                        <BarChart3 class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight">Statistik Fauna</h1>
                        <p class="text-amber-100">Analisis lengkap data fauna taman nasional</p>
                    </div>
                </div>
                <Link href="/admin/fauna" class="group inline-flex items-center gap-3 px-4 py-2.5 bg-white/20 backdrop-blur-xl text-white font-bold rounded-xl border border-white/30 hover:bg-white/30 transition-all">
                    <ArrowLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                    <span>Kembali</span>
                </Link>
            </div>
            
            <!-- Period Stats in Header -->
            <div class="relative grid grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><Calendar class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ thisWeekFauna }}</p><p class="text-xs text-amber-100">Minggu ini</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><Calendar class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ thisMonthFauna }}</p><p class="text-xs text-amber-100">Bulan ini</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><TrendingUp class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ avgViewsPerFauna }}</p><p class="text-xs text-amber-100">Avg Views</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><Image class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ avgMediaPerFauna }}</p><p class="text-xs text-amber-100">Avg Gambar</p></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="group bg-white/80 backdrop-blur-xl rounded-2xl p-5 border border-gray-100/50 shadow-lg shadow-gray-200/50 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-400/20 to-rose-500/20 rounded-xl flex items-center justify-center mb-3"><Bird class="w-6 h-6 text-orange-600" /></div>
                <div class="text-2xl font-black text-gray-900">{{ totalFauna }}</div>
                <div class="text-[11px] text-gray-500">Total Fauna</div>
            </div>
            <div class="group bg-white/80 backdrop-blur-xl rounded-2xl p-5 border border-gray-100/50 shadow-lg shadow-gray-200/50 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-gradient-to-br from-green-400/20 to-emerald-500/20 rounded-xl flex items-center justify-center mb-3"><Eye class="w-6 h-6 text-green-600" /></div>
                <div class="text-2xl font-black text-gray-900">{{ formatNumber(totalViews) }}</div>
                <div class="text-[11px] text-gray-500">Total Views</div>
            </div>
            <div class="group bg-white/80 backdrop-blur-xl rounded-2xl p-5 border border-gray-100/50 shadow-lg shadow-gray-200/50 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400/20 to-indigo-500/20 rounded-xl flex items-center justify-center mb-3"><Image class="w-6 h-6 text-blue-600" /></div>
                <div class="text-2xl font-black text-gray-900">{{ totalImages }}</div>
                <div class="text-[11px] text-gray-500">Total Gambar</div>
            </div>
            <div class="group bg-white/80 backdrop-blur-xl rounded-2xl p-5 border border-gray-100/50 shadow-lg shadow-gray-200/50 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400/20 to-orange-500/20 rounded-xl flex items-center justify-center mb-3"><Star class="w-6 h-6 text-amber-600" /></div>
                <div class="text-2xl font-black text-gray-900">{{ featuredCount }}</div>
                <div class="text-[11px] text-gray-500">Unggulan</div>
            </div>
            <div class="group bg-white/80 backdrop-blur-xl rounded-2xl p-5 border border-gray-100/50 shadow-lg shadow-gray-200/50 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400/20 to-teal-500/20 rounded-xl flex items-center justify-center mb-3"><Shield class="w-6 h-6 text-emerald-600" /></div>
                <div class="text-2xl font-black text-gray-900">{{ activeCount }}</div>
                <div class="text-[11px] text-gray-500">Aktif</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-2 bg-white/80 backdrop-blur-xl rounded-2xl p-6 border border-gray-100/50 shadow-lg shadow-gray-200/50">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><TrendingUp class="w-5 h-5 text-orange-500" />Tren 6 Bulan Terakhir</h3>
                <canvas ref="viewsChart"></canvas>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 border border-gray-100/50 shadow-lg shadow-gray-200/50">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><Bird class="w-5 h-5 text-amber-500" />Distribusi Kategori</h3>
                <canvas ref="categoryChart"></canvas>
            </div>
        </div>

        <!-- Lists Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Top Viewed -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-gray-100/50 shadow-lg shadow-gray-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Award class="w-5 h-5 text-orange-500" />Top 10 Fauna Populer</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="(f, i) in topViewed" :key="f.id" class="px-6 py-3 flex items-center gap-4 hover:bg-gradient-to-r hover:from-orange-50/50 hover:to-transparent transition-all">
                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm', i < 3 ? 'bg-gradient-to-br from-orange-500 to-rose-600 text-white shadow-lg shadow-orange-500/30' : 'bg-gray-100 text-gray-600']">{{ i + 1 }}</div>
                        <div class="flex-1 min-w-0"><div class="font-semibold text-gray-900 truncate">{{ f.name }}</div><div class="text-xs text-gray-400">{{ formatDate(f.created_at) }}</div></div>
                        <div class="text-right"><div class="font-bold text-orange-600">{{ formatNumber(f.view_count) }}</div><div class="text-xs text-gray-400">views</div></div>
                    </div>
                </div>
            </div>

            <!-- Conservation Status -->
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-gray-100/50 shadow-lg shadow-gray-200/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-rose-50 to-orange-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Shield class="w-5 h-5 text-rose-500" />Status Konservasi</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="c in byConservation" :key="c.conservation_status" class="px-6 py-4 flex items-center justify-between hover:bg-gradient-to-r hover:from-rose-50/50 hover:to-transparent transition-all">
                        <div class="flex items-center gap-3">
                            <div :style="{backgroundColor: conservationColors[c.conservation_status] || '#9ca3af'}" class="w-4 h-4 rounded-lg shadow-sm"></div>
                            <span class="font-medium text-gray-700">{{ conservationLabels[c.conservation_status] || c.conservation_status || 'Tidak diketahui' }}</span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-700">{{ c.count }} fauna</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Fauna -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-gray-100/50 shadow-lg shadow-gray-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Clock class="w-5 h-5 text-blue-500" />Fauna Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/80">
                        <tr><th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama</th><th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Gambar</th><th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Views</th><th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th><th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="f in recentFauna" :key="f.id" class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-transparent transition-all">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ f.name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ f.images_count }} item</td>
                            <td class="px-6 py-4 text-orange-600 font-bold">{{ formatNumber(f.view_count) }}</td>
                            <td class="px-4 py-3"><span :class="f.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" class="px-3 py-1 text-xs font-bold rounded-full">{{ f.is_active ? 'Aktif' : 'Draft' }}</span></td>
                            <td class="px-4 py-3 text-gray-500 text-sm">{{ formatDate(f.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
