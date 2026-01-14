<script setup>
/**
 * Dashboard.vue - Premium User Dashboard
 * Modern design with responsive layout and GSAP animations
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import HeroBanner from './Partials/HeroBanner.vue';
import QuickActions from './Partials/QuickActions.vue';
import RecentBookings from './Partials/RecentBookings.vue';
import NewsArticles from './Partials/NewsArticles.vue';
import Sidebar from './Partials/Sidebar.vue';

gsap.registerPlugin(ScrollTrigger);

defineOptions({ layout: PublicLayout });

defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalBookings: 0,
            activeTickets: 0,
            pendingPayments: 0
        })
    },
    recentBookings: {
        type: Array,
        default: () => []
    },
    recentNews: {
        type: Array,
        default: () => []
    },
    recentArticles: {
        type: Array,
        default: () => []
    }
});

const mainRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        // Main content entrance
        gsap.fromTo('.content-section', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.15, ease: 'power2.out', delay: 0.5 }
        );
    }, mainRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <Head title="Dashboard" />

    <div>
        <!-- Hero Banner -->
        <HeroBanner />

        <!-- Main Content -->
        <section id="dashboard-content" ref="mainRef" class="relative py-8 sm:py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-20 -right-32 w-48 h-48 sm:w-64 sm:h-64 bg-emerald-100/40 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 -left-32 w-40 h-40 sm:w-56 sm:h-56 bg-teal-100/30 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-emerald-50/30 to-cyan-50/30 rounded-full blur-3xl"></div>
            </div>

            <div class="container mx-auto px-4 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Main Column -->
                    <div class="lg:col-span-2 space-y-5 sm:space-y-6">
                        <!-- Quick Actions -->
                        <div class="content-section">
                            <QuickActions />
                        </div>

                        <!-- Recent Bookings -->
                        <div class="content-section">
                            <RecentBookings :bookings="recentBookings" />
                        </div>

                        <!-- News & Articles -->
                        <div class="content-section">
                            <NewsArticles :news="recentNews" :articles="recentArticles" />
                        </div>
                    </div>

                    <!-- Sidebar - Hidden on mobile, shown on desktop -->
                    <div class="hidden lg:block content-section">
                        <Sidebar />
                    </div>
                </div>

                <!-- Mobile Sidebar - Shown below main content on mobile -->
                <div class="lg:hidden mt-6 content-section">
                    <Sidebar />
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.content-section {
    will-change: transform, opacity;
}
</style>
