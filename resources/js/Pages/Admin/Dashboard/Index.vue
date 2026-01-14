<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import WelcomeHeader from './Partials/WelcomeHeader.vue';
import PrimaryStats from './Partials/PrimaryStats.vue';
import ContentStats from './Partials/ContentStats.vue';
import RecentBookings from './Partials/RecentBookings.vue';
import AttentionCard from './Partials/AttentionCard.vue';
import TopDestinations from './Partials/TopDestinations.vue';
import GoalsCard from './Partials/GoalsCard.vue';
import RecentActivities from './Partials/RecentActivities.vue';
import SystemStatus from './Partials/SystemStatus.vue';
import QuickActions from './Partials/QuickActions.vue';
import RevenueChart from './Partials/RevenueChart.vue';
import BookingCalendar from './Partials/BookingCalendar.vue';
import { 
    Wallet, CalendarCheck, Clock, Users, MapPin, Flower2, Bird, Image,
    Calendar, Filter, RefreshCw
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object, labels: Array, revenueData: Array, visitorData: Array,
    bookingStatus: Object, topDestinations: Array, recentBookings: Array,
    recentActivities: Array, goals: Object, systemStatus: Object,
    attention: Object, calendarBookings: Object, periodLabel: String,
    startDate: String, endDate: String,
});

// Date filter state
const showDateFilter = ref(false);
const localStartDate = ref(props.startDate);
const localEndDate = ref(props.endDate);
const isRefreshing = ref(false);

// GSAP context
let ctx;

// Animation on mount
onMounted(() => {
    nextTick(() => {
        ctx = gsap.context(() => {
            // Animate main sections
            const sections = document.querySelectorAll('.dashboard-section');
            if (sections.length > 0) {
                gsap.fromTo(sections, 
                    { opacity: 0, y: 30, scale: 0.98 }, 
                    { opacity: 1, y: 0, scale: 1, duration: 0.6, stagger: 0.1, ease: 'power3.out' }
                );
            }
        });
    });
});


onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Format helpers
const formatCurrency = (amount) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
}).format(amount || 0);

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

// Primary stats config
const primaryStats = computed(() => [
    { 
        title: `Pendapatan (${props.periodLabel || '7 Hari'})`, 
        value: formatCurrency(props.stats?.periodRevenue), 
        subLabel: 'Total', 
        subValue: formatCurrency(props.stats?.totalRevenue), 
        icon: Wallet, 
        gradient: 'from-emerald-500 to-teal-600', 
        bgGradient: 'from-emerald-500/10 to-transparent',
        shadowColor: 'shadow-emerald-500/25'
    },
    { 
        title: `Pemesanan (${props.periodLabel || '7 Hari'})`, 
        value: formatNumber(props.stats?.periodBookings), 
        subLabel: 'Total', 
        subValue: formatNumber(props.stats?.totalBookings), 
        icon: CalendarCheck, 
        gradient: 'from-blue-500 to-indigo-600', 
        bgGradient: 'from-blue-500/10 to-transparent',
        shadowColor: 'shadow-blue-500/25'
    },
    { 
        title: 'Menunggu Pembayaran', 
        value: formatNumber(props.stats?.pendingBookings), 
        linkHref: '/admin/bookings?status=pending', 
        linkText: 'Lihat semua', 
        icon: Clock, 
        gradient: 'from-amber-500 to-orange-600', 
        bgGradient: 'from-amber-500/10 to-transparent',
        shadowColor: 'shadow-amber-500/25',
        pulse: props.stats?.pendingBookings > 0
    },
    { 
        title: 'Total Pengguna', 
        value: formatNumber(props.stats?.totalUsers), 
        subLabel: 'Pengguna terdaftar', 
        icon: Users, 
        gradient: 'from-violet-500 to-purple-600', 
        bgGradient: 'from-violet-500/10 to-transparent',
        shadowColor: 'shadow-violet-500/25'
    },
]);

// Content stats config
const contentStats = computed(() => [
    { 
        title: 'Destinasi', 
        value: props.stats?.totalDestinations || 0, 
        icon: MapPin, 
        href: '/admin/destinations/dashboard',
        bgColor: 'from-emerald-50 to-teal-50', 
        iconBg: 'from-emerald-500 to-teal-600', 
        textColor: 'text-emerald-700', 
        iconColor: 'text-white', 
        borderColor: 'border-emerald-200/60',
        hoverBg: 'hover:from-emerald-100 hover:to-teal-100'
    },
    { 
        title: 'Flora', 
        value: props.stats?.totalFlora || 0, 
        icon: Flower2,
        href: '/admin/flora/dashboard', 
        bgColor: 'from-lime-50 to-green-50', 
        iconBg: 'from-lime-500 to-green-600', 
        textColor: 'text-lime-700', 
        iconColor: 'text-white', 
        borderColor: 'border-lime-200/60',
        hoverBg: 'hover:from-lime-100 hover:to-green-100'
    },
    { 
        title: 'Fauna', 
        value: props.stats?.totalFauna || 0, 
        icon: Bird, 
        href: '/admin/fauna/dashboard',
        bgColor: 'from-amber-50 to-orange-50', 
        iconBg: 'from-amber-500 to-orange-600', 
        textColor: 'text-amber-700', 
        iconColor: 'text-white', 
        borderColor: 'border-amber-200/60',
        hoverBg: 'hover:from-amber-100 hover:to-orange-100'
    },
    { 
        title: 'Gallery', 
        value: props.stats?.totalGallery || 0, 
        icon: Image,
        href: '/admin/gallery/dashboard', 
        bgColor: 'from-pink-50 to-rose-50', 
        iconBg: 'from-pink-500 to-rose-600', 
        textColor: 'text-pink-700', 
        iconColor: 'text-white', 
        borderColor: 'border-pink-200/60',
        hoverBg: 'hover:from-pink-100 hover:to-rose-100'
    },
]);

