<script setup>
/**
 * QuickActions.vue - Premium Quick Action Cards
 * Modern glassmorphism design with hover animations
 */
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Ticket, ClipboardList, ArrowRight, MapPin, Sparkles } from 'lucide-vue-next';

const sectionRef = ref(null);

onMounted(() => {
    gsap.fromTo('.action-card', 
        { opacity: 0, y: 30, scale: 0.95 }, 
        { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.15, ease: 'power2.out' }
    );
});
</script>

<template>
    <div ref="sectionRef" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <!-- Book New Ticket -->
        <Link href="/destinations"
            class="action-card group relative p-4 sm:p-5 lg:p-6 rounded-xl sm:rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 via-teal-400 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-24 h-24 sm:w-32 sm:h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="absolute bottom-0 left-0 w-16 h-16 sm:w-20 sm:h-20 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            
            <!-- Content -->
            <div class="relative z-10">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 group-hover:bg-white/30 transition-all duration-300">
                    <Ticket class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                <h3 class="text-sm sm:text-base lg:text-lg font-bold text-white mb-1">Pesan Tiket Baru</h3>
                <p class="text-white/80 text-[10px] sm:text-xs mb-3 sm:mb-4 leading-relaxed">Jelajahi destinasi wisata menakjubkan di TNLL</p>
                <div class="inline-flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs font-semibold text-white">
                    <MapPin class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    <span>Lihat Destinasi</span>
                    <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5 group-hover:translate-x-1 transition-transform" />
                </div>
            </div>
        </Link>

        <!-- View Bookings -->
        <Link href="/my-bookings"
            class="action-card group relative p-4 sm:p-5 lg:p-6 rounded-xl sm:rounded-2xl bg-white border border-gray-100 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-emerald-200">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-24 h-24 sm:w-32 sm:h-32 bg-gradient-to-br from-cyan-50 to-teal-50 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="absolute bottom-0 left-0 w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-emerald-50 to-transparent rounded-full translate-y-1/2 -translate-x-1/2"></div>
            
            <!-- Content -->
            <div class="relative z-10">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-gradient-to-br from-cyan-100 to-teal-100 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 group-hover:from-cyan-200 group-hover:to-teal-200 transition-all duration-300">
                    <ClipboardList class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-600" />
                </div>
                <h3 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900 mb-1 group-hover:text-emerald-700 transition-colors">Lihat Pemesanan</h3>
                <p class="text-gray-500 text-[10px] sm:text-xs mb-3 sm:mb-4 leading-relaxed">Kelola tiket dan riwayat perjalanan Anda</p>
                <div class="inline-flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs font-semibold text-emerald-600">
                    <Sparkles class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    <span>Buka Tiket Saya</span>
                    <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5 group-hover:translate-x-1 transition-transform" />
                </div>
            </div>
        </Link>
    </div>
</template>

<style scoped>
.action-card {
    will-change: transform, opacity;
}
</style>
