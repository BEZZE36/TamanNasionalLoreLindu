<script setup>
/**
 * ScanTransactions.vue - Premium Transactions Table Component
 * Matching Newsletter design system
 */
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Clock, CreditCard, Banknote, CheckCircle, ExternalLink, Ticket, Play } from 'lucide-vue-next';

const props = defineProps({
    transactions: Array,
    adminName: String
});

const emit = defineEmits(['validateTicket']);

const validating = ref(null);

let ctx;

const formatTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const formatCurrency = (amount) => {
    if (!amount) return '-';
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
};

// Normalize legacy statuses
const normalizeStatus = (status) => {
    const mapping = {
        'pending': 'pending', 'awaiting_cash': 'pending',
        'paid': 'confirmed', 'confirmed': 'confirmed',
        'used': 'used',
        'cancelled': 'cancelled', 'expired': 'cancelled', 'refunded': 'cancelled'
    };
    return mapping[status] || status;
};

const getStatusClass = (status) => {
    const normalized = normalizeStatus(status);
    const classes = {
        'confirmed': 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white',
        'used': 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white',
        'pending': 'bg-gradient-to-r from-amber-500 to-orange-500 text-white',
        'cancelled': 'bg-gray-100 text-gray-600'
    };
    return classes[normalized] || 'bg-gray-100 text-gray-600';
};

const canValidate = (transaction) => {
    const normalized = normalizeStatus(transaction.status);
    return normalized === 'confirmed' && transaction.tickets?.some(t => t.status === 'valid');
};

const handleValidate = async (transaction) => {
    const ticket = transaction.tickets?.find(t => t.status === 'valid');
    if (!ticket) return;
    
    validating.value = transaction.id;
    try {
        const response = await fetch('/admin/tickets/validate-entry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ticket_id: ticket.id })
        });
        const data = await response.json();
        if (data.success) {
            window.showToast?.('Tiket berhasil divalidasi!', 'success');
            window.location.reload();
        } else {
            window.showToast?.(data.message || 'Gagal memvalidasi', 'error');
        }
    } catch (e) {
        window.showToast?.('Gagal menghubungi server', 'error');
    }
    validating.value = null;
};

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.table-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="content-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-gradient-to-r from-gray-50 to-gray-100">
            <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <Clock class="w-4 h-4 text-white" />
                </div>
                Riwayat Pembayaran & Validasi Hari Ini
            </h3>
            <Link href="/admin/bookings" 
                class="inline-flex items-center gap-1.5 text-[10px] text-indigo-600 hover:text-indigo-700 font-semibold hover:underline transition-colors">
                Lihat Semua Booking <ExternalLink class="w-3 h-3" />
            </Link>
        </div>
        
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Order ID</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="transaction in transactions" :key="transaction.id" 
                        class="table-row group hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/30 transition-all duration-200">
                        <td class="px-4 py-3 text-[10px] text-gray-500">
                            {{ formatTime(transaction.created_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-[10px] font-medium text-gray-600">
                                {{ transaction.order_number || '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-xs font-medium text-gray-900">{{ transaction.leader_name }}</span>
                                <span class="text-[10px] text-gray-400">{{ transaction.destination?.name || '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="transaction.payment?.payment_type === 'cash'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-bold bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-sm">
                                <Banknote class="w-3 h-3" />
                                Tunai
                            </span>
                            <span v-else-if="transaction.status === 'used'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-bold bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-sm">
                                <CheckCircle class="w-3 h-3" />
                                Validasi
                            </span>
                            <span v-else class="text-gray-500 text-[10px]">-</span>
                        </td>
                        <td class="px-4 py-3 text-xs font-bold text-gray-900">
                            {{ formatCurrency(transaction.total_amount) }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="[
                                'inline-flex px-2 py-1 text-[9px] font-bold rounded-full',
                                getStatusClass(transaction.status)
                            ]">
                                {{ transaction.status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button v-if="canValidate(transaction)"
                                @click="handleValidate(transaction)"
                                :disabled="validating === transaction.id"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold bg-gradient-to-r from-emerald-500 to-teal-500 text-white hover:from-emerald-600 hover:to-teal-600 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <Play v-if="validating !== transaction.id" class="w-3 h-3" />
                                <span v-else class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                Validasi
                            </button>
                            <span v-else class="text-[10px] text-gray-400">-</span>
                        </td>
                    </tr>
                    <tr v-if="!transactions?.length">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                                    <Ticket class="w-7 h-7 text-gray-400" />
                                </div>
                                <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada transaksi hari ini</p>
                                <p class="text-[10px] text-gray-400">Transaksi akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-100">
            <div v-for="transaction in transactions" :key="transaction.id"
                 class="table-row p-4 hover:bg-gray-50/80 transition-colors">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <p class="font-medium text-gray-900 text-xs">{{ transaction.leader_name }}</p>
                        <p class="font-mono text-[10px] text-gray-500 mt-0.5">{{ transaction.order_number || '-' }}</p>
                    </div>
                    <span v-if="transaction.payment?.payment_type === 'cash'"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700">
                        <Banknote class="w-2.5 h-2.5" /> Tunai
                    </span>
                    <span v-else-if="transaction.status === 'used'"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-100 text-indigo-700">
                        <CheckCircle class="w-2.5 h-2.5" /> Validasi
                    </span>
                </div>
                <div class="flex items-center justify-between gap-2 text-[10px]">
                    <div class="flex items-center gap-2 text-gray-500">
                        <span>{{ formatTime(transaction.created_at) }}</span>
                        <span>•</span>
                        <span class="font-bold text-gray-900">{{ formatCurrency(transaction.total_amount) }}</span>
                    </div>
                    <span :class="[
                        'px-2 py-0.5 rounded-full text-[9px] font-bold',
                        transaction.status_color === 'green' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'
                    ]">
                        {{ transaction.status_label }}
                    </span>
                </div>
            </div>
            
            <!-- Mobile Empty State -->
            <div v-if="!transactions?.length" class="p-8 text-center">
                <Ticket class="w-10 h-10 mx-auto mb-3 text-gray-300" />
                <p class="text-sm font-medium text-gray-600">Belum ada transaksi hari ini</p>
            </div>
        </div>
    </div>
</template>