// Date filter handlers
const applyDateFilter = () => {
    isRefreshing.value = true;
    router.get('/admin/dashboard', {
        start_date: localStartDate.value,
        end_date: localEndDate.value,
    }, {
        preserveState: true,
        onFinish: () => {
            isRefreshing.value = false;
            showDateFilter.value = false;
        }
    });
};

const resetDateFilter = () => {
    isRefreshing.value = true;
    router.get('/admin/dashboard', {}, {
        preserveState: true,
        onFinish: () => {
            isRefreshing.value = false;
            showDateFilter.value = false;
        }
    });
};

const refreshDashboard = () => {
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-4">
            <!-- Welcome Header with Date Filter -->
            <div class="dashboard-section">
                <WelcomeHeader 
                    :period-label="periodLabel" 
                    :today-bookings="stats?.todayBookings" 
                    :pending-bookings="stats?.pendingBookings"
                    :start-date="startDate"
                    :end-date="endDate"
                    @refresh="refreshDashboard"
                    @filter="showDateFilter = !showDateFilter"
                    :is-refreshing="isRefreshing"
                />
            </div>

            <!-- Date Filter Dropdown -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-4 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 -translate-y-4 scale-95"
            >
                <div v-if="showDateFilter" class="dashboard-section rounded-xl bg-white p-4 shadow-lg border border-gray-100">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <div class="flex items-center gap-2">
                            <Calendar class="w-4 h-4 text-violet-500" />
                            <span class="text-xs font-semibold text-gray-700">Filter Periode:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <input 
                                type="date" 
                                v-model="localStartDate"
                                class="px-3 py-2 text-xs rounded-lg border-2 border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
                            />
                            <span class="text-xs text-gray-400">s/d</span>
                            <input 
                                type="date" 
                                v-model="localEndDate"
                                class="px-3 py-2 text-xs rounded-lg border-2 border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
                            />
                            <button 
                                @click="applyDateFilter"
                                :disabled="isRefreshing"
                                class="px-4 py-2 text-xs font-semibold rounded-lg bg-gradient-to-r from-violet-500 to-purple-600 text-white hover:shadow-lg hover:-translate-y-0.5 transition-all disabled:opacity-50"
                            >
                                <Filter class="w-3.5 h-3.5 inline mr-1" />
                                Terapkan
                            </button>
                            <button 
                                @click="resetDateFilter"
                                :disabled="isRefreshing"
                                class="px-3 py-2 text-xs font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all disabled:opacity-50"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Quick Actions -->
            <div class="dashboard-section">
                <QuickActions />
            </div>

            <!-- Primary Stats -->
            <div class="dashboard-section">
                <PrimaryStats :stats="primaryStats" />
            </div>

            <!-- Content Stats -->
            <div class="dashboard-section">
                <ContentStats :stats="contentStats" />
            </div>

            <!-- Charts & Calendar Row -->
            <div class="dashboard-section grid grid-cols-1 xl:grid-cols-3 gap-4 items-start">
                <!-- Revenue/Visitor Chart -->
                <div class="xl:col-span-2">
                    <RevenueChart 
                        :labels="labels" 
                        :revenue-data="revenueData" 
                        :visitor-data="visitorData"
                        :period-label="periodLabel"
                    />
                </div>
                <!-- Mini Calendar -->
                <div>
                    <BookingCalendar :calendar-bookings="calendarBookings" />
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="dashboard-section grid grid-cols-1 xl:grid-cols-3 gap-4 items-start">
                <!-- Recent Bookings -->
                <div class="xl:col-span-2">
                    <RecentBookings :bookings="recentBookings" />
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <AttentionCard :attention="attention" />
                    <TopDestinations :destinations="topDestinations" />
                </div>
            </div>

            <!-- Goals & Activities Row -->
            <div class="dashboard-section grid grid-cols-1 lg:grid-cols-2 gap-4">
                <GoalsCard :goals="goals" />
                <RecentActivities :activities="recentActivities" />
            </div>

            <!-- System Status -->
            <div class="dashboard-section">
                <SystemStatus :system-status="systemStatus" />
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.dashboard-section {
    will-change: transform, opacity;
}
</style>
