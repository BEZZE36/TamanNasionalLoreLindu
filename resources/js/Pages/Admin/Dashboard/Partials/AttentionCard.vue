<script setup>
import { Link } from '@inertiajs/vue3';
import { AlertCircle, Clock, MessageSquare, CheckCircle } from 'lucide-vue-next';

defineProps({
    attention: Object
});
</script>

<template>
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30">
                    <AlertCircle class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-lg font-bold text-gray-900">Perlu Perhatian</h2>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <Link v-if="attention?.pending_bookings > 0" href="/admin/bookings?status=pending" class="group flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-amber-100/50 border border-amber-200/50 hover:shadow-lg transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg group-hover:scale-110 transition-transform">
                        <Clock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <span class="font-semibold text-amber-900">Booking Pending</span>
                        <p class="text-sm text-amber-600">Butuh konfirmasi</p>
                    </div>
                </div>
                <span class="px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-sm font-bold rounded-xl shadow-lg">
                    {{ attention.pending_bookings }}
                </span>
            </Link>

            <Link v-if="attention?.unread_feedback > 0" href="/admin/feedback" class="group flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-100/50 border border-blue-200/50 hover:shadow-lg transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg group-hover:scale-110 transition-transform">
                        <MessageSquare class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <span class="font-semibold text-blue-900">Feedback Baru</span>
                        <p class="text-sm text-blue-600">Belum dibaca</p>
                    </div>
                </div>
                <span class="px-3 py-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-sm font-bold rounded-xl shadow-lg">
                    {{ attention.unread_feedback }}
                </span>
            </Link>

            <div v-if="!attention?.pending_bookings && !attention?.unread_feedback" class="text-center py-8">
                <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-100 flex items-center justify-center mb-4">
                    <CheckCircle class="w-8 h-8 text-emerald-600" />
                </div>
                <p class="font-semibold text-gray-700">Semua Beres!</p>
                <p class="text-[11px] text-gray-500">Tidak ada yang perlu ditangani</p>
            </div>
        </div>
    </div>
</template>
