<script setup>
import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { 
    ShoppingCart, ChevronRight, Eye, MapPin, Clock, CheckCircle, 
    XCircle, AlertCircle, Wallet, Calendar, User
} from 'lucide-vue-next';

const props = defineProps({
    bookings: Array,
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const rows = document.querySelectorAll('.booking-row');
        if (rows.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(rows, 
                    { opacity: 0, x: -20 }, 
                    { opacity: 1, x: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const getStatusClass = (status) => {
    const classes = {
        confirmed: 'from-emerald-500 to-teal-600 text-white',
        pending: 'from-amber-500 to-orange-600 text-white',
        cancelled: 'from-red-500 to-rose-600 text-white',
        completed: 'from-blue-500 to-indigo-600 text-white',
        paid: 'from-emerald-500 to-teal-600 text-white',
    };
    return classes[status] || 'from-gray-500 to-gray-600 text-white';
};

const getStatusLabel = (status) => {
    const labels = {
        confirmed: 'Dikonfirmasi',
        pending: 'Menunggu',
        cancelled: 'Dibatalkan',
        completed: 'Selesai',
        paid: 'Dibayar',
    };
    return labels[status] || status;
};

const getStatusIcon = (status) => {
    const icons = {
        confirmed: CheckCircle,
        pending: Clock,
        cancelled: XCircle,
        completed: CheckCircle,
        paid: CheckCircle,
    };
    return icons[status] || AlertCircle;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <ShoppingCart class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Booking Terbaru</h3>
                        <p class="text-[10px] text-gray-500">5 transaksi terakhir</p>
                    </div>
                </div>
                <Link 
                    href="/admin/bookings" 
                    class="group flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-violet-600 hover:bg-violet-50 text-xs font-semibold transition-all"
                >
                    Lihat Semua
                    <ChevronRight class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Destinasi</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr 
                        v-for="booking in bookings" 
                        :key="booking.id" 
                        class="booking-row group hover:bg-gradient-to-r hover:from-violet-50/50 hover:to-purple-50/30 transition-all duration-300"
                    >
                        <!-- User -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold shadow-md group-hover:scale-105 transition-transform">
                                    {{ booking.user?.name?.charAt(0) || '?' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate group-hover:text-violet-600 transition-colors">{{ booking.user?.name || 'Unknown' }}</p>
                                    <p class="text-[10px] text-gray-500 truncate">{{ booking.user?.email || '' }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Destination -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <MapPin class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                                <p class="text-xs text-gray-700 truncate max-w-[120px]">{{ booking.destination?.name || 'N/A' }}</p>
                            </div>
                        </td>
                        
                        <!-- Date -->
                        <td class="px-4 py-3 hidden md:table-cell">
                            <div class="flex items-center gap-1.5">
                                <Calendar class="w-3.5 h-3.5 text-gray-400" />
                                <span class="text-[11px] text-gray-600">{{ formatDate(booking.visit_date) }}</span>
                            </div>
                        </td>
                        
                        <!-- Status -->
                        <td class="px-4 py-3">
                            <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold bg-gradient-to-r shadow-sm', getStatusClass(booking.status)]">
                                <component :is="getStatusIcon(booking.status)" class="w-3 h-3" />
                                {{ getStatusLabel(booking.status) }}
                            </span>
                        </td>
                        
                        <!-- Action -->
                        <td class="px-4 py-3 text-right">
                            <Link 
                                :href="`/admin/bookings/${booking.id}`" 
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-violet-600 hover:bg-violet-100 text-[11px] font-semibold transition-all"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span class="hidden sm:inline">Detail</span>
                            </Link>
                        </td>
                    </tr>
                    
                    <!-- Empty State -->
                    <tr v-if="!bookings?.length">
                        <td colspan="5" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <ShoppingCart class="w-7 h-7 text-gray-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-600">Belum ada booking</p>
                                    <p class="text-[11px] text-gray-400">Booking akan muncul di sini</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
