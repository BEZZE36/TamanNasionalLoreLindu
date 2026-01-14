<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    BarChart3, Megaphone, Eye, MousePointer, XCircle, TrendingUp, 
    ArrowLeft, Award, Target, Users, Clock, Sparkles
} from 'lucide-vue-next';
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
const distributionChart = ref(null);
let chartInstance = null;
let pieInstance = null;
let ctx;

onMounted(() => {
    nextTick(() => {
        ctx = gsap.context(() => {
            // Animate stats cards
            gsap.fromTo('.stat-card', 
                { opacity: 0, y: 20, scale: 0.95 }, 
                { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
            );
            
            // Animate table rows
            gsap.fromTo('.table-row', 
                { opacity: 0, x: -20 }, 
                { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.5, ease: 'power2.out' }
            );
        });

        // Performance Bar Chart
        if (performanceChart.value && props.announcements?.length) {
            chartInstance = new Chart(performanceChart.value, {
                type: 'bar',
                data: {
                    labels: props.announcements.slice(0, 10).map(a => a.title?.substring(0, 15) + '...'),
                    datasets: [
                        { 
                            label: 'Views', 
                            data: props.announcements.slice(0, 10).map(a => a.view_count || 0), 
                            backgroundColor: 'rgba(139, 92, 246, 0.8)',
                            borderColor: 'rgb(139, 92, 246)',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        { 
                            label: 'Clicks', 
                            data: props.announcements.slice(0, 10).map(a => a.click_count || 0), 
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        { 
                            label: 'Dismisses', 
                            data: props.announcements.slice(0, 10).map(a => a.dismiss_count || 0), 
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            borderColor: 'rgb(245, 158, 11)',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: { size: 11, weight: 'bold' }
                            }
                        } 
                    }, 
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 9 } }
                        }
                    } 
                }
            });
        }

        // Distribution Pie Chart
        if (distributionChart.value && props.announcements?.length) {
            const typeCount = {};
            props.announcements.forEach(a => {
                typeCount[a.type] = (typeCount[a.type] || 0) + 1;
            });

            pieInstance = new Chart(distributionChart.value, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(typeCount).map(t => t.charAt(0).toUpperCase() + t.slice(1)),
                    datasets: [{
                        data: Object.values(typeCount),
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(20, 184, 166, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: { size: 10, weight: 'bold' }
                            }
                        }
                    }
                }
            });
        }
    });
});

onBeforeUnmount(() => { 
    if (ctx) ctx.revert();
    if (chartInstance) chartInstance.destroy();
    if (pieInstance) pieInstance.destroy();
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const getTypeColor = (type) => {
    const colors = {
        banner: 'from-blue-500 to-indigo-600',
        fullscreen: 'from-purple-500 to-fuchsia-600',
        popup: 'from-violet-500 to-purple-600',
        floating: 'from-pink-500 to-rose-600',
        marquee: 'from-teal-500 to-cyan-600'
    };
    return colors[type] || 'from-gray-500 to-gray-600';
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 p-6 shadow-2xl">
            <!-- Animated Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-purple-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-fuchsia-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <BarChart3 class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight">Statistik Pengumuman</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ announcements?.length || 0 }} Total
                            </span>
                        </div>
                        <p class="text-purple-100/80 text-xs">Analisis performa dan engagement pengumuman</p>
                    </div>
                </div>
                
                <Link 
                    href="/admin/announcements" 
                    class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/20 backdrop-blur-sm text-white text-xs font-bold hover:bg-white/30 transition-all"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Kembali</span>
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Total Announcements -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <Megaphone class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ announcements?.length || 0 }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total</p>
                    </div>
                </div>
            </div>
            
            <!-- Total Views -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Eye class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(totalViews) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Views</p>
                    </div>
                </div>
            </div>
            
            <!-- Total Clicks -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <MousePointer class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(totalClicks) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Clicks</p>
                    </div>
                </div>
            </div>
            
            <!-- Total Dismisses -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <XCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(totalDismisses) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Dismisses</p>
                    </div>
                </div>
            </div>
            
            <!-- Click Rate -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-pink-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-500/30 group-hover:scale-110 transition-transform">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ avgClickRate }}%</p>
                        <p class="text-[10px] text-gray-500 font-medium">Click Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Performance Chart -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                        <BarChart3 class="w-4 h-4 text-white" />
                    </div>
                    Performa Top 10 Pengumuman
                </h3>
                <div class="h-64">
                    <canvas ref="performanceChart"></canvas>
                </div>
            </div>

            <!-- Distribution Chart -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center">
                        <Target class="w-4 h-4 text-white" />
                    </div>
                    Distribusi Tipe
                </h3>
                <div class="h-48">
                    <canvas ref="distributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-violet-50 via-purple-50 to-fuchsia-50">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <Award class="w-5 h-5 text-violet-500" />
                    Semua Pengumuman
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Judul</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tipe</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-600 uppercase tracking-wider">Views</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-600 uppercase tracking-wider">Clicks</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-600 uppercase tracking-wider">Dismisses</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-600 uppercase tracking-wider">CTR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="(a, i) in announcements" 
                            :key="a.id" 
                            class="table-row hover:bg-gradient-to-r hover:from-violet-50/50 hover:to-purple-50/30 transition-all"
                        >
                            <td class="px-4 py-3">
                                <span class="w-6 h-6 flex items-center justify-center bg-gray-100 rounded-lg text-[10px] font-bold text-gray-500">{{ i + 1 }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs font-semibold text-gray-900 line-clamp-1">{{ a.title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="[
                                    'inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold text-white bg-gradient-to-r',
                                    getTypeColor(a.type)
                                ]">
                                    {{ a.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-bold text-violet-600">{{ formatNumber(a.view_count) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-bold text-emerald-600">{{ formatNumber(a.click_count) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-bold text-amber-600">{{ formatNumber(a.dismiss_count) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="px-2 py-1 bg-gray-100 rounded-lg text-[10px] font-bold text-gray-700">
                                    {{ a.view_count > 0 ? ((a.click_count / a.view_count) * 100).toFixed(1) : 0 }}%
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="!announcements?.length">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-100 to-purple-200 flex items-center justify-center mb-4">
                                        <BarChart3 class="w-8 h-8 text-violet-500" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada data</p>
                                    <p class="text-xs text-gray-400">Statistik akan muncul setelah ada pengumuman</p>
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
.stat-card {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
