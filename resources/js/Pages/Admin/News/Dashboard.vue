<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import {
    Newspaper, Eye, Heart, MessageCircle, Clock, Star,
    TrendingUp, Monitor, Smartphone, Tablet, Globe, BarChart3, Sparkles
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stats: Object,
    viewsChart: Object,
    topNews: Array,
    recentNews: Array,
    topReferrers: Array,
    peakHours: Object,
    deviceBreakdown: Object,
    avgReadTime: Number,
    bounceRate: Number,
    period: String,
});

const period = ref(props.period || '30');
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.stat-card', { opacity: 0, y: 20, scale: 0.95 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' });
        gsap.fromTo('.status-card', { opacity: 0, scale: 0.9 }, { opacity: 1, scale: 1, duration: 0.5, stagger: 0.15, delay: 0.3, ease: 'back.out(1.5)' });
        gsap.fromTo('.chart-section', { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.6, stagger: 0.2, delay: 0.4, ease: 'power2.out' });
        gsap.fromTo('.bottom-section', { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.5, stagger: 0.15, delay: 0.6, ease: 'power2.out' });
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const changePeriod = () => {
    router.get('/admin/news/dashboard', { period: period.value }, { preserveState: true });
};

const formatNumber = (num) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num?.toString() || '0';
};

const formatSeconds = (seconds) => {
    if (seconds >= 60) return Math.round(seconds / 60) + ' menit';
    return Math.round(seconds) + ' detik';
};

const viewsData = computed(() => props.viewsChart?.data || []);
const viewsLabels = computed(() => props.viewsChart?.labels || []);
const chartMax = computed(() => Math.max(...viewsData.value, 1));

