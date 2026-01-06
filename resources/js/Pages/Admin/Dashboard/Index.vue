<script setup>
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import WelcomeHeader from './Partials/WelcomeHeader.vue';
import PrimaryStats from './Partials/PrimaryStats.vue';
import ContentStats from './Partials/ContentStats.vue';
import RecentBookings from './Partials/RecentBookings.vue';
import AttentionCard from './Partials/AttentionCard.vue';
import TopDestinations from './Partials/TopDestinations.vue';
import GoalsCard from './Partials/GoalsCard.vue';
import RecentActivities from './Partials/RecentActivities.vue';
import SystemStatus from './Partials/SystemStatus.vue';
import { Wallet, CalendarCheck, Clock, Users, MapPin, Flower2, Bird, Image } from 'lucide-vue-next';

const props = defineProps({
    stats: Object, labels: Array, revenueData: Array, visitorData: Array,
    bookingStatus: Object, topDestinations: Array, recentBookings: Array,
    recentActivities: Array, goals: Object, systemStatus: Object,
    attention: Object, calendarBookings: Object, periodLabel: String,
    startDate: String, endDate: String,
});

// Format helpers
const formatCurrency = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

// Primary stats config
const primaryStats = computed(() => [
    { title: `Pendapatan (${props.periodLabel || '7 Hari'})`, value: formatCurrency(props.stats?.periodRevenue), subLabel: 'Total', subValue: formatCurrency(props.stats?.totalRevenue), icon: Wallet, gradient: 'from-emerald-400 to-green-600', bgGradient: 'from-emerald-500/10 to-transparent' },
    { title: `Pemesanan (${props.periodLabel || '7 Hari'})`, value: formatNumber(props.stats?.periodBookings), subLabel: 'Total', subValue: formatNumber(props.stats?.totalBookings), icon: CalendarCheck, gradient: 'from-blue-400 to-indigo-600', bgGradient: 'from-blue-500/10 to-transparent' },
    { title: 'Menunggu Pembayaran', value: formatNumber(props.stats?.pendingBookings), linkHref: '/admin/bookings?status=pending', linkText: 'Lihat semua', icon: Clock, gradient: 'from-amber-400 to-orange-600', bgGradient: 'from-amber-500/10 to-transparent' },
    { title: 'Total Pengguna', value: formatNumber(props.stats?.totalUsers), subLabel: 'Pengguna terdaftar', icon: Users, gradient: 'from-purple-400 to-violet-600', bgGradient: 'from-purple-500/10 to-transparent' },
]);

// Content stats config
const contentStats = computed(() => [
    { title: 'Destinasi', value: props.stats?.totalDestinations || 0, icon: MapPin, bgColor: 'from-emerald-50 to-green-50', iconBg: 'from-emerald-400/20 to-green-500/20', textColor: 'text-emerald-700', iconColor: 'text-emerald-600', borderColor: 'border-emerald-100/50' },
    { title: 'Flora', value: props.stats?.totalFlora || 0, icon: Flower2, bgColor: 'from-lime-50 to-green-50', iconBg: 'from-lime-400/20 to-green-500/20', textColor: 'text-lime-700', iconColor: 'text-lime-600', borderColor: 'border-lime-100/50' },
    { title: 'Fauna', value: props.stats?.totalFauna || 0, icon: Bird, bgColor: 'from-amber-50 to-yellow-50', iconBg: 'from-amber-400/20 to-orange-500/20', textColor: 'text-amber-700', iconColor: 'text-amber-600', borderColor: 'border-amber-100/50' },
    { title: 'Gallery', value: props.stats?.totalGallery || 0, icon: Image, bgColor: 'from-pink-50 to-rose-50', iconBg: 'from-pink-400/20 to-rose-500/20', textColor: 'text-pink-700', iconColor: 'text-pink-600', borderColor: 'border-pink-100/50' },
]);
</script>

<template>
    <AdminLayout>
        <div class="space-y-5">
            <!-- Welcome Header -->
            <WelcomeHeader :period-label="periodLabel" :today-bookings="stats?.todayBookings" :pending-bookings="stats?.pendingBookings" />

            <!-- Primary Stats -->
            <PrimaryStats :stats="primaryStats" />

            <!-- Content Stats -->
            <ContentStats :stats="contentStats" />

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-3">
                <!-- Recent Bookings -->
                <div class="xl:col-span-2">
                    <RecentBookings :bookings="recentBookings" />
                </div>

                <!-- Right Column -->
                <div class="space-y-5">
                    <AttentionCard :attention="attention" />
                    <TopDestinations :destinations="topDestinations" />
                    <GoalsCard :goals="goals" />
                </div>
            </div>

            <!-- Recent Activities -->
            <RecentActivities :activities="recentActivities" />

            <!-- System Status -->
            <SystemStatus :system-status="systemStatus" />
        </div>
    </AdminLayout>
</template>
