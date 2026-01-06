<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Ticket, Search, QrCode, Calendar, Check, Clock, XCircle, Eye, ChevronLeft, ChevronRight, RotateCcw } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    tickets: Object,
    todayStats: { type: Object, default: () => ({ total: 0, validated: 0, pending: 0 }) },
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const date = ref(props.filters?.date || '');

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/tickets', {
        search: search.value || undefined,
        status: status.value || undefined,
        date: date.value || undefined
    }, { preserveState: true });
};

const resetFilters = () => { search.value = ''; status.value = ''; date.value = ''; applyFilters(); };

const manualValidate = async (ticket) => {
    if (!confirm('Validasi tiket ini secara manual?')) return;
    await fetch(`/admin/tickets/${ticket.id}/manual-validate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    router.reload();
};

const getStatusClasses = (s) => {
    const classes = {
        valid: 'bg-green-100 text-green-700',
        used: 'bg-blue-100 text-blue-700',
        expired: 'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-100 text-red-700'
    };
    return classes[s] || 'bg-gray-100 text-gray-600';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <Ticket class="w-7 h-7 text-teal-600" />Manajemen Tiket
                </h1>
                <p class="text-gray-500 text-sm mt-1">Kelola dan validasi tiket pengunjung</p>
            </div>
            <Link href="/admin/tickets/validate"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 transition-all">
                <QrCode class="w-5 h-5" />Scan QR Code
            </Link>
        </div>

        <!-- Today Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-blue-100"><Ticket class="w-6 h-6 text-blue-600" /></div>
                    <div>
                        <p class="text-[11px] text-gray-500">Tiket Hari Ini</p>
                        <p class="text-lg font-bold text-gray-900">{{ todayStats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-green-100"><Check class="w-6 h-6 text-green-600" /></div>
                    <div>
                        <p class="text-[11px] text-gray-500">Sudah Divalidasi</p>
                        <p class="text-2xl font-bold text-green-600">{{ todayStats.validated }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-yellow-100"><Clock class="w-6 h-6 text-yellow-600" /></div>
                    <div>
                        <p class="text-[11px] text-gray-500">Belum Digunakan</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ todayStats.pending }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari kode tiket..."
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                </div>
                <select v-model="status" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                    <option value="">Semua Status</option>
                    <option value="valid">Valid</option>
                    <option value="used">Sudah Digunakan</option>
                    <option value="expired">Expired</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
                <input v-model="date" type="date" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                <button v-if="search || status || date" @click="resetFilters"
                    class="px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2">
                    <RotateCcw class="w-4 h-4" />Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode Tiket</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Booking</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal Valid</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Divalidasi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-teal-50/30 transition-colors group">
                            <td class="px-4 py-3">
                                <span class="font-mono font-bold text-teal-600">{{ ticket.ticket_code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ ticket.destination_name || '-' }}</p>
                                <p class="text-xs text-gray-500">{{ ticket.order_number }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(ticket.valid_date) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-bold', getStatusClasses(ticket.status)]">
                                    {{ ticket.status?.charAt(0).toUpperCase() + ticket.status?.slice(1) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <template v-if="ticket.used_at">
                                    <p>{{ formatDateTime(ticket.used_at) }}</p>
                                    <p class="text-xs">oleh {{ ticket.validator_name || '-' }}</p>
                                </template>
                                <span v-else>-</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button v-if="ticket.status === 'valid'" @click="manualValidate(ticket)"
                                        class="px-3 py-1.5 bg-green-100 text-green-700 font-bold text-xs rounded-lg hover:bg-green-200 transition-colors flex items-center gap-1">
                                        <Check class="w-3 h-3" />Validasi
                                    </button>
                                    <Link :href="`/admin/tickets/${ticket.id}`" class="p-2 rounded-lg text-gray-400 hover:text-teal-600 hover:bg-teal-50 transition-colors">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!tickets.data?.length">
                            <td colspan="6" class="px-4 py-16 text-center">
                                <Ticket class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                <p class="font-medium text-gray-900">Tidak ada tiket ditemukan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="tickets.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                <p class="text-[11px] text-gray-500">Halaman {{ tickets.current_page }} dari {{ tickets.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100">
                        <ChevronLeft class="w-5 h-5" />
                    </Link>
                    <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100">
                        <ChevronRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
