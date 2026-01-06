<script setup>
import { Link } from '@inertiajs/vue3';
import { Clock, CreditCard, Banknote, CheckCircle, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
    transactions: Array,
    adminName: String
});

const formatTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const formatCurrency = (amount) => {
    if (!amount) return '-';
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};
</script>

<template>
    <div class="mt-8 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                <Clock class="w-5 h-5 text-indigo-600" />
                Riwayat Pembayaran & Validasi Hari Ini
            </h3>
            <Link href="/admin/bookings" 
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                Lihat Semua Booking <ExternalLink class="w-4 h-4" />
            </Link>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="transaction in transactions" :key="transaction.id" 
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ formatTime(transaction.created_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-medium text-gray-600">
                                {{ transaction.order_number || '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">{{ transaction.leader_name }}</span>
                                <span class="text-xs text-gray-400">{{ transaction.destination?.name || '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="transaction.payment?.payment_type === 'cash'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                <Banknote class="w-3 h-3" />
                                Tunai
                            </span>
                            <span v-else-if="transaction.status === 'used'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                                <CheckCircle class="w-3 h-3" />
                                Validasi Masuk
                            </span>
                            <span v-else class="text-gray-500">-</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">
                            {{ formatCurrency(transaction.total_amount) }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="[
                                'inline-flex px-2 py-1 text-xs font-bold leading-5 rounded-full',
                                transaction.status_color === 'green' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                            ]">
                                {{ transaction.status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ adminName || 'Admin' }}
                        </td>
                    </tr>
                    <tr v-if="!transactions?.length">
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            Belum ada transaksi hari ini
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
