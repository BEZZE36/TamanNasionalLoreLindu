<script setup>
/**
 * RecentBookings.vue - Premium Booking Cards
 * Modern card design with GSAP animations
 */
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Ticket, Calendar, CheckCircle, Clock, MapPin, ClipboardList, ArrowRight, Sparkles } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    bookings: {
        type: Array,
        default: () => []
    }
});

const sectionRef = ref(null);

onMounted(() => {
    gsap.context(() => {
        gsap.fromTo('.booking-card', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.1, ease: 'power2.out',
                scrollTrigger: { trigger: sectionRef.value, start: 'top 85%' }
            }
        );
    }, sectionRef.value);
});
</script>

<template>
    <div ref="sectionRef" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 sm:p-5 lg:p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25">
                        <Ticket class="w-4 h-4 sm:w-5 sm:h-5" />
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900">Pemesanan Terbaru</h2>
                        <p class="text-[10px] sm:text-xs text-gray-500">Tiket yang baru Anda pesan</p>
                    </div>
                </div>
                <Link v-if="bookings.length > 0" href="/my-bookings" 
                    class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                    <span>Lihat Semua</span>
                    <ArrowRight class="w-3.5 h-3.5" />
                </Link>
            </div>
        </div>

        <!-- Bookings List -->
        <div v-if="bookings.length > 0" class="p-3 sm:p-4 lg:p-5 space-y-2.5 sm:space-y-3">
            <div v-for="booking in bookings" :key="booking.id"
                class="booking-card flex items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-lg sm:rounded-xl bg-gradient-to-r from-gray-50 to-gray-100/50 border border-gray-100 hover:border-emerald-200 hover:shadow-md hover:from-emerald-50/30 hover:to-gray-50 transition-all duration-300 group">
                
                <!-- Destination Image -->
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg sm:rounded-xl overflow-hidden flex-shrink-0 shadow-md border border-white">
                    <img :src="booking.destination?.primary_image_url || '/assets/logo.png'" 
                         :alt="booking.destination?.name"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                </div>

                <!-- Booking Info -->
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm truncate group-hover:text-emerald-700 transition-colors">{{ booking.destination?.name }}</h4>
                    <div class="flex items-center gap-1.5 mt-1 text-[10px] sm:text-xs text-gray-500">
                        <Calendar class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        {{ booking.formatted_visit_date }}
                    </div>
                    <p class="text-[9px] sm:text-[10px] text-gray-400 mt-0.5 font-medium">{{ booking.order_number }}</p>
                </div>

                <!-- Status & Price -->
                <div class="text-right flex-shrink-0">
                    <span v-if="booking.status === 'paid'" 
                          class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[9px] sm:text-[10px] font-semibold shadow-sm">
                        <CheckCircle class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                        Lunas
                    </span>
                    <span v-else-if="booking.status === 'pending'"
                          class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-[9px] sm:text-[10px] font-semibold shadow-sm">
                        <Clock class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                        Menunggu
                    </span>
                    <span v-else
                          class="inline-flex items-center px-2 sm:px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-[9px] sm:text-[10px] font-semibold">
                        {{ booking.status_label }}
                    </span>
                    <p class="text-xs sm:text-sm font-bold text-gray-900 mt-1.5">{{ booking.formatted_total_amount }}</p>
                </div>
            </div>

            <!-- Mobile View All -->
            <Link href="/my-bookings" 
                class="sm:hidden flex items-center justify-center gap-2 w-full py-2.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                <span>Lihat Semua Tiket</span>
                <ArrowRight class="w-3.5 h-3.5" />
            </Link>
        </div>

        <!-- Empty State -->
        <div v-else class="p-6 sm:p-8 lg:p-10 text-center">
            <div class="w-14 h-14 sm:w-16 sm:h-16 mx-auto mb-4 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl sm:rounded-2xl flex items-center justify-center">
                <ClipboardList class="w-7 h-7 sm:w-8 sm:h-8 text-gray-400" />
            </div>
            <h3 class="text-sm sm:text-base font-bold text-gray-900 mb-1.5">Belum Ada Pemesanan</h3>
            <p class="text-gray-500 text-[10px] sm:text-xs mb-4 sm:mb-5 max-w-xs mx-auto">Mulai petualangan Anda dengan menjelajahi destinasi wisata di TNLL</p>
            <Link href="/destinations" 
                  class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-xs sm:text-sm rounded-lg sm:rounded-xl hover:from-emerald-600 hover:to-teal-700 hover:shadow-lg hover:shadow-emerald-500/25 hover:-translate-y-0.5 transition-all duration-300">
                <MapPin class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                <span>Jelajahi Destinasi</span>
                <Sparkles class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
            </Link>
        </div>
    </div>
</template>

<style scoped>
.booking-card {
    will-change: transform, opacity;
}
</style>