const peakData = computed(() => props.peakHours?.data || []);
const peakLabels = computed(() => props.peakHours?.labels || []);
const peakMax = computed(() => Math.max(...peakData.value, 1));
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-rose-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="header-content relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <BarChart3 class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Dashboard Berita</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ formatNumber(stats?.total || 0) }} Total
                            </span>
                        </div>
                        <p class="text-red-100/80 text-xs">Statistik dan analitik berita Anda</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <select v-model="period" @change="changePeriod" 
                        class="px-4 py-2 text-xs rounded-xl border-2 border-white/20 bg-white/10 text-white focus:border-white/40 focus:ring-0 backdrop-blur-sm cursor-pointer">
                        <option value="7" class="text-gray-900">7 Hari Terakhir</option>
                        <option value="30" class="text-gray-900">30 Hari Terakhir</option>
                        <option value="90" class="text-gray-900">90 Hari Terakhir</option>
                    </select>
                    <Link href="/admin/news" class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-red-600 text-xs font-bold hover:bg-red-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg">
                        <Newspaper class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        <span>Kelola Berita</span>
                        <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                        <Newspaper class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(stats?.total || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Berita</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <Eye class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(stats?.total_views || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Views</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-pink-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-500/30 group-hover:scale-110 transition-transform">
                        <Heart class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(stats?.total_likes || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Likes</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <MessageCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(stats?.total_comments || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Komentar</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <Star class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats?.featured || 0 }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Headline</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-3 gap-3">
            <div class="status-card group relative overflow-hidden bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-4 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <p class="text-2xl font-black">{{ stats?.published || 0 }}</p>
                <p class="text-xs font-medium opacity-80">Dipublikasikan</p>
            </div>
            <div class="status-card group relative overflow-hidden bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl p-4 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <p class="text-2xl font-black">{{ stats?.draft || 0 }}</p>
                <p class="text-xs font-medium opacity-80">Draft</p>
            </div>
            <div class="status-card group relative overflow-hidden bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl p-4 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <p class="text-2xl font-black">{{ stats?.scheduled || 0 }}</p>
                <p class="text-xs font-medium opacity-80">Terjadwal</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid lg:grid-cols-2 gap-5">
            <div class="chart-section rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-rose-600 shadow-md">
                        <TrendingUp class="w-4 h-4 text-white" />
                    </div>
                    Views per Hari
                </h3>
                <div v-if="viewsData.length > 0" class="h-48 flex items-end gap-1 px-2">
                    <div v-for="(val, i) in viewsData" :key="i" 
                        class="flex-1 bg-gradient-to-t from-red-500 to-rose-400 rounded-t-md hover:from-red-600 hover:to-rose-500 transition-all cursor-pointer relative group min-w-[8px]"
                        :style="{ height: Math.max((val / chartMax) * 100, 2) + '%' }">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10 shadow-lg">
                            {{ viewsLabels[i] }}: {{ val }}
                        </div>
                    </div>
                </div>
                <div v-else class="h-48 flex items-center justify-center text-gray-400 text-sm">
                    Belum ada data views
                </div>
            </div>

            <div class="chart-section rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 shadow-md">
                        <Clock class="w-4 h-4 text-white" />
                    </div>
                    Jam Pembaca Aktif
                </h3>
                <div v-if="peakData.length > 0" class="h-48 flex items-end gap-0.5 px-2">
                    <div v-for="(val, i) in peakData" :key="i" 
                        class="flex-1 bg-gradient-to-t from-blue-500 to-cyan-400 rounded-t-sm hover:from-blue-600 hover:to-cyan-500 transition-all cursor-pointer relative group min-w-[4px]"
                        :style="{ height: Math.max((val / peakMax) * 100, 2) + '%' }">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10 shadow-lg">
                            {{ peakLabels[i] }}: {{ val }} views
                        </div>
                    </div>
                </div>
                <div v-else class="h-48 flex items-center justify-center text-gray-400 text-sm">
                    Belum ada data jam aktif
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid lg:grid-cols-3 gap-5">
            <div class="bottom-section rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 shadow-md">
                        <Star class="w-4 h-4 text-white" />
                    </div>
                    Top 10 Berita
                </h3>
                <div class="space-y-2.5 max-h-72 overflow-y-auto">
                    <div v-for="(item, i) in topNews" :key="item.id" class="flex items-center gap-3 group p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="w-6 h-6 bg-gradient-to-br from-red-500 to-rose-600 text-white rounded-full flex items-center justify-center text-[10px] font-bold shadow-sm">{{ i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <Link :href="`/admin/news/${item.id}/edit`" class="text-xs font-medium text-gray-900 group-hover:text-red-600 truncate block transition-colors">
                                {{ item.title }}
                            </Link>
                            <p class="text-[10px] text-gray-400">{{ formatNumber(item.views_count) }} views</p>
                        </div>
                    </div>
                    <p v-if="!topNews?.length" class="text-gray-400 text-xs text-center py-4">Belum ada data</p>
                </div>
            </div>

            <div class="bottom-section rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md">
                        <Globe class="w-4 h-4 text-white" />
                    </div>
                    Top Referrers
                </h3>
                <div class="space-y-2.5 max-h-72 overflow-y-auto">
                    <div v-for="ref in topReferrers" :key="ref.domain" class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-xs text-gray-600 truncate">{{ ref.domain || 'Direct' }}</span>
                        <span class="text-xs font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded-full">{{ formatNumber(ref.count) }}</span>
                    </div>
                    <p v-if="!topReferrers?.length" class="text-gray-400 text-xs text-center py-4">Belum ada data</p>
                </div>
            </div>

            <div class="bottom-section space-y-3">
                <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <Monitor class="w-4 h-4 text-gray-500" /> Device Breakdown
                    </h3>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg">
                            <Monitor class="w-3.5 h-3.5 text-gray-500" />
                            <span class="text-xs font-medium">{{ formatNumber(deviceBreakdown?.desktop || 0) }}</span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg">
                            <Smartphone class="w-3.5 h-3.5 text-gray-500" />
                            <span class="text-xs font-medium">{{ formatNumber(deviceBreakdown?.mobile || 0) }}</span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg">
                            <Tablet class="w-3.5 h-3.5 text-gray-500" />
                            <span class="text-xs font-medium">{{ formatNumber(deviceBreakdown?.tablet || 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-900 mb-2">Rata-rata Waktu Baca</h3>
                    <p class="text-xl font-black bg-gradient-to-r from-red-600 to-rose-600 bg-clip-text text-transparent">{{ formatSeconds(avgReadTime) }}</p>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-900 mb-2">Bounce Rate</h3>
                    <p class="text-xl font-black" :class="bounceRate > 50 ? 'text-red-600' : 'text-green-600'">
                        {{ bounceRate }}%
                    </p>
                    <p class="text-[10px] text-gray-400 mt-1">Pengunjung yang keluar dalam &lt;10 detik</p>
                </div>
            </div>
        </div>
    </div>
</template>
