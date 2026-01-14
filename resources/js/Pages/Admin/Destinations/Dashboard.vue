<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { MapPin, Eye, Heart, MessageCircle, Star, TrendingUp, BarChart3, Sparkles, Ticket, Users, Car } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    viewsChart: { type: Object, default: () => ({}) },
    topDestinations: { type: Array, default: () => [] },
    recentDestinations: { type: Array, default: () => [] },
    bookingStats: { type: Object, default: () => ({}) },
    period: { type: String, default: '30' }
});

const period = ref(props.period);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.stat-card', { opacity: 0, y: 20, scale: 0.95 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' });
        gsap.fromTo('.status-card', { opacity: 0, scale: 0.9 }, { opacity: 1, scale: 1, duration: 0.5, stagger: 0.15, delay: 0.3, ease: 'back.out(1.5)' });
        gsap.fromTo('.chart-section', { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.6, delay: 0.4, ease: 'power2.out' });
        gsap.fromTo('.bottom-section', { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.5, stagger: 0.15, delay: 0.6, ease: 'power2.out' });
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const changePeriod = () => router.get('/admin/destinations/dashboard', { period: period.value }, { preserveState: true });

const formatNumber = (num) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num?.toString() || '0';
};

const formatCurrency = (num) => {
    if (num >= 1000000) return 'Rp ' + (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return 'Rp ' + (num / 1000).toFixed(1) + 'K';
    return 'Rp ' + (num?.toString() || '0');
};

const viewsData = computed(() => props.viewsChart?.data || []);
const viewsLabels = computed(() => props.viewsChart?.labels || []);
const chartMax = computed(() => Math.max(...viewsData.value, 1));
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-emerald-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
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
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Dashboard Destinasi</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ formatNumber(stats?.total || 0) }} Lokasi
                            </span>
                        </div>
                        <p class="text-emerald-100/80 text-xs">Statistik & analitik destinasi wisata</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <select v-model="period" @change="changePeriod" class="px-4 py-2 text-xs rounded-xl border-2 border-white/20 bg-white/10 text-white focus:border-white/40 focus:ring-0 backdrop-blur-sm cursor-pointer">
                        <option value="7" class="text-gray-900">7 Hari Terakhir</option>
                        <option value="30" class="text-gray-900">30 Hari Terakhir</option>
                        <option value="90" class="text-gray-900">90 Hari Terakhir</option>
                    </select>
                    <Link href="/admin/destinations" class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-600 text-xs font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg">
                        <MapPin class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        <span>Kelola Destinasi</span>
                        <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-green-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <MapPin class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(stats?.total || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Destinasi</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-teal-100 to-cyan-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg shadow-teal-500/30 group-hover:scale-110 transition-transform">
                        <Eye class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(stats?.total_views || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Views</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(bookingStats?.total_bookings || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Booking</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Users class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(bookingStats?.total_visitors || 0) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Pengunjung</p>
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
                        <p class="text-[10px] text-gray-500 font-medium">Unggulan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-3 gap-3">
            <div class="status-card group relative overflow-hidden bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-4 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <p class="text-2xl font-black">{{ stats?.published || 0 }}</p>
                <p class="text-xs font-medium opacity-80">Aktif</p>
            </div>
            <div class="status-card group relative overflow-hidden bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl p-4 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <p class="text-2xl font-black">{{ stats?.draft || 0 }}</p>
                <p class="text-xs font-medium opacity-80">Nonaktif</p>
            </div>
            <div class="status-card group relative overflow-hidden bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl p-4 text-white shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <p class="text-lg font-black">{{ formatCurrency(bookingStats?.revenue || 0) }}</p>
                <p class="text-xs font-medium opacity-80">Pendapatan</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid lg:grid-cols-2 gap-5">
            <!-- Views Chart -->
            <div class="chart-section rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 shadow-md">
                        <TrendingUp class="w-4 h-4 text-white" />
                    </div>
                    Views per Hari
                </h3>
                <div v-if="viewsData.length > 0" class="h-48 flex items-end gap-1 px-2">
                    <div v-for="(val, i) in viewsData" :key="i" 
                        class="flex-1 bg-gradient-to-t from-emerald-500 to-green-400 rounded-t-md hover:from-emerald-600 hover:to-green-500 transition-all cursor-pointer relative group min-w-[8px]"
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

            <!-- Top Destinations -->
            <div class="chart-section rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-teal-500 to-cyan-600 shadow-md">
                        <Star class="w-4 h-4 text-white" />
                    </div>
                    Top 10 Destinasi Populer
                </h3>
                <div class="space-y-2.5 max-h-48 overflow-y-auto">
                    <div v-for="(dest, i) in topDestinations" :key="dest.id" class="flex items-center gap-3 group p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="w-6 h-6 bg-gradient-to-br from-emerald-500 to-green-600 text-white rounded-full flex items-center justify-center text-[10px] font-bold shadow-sm">{{ i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <Link :href="`/admin/destinations/${dest.id}/edit`" class="text-xs font-medium text-gray-900 group-hover:text-emerald-600 truncate block transition-colors">
                                {{ dest.name }}
                            </Link>
                            <p class="text-[10px] text-gray-400">{{ formatNumber(dest.views_count || 0) }} views • {{ dest.city }}</p>
                        </div>
                    </div>
                    <p v-if="!topDestinations?.length" class="text-gray-400 text-xs text-center py-4">Belum ada data</p>
                </div>
            </div>
        </div>

        <!-- Recent Destinations -->
        <div class="bottom-section rounded-xl bg-white p-5 shadow-lg border border-gray-100">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 shadow-md">
                    <MapPin class="w-4 h-4 text-white" />
                </div>
                Destinasi Terbaru
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div v-for="dest in recentDestinations" :key="dest.id" class="group rounded-xl overflow-hidden border border-gray-100 hover:shadow-lg transition-all">
                    <div class="aspect-video bg-gray-100 relative">
                        <img v-if="dest.cover_url" :src="dest.cover_url" class="w-full h-full object-cover" />
                        <div v-if="dest.is_featured" class="absolute top-2 right-2">
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-500 text-white">Unggulan</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <Link :href="`/admin/destinations/${dest.id}/edit`" class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 truncate block">{{ dest.name }}</Link>
                        <p class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1"><MapPin class="w-2.5 h-2.5" /> {{ dest.city }}</p>
                    </div>
                </div>
                <p v-if="!recentDestinations?.length" class="col-span-4 text-gray-400 text-xs text-center py-8">Belum ada destinasi</p>
            </div>
        </div>
    </div>
</template>
