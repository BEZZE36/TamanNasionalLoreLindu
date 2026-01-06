<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CalendarCheck, Plus, Search, Eye, Edit, Trash2, Download, User, MapPin, ChevronLeft, ChevronRight, Ticket, X, Check } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    bookings: Object,
    destinations: Array,
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const destination = ref(props.filters?.destination_id || '');
const status = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/bookings', {
        search: search.value || undefined,
        destination_id: destination.value || undefined,
        status: status.value || undefined
    }, { preserveState: true });
};

const confirmDelete = (item) => { deleteTarget.value = item; showDeleteModal.value = true; };
const deleteBooking = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/bookings/${deleteTarget.value.id}`, {
            onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; }
        });
    }
};

const exportBookings = () => {
    window.location.href = `/admin/bookings/export?search=${search.value}&destination_id=${destination.value}&status=${status.value}`;
};

const getStatusClasses = (s) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-700',
        paid: 'bg-green-100 text-green-700',
        confirmed: 'bg-emerald-100 text-emerald-700',
        used: 'bg-gray-100 text-gray-700',
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
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <CalendarCheck class="w-7 h-7 text-blue-600" />Pemesanan
                </h1>
                <p class="text-gray-500 text-sm mt-1">Kelola semua transaksi tiket dan reservasi pengunjung</p>
            </div>
            <Link href="/admin/bookings/create"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all">
                <Plus class="w-4 h-4" />Buat Booking Baru
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Order ID, Nama, Email..."
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                </div>
                <select v-model="destination" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Destinasi</option>
                    <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
                <select v-model="status" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="used">Used</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button @click="exportBookings"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 bg-green-100 text-green-700 font-bold rounded-xl hover:bg-green-200 transition-colors">
                    <Download class="w-5 h-5" />Export
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order Info</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tamu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Destinasi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jadwal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        #{{ booking.order_number?.slice(-4) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm">{{ booking.order_number }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDateTime(booking.created_at) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                        <User class="w-4 h-4 text-gray-500" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ booking.leader_name }}</p>
                                        <p class="text-xs text-gray-500">{{ booking.leader_phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700">
                                    <MapPin class="w-3 h-3" />{{ booking.destination?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 text-sm">{{ formatDate(booking.visit_date) }}</p>
                                <p class="text-xs text-gray-500">{{ booking.total_visitors }} pax</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-bold', getStatusClasses(booking.status)]">
                                    {{ booking.status?.charAt(0).toUpperCase() + booking.status?.slice(1) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-gray-900 text-sm">{{ booking.formatted_total_amount }}</p>
                                <p class="text-xs text-gray-500">{{ booking.payment_type || 'Unpaid' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Link :href="`/admin/bookings/${booking.id}`" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="`/admin/bookings/${booking.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(booking)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!bookings.data?.length">
                            <td colspan="7" class="px-4 py-16 text-center">
                                <Ticket class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                <p class="font-medium text-gray-900">Belum ada pemesanan</p>
                                <p class="text-gray-500 text-sm mt-1">Booking yang masuk akan muncul di sini</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="bookings.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                <p class="text-[11px] text-gray-500">Halaman {{ bookings.current_page }} dari {{ bookings.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="bookings.prev_page_url" :href="bookings.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100">
                        <ChevronLeft class="w-5 h-5" />
                    </Link>
                    <Link v-if="bookings.next_page_url" :href="bookings.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100">
                        <ChevronRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 animate-in fade-in zoom-in duration-200">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Hapus Booking</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus booking <strong class="text-blue-600">{{ deleteTarget?.order_number }}</strong>? Data tidak dapat dikembalikan.</p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="deleteBooking" class="px-4 py-2 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
