<script setup>
import { Link } from '@inertiajs/vue3';
import { ShoppingCart, MapPin, ChevronRight, Eye, CheckCircle, Clock, XCircle, AlertCircle } from 'lucide-vue-next';

defineProps({
    bookings: { type: Array, default: () => [] }
});

const getStatusClass = (status) => {
    const classes = {
        confirmed: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
        pending: 'bg-amber-100 text-amber-700 ring-amber-600/20',
        cancelled: 'bg-red-100 text-red-700 ring-red-600/20',
        completed: 'bg-blue-100 text-blue-700 ring-blue-600/20',
        paid: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    };
    return classes[status] || 'bg-gray-100 text-gray-700 ring-gray-600/20';
};

const getStatusLabel = (status) => {
    const labels = { confirmed: 'Dikonfirmasi', pending: 'Menunggu', cancelled: 'Dibatalkan', completed: 'Selesai', paid: 'Dibayar' };
    return labels[status] || status;
};

const getStatusIcon = (status) => {
    const icons = { confirmed: CheckCircle, pending: Clock, cancelled: XCircle, completed: CheckCircle, paid: CheckCircle };
    return icons[status] || AlertCircle;
};
</script>

<template>
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30">
                        <ShoppingCart class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Booking Terbaru</h2>
                        <p class="text-[11px] text-gray-500">5 transaksi terakhir</p>
                    </div>
                </div>
                <Link href="/admin/bookings" class="group flex items-center gap-2 px-4 py-2 rounded-xl text-violet-600 hover:bg-violet-50 font-medium text-sm transition-all">
                    Lihat Semua
                    <ChevronRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </Link>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Destinasi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="booking in bookings" :key="booking.id" class="group hover:bg-gradient-to-r hover:from-violet-50/50 hover:to-purple-50/50 transition-all">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-white text-sm font-bold shadow-lg">
                                    {{ booking.user?.name?.charAt(0) || '?' }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors">{{ booking.user?.name || 'Unknown' }}</p>
                                    <p class="text-[11px] text-gray-500">{{ booking.user?.email || '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <MapPin class="w-4 h-4 text-gray-400" />
                                <p class="text-gray-700">{{ booking.destination?.name || 'N/A' }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold ring-1', getStatusClass(booking.status)]">
                                <component :is="getStatusIcon(booking.status)" class="w-3.5 h-3.5" />
                                {{ getStatusLabel(booking.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <Link :href="`/admin/bookings/${booking.id}`" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-violet-600 hover:bg-violet-100 text-sm font-medium transition-colors">
                                <Eye class="w-4 h-4" /> Detail
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!bookings?.length">
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <ShoppingCart class="w-8 h-8 text-gray-400" />
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada booking</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
