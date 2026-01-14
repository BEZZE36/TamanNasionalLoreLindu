<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    CalendarCheck, Plus, Search, Eye, Edit, Trash2, Download, User, MapPin, 
    ChevronLeft, ChevronRight, Ticket, X, Check, TrendingUp, Clock, 
    CheckCircle, XCircle, AlertCircle, FileText, FileSpreadsheet, Table2,
    ChevronDown, Loader2, Users, DollarSign, Filter
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    bookings: Object,
    destinations: Array,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Local state
const search = ref(props.filters?.search || '');
const destination = ref(props.filters?.destination_id || '');
const status = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const isDeleting = ref(false);
const showExportMenu = ref(false);
const isExporting = ref(false);
let ctx;

// Animation on mount
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.table-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.03, delay: 0.3, ease: 'power2.out' }
        );
        gsap.fromTo('.filter-card',
            { opacity: 0, y: -10 },
            { opacity: 1, y: 0, duration: 0.4, delay: 0.2, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Search with debounce
let searchTimeout;
watch(search, () => { 
    clearTimeout(searchTimeout); 
    searchTimeout = setTimeout(() => applyFilters(), 300); 
});

const applyFilters = () => {
    router.get('/admin/bookings', {
        search: search.value || undefined,
        destination_id: destination.value || undefined,
        status: status.value || undefined
    }, { preserveState: true, replace: true });
};

const confirmDelete = (item) => { 
    deleteTarget.value = item; 
    showDeleteModal.value = true; 
};

const deleteBooking = () => {
    if (deleteTarget.value) {
        isDeleting.value = true;
        router.delete(`/admin/bookings/${deleteTarget.value.id}`, {
            onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; },
            onFinish: () => { isDeleting.value = false; }
        });
    }
};

const exportBookings = (format) => {
    isExporting.value = true;
    showExportMenu.value = false;
    
    const params = new URLSearchParams({
        format: format,
        search: search.value || '',
        destination_id: destination.value || '',
        status: status.value || ''
    });
    
    window.location.href = `/admin/bookings-export?${params.toString()}`;
    
    setTimeout(() => { isExporting.value = false; }, 2000);
};

// Normalize legacy statuses to new 4 statuses
const normalizeStatus = (status) => {
    const mapping = {
        'pending': 'pending',
        'awaiting_cash': 'pending',
        'paid': 'confirmed',
        'confirmed': 'confirmed',
        'used': 'used',
        'cancelled': 'cancelled',
        'expired': 'cancelled',
        'refunded': 'cancelled'
    };
    return mapping[status] || status;
};

const getStatusConfig = (s) => {
    const normalizedStat = normalizeStatus(s);
    const configs = {
        pending: { 
            class: 'from-amber-500 to-orange-600 text-white', 
            bgClass: 'bg-amber-50',
            icon: Clock,
            label: 'Pending'
        },
        confirmed: { 
            class: 'from-emerald-500 to-teal-600 text-white', 
            bgClass: 'bg-emerald-50',
            icon: CheckCircle,
            label: 'Terkonfirmasi'
        },
        used: { 
            class: 'from-blue-500 to-indigo-600 text-white', 
            bgClass: 'bg-blue-50',
            icon: Ticket,
            label: 'Sudah Digunakan'
        },
        cancelled: { 
            class: 'from-red-500 to-rose-600 text-white', 
            bgClass: 'bg-red-50',
            icon: XCircle,
            label: 'Dibatalkan'
        }
    };
    return configs[normalizedStat] || configs.pending;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
};

// Close export menu on click outside
const closeExportMenu = () => { showExportMenu.value = false; };
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-cyan-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-purple-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-cyan-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Icon with Glow -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <CalendarCheck class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Manajemen Pemesanan</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ stats.total }} Total
                            </span>
                        </div>
                        <p class="text-blue-100/80 text-xs">Kelola semua transaksi tiket dan reservasi pengunjung</p>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <Link 
                    href="/admin/bookings/create"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-blue-600 text-xs font-bold hover:bg-blue-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                >
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                    <span>Buat Booking Baru</span>
                </Link>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Bookings -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <CalendarCheck class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Booking</p>
                    </div>
                </div>
            </div>
            
            <!-- Pending -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <Clock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.pending }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Menunggu Bayar</p>
                    </div>
                </div>
            </div>
            
            <!-- Paid/Confirmed -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.paid + stats.confirmed }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Lunas / Confirmed</p>
                    </div>
                </div>
            </div>
            
            <!-- Revenue -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <DollarSign class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-lg font-black text-gray-900">{{ formatCurrency(stats.revenue).replace('Rp', 'Rp ') }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Pendapatan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Actions Card -->
        <div class="filter-card rounded-xl bg-white p-4 shadow-lg border border-gray-100 relative z-20">
            <div class="flex flex-col lg:flex-row gap-3 justify-between">
                <!-- Search & Filter -->
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
                        <input 
                            type="text" 
                            v-model="search"
                            placeholder="Cari order, nama, email..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all duration-300"
                        />
                    </div>
                    <select 
                        v-model="destination"
                        @change="applyFilters"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Destinasi</option>
                        <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                    <select 
                        v-model="status"
                        @change="applyFilters"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Terkonfirmasi</option>
                        <option value="used">Sudah Digunakan</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <!-- Export Dropdown -->
                <div class="relative z-30">
                    <button 
                        @click="showExportMenu = !showExportMenu"
                        :disabled="isExporting"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all disabled:opacity-50"
                    >
                        <Loader2 v-if="isExporting" class="w-4 h-4 animate-spin" />
                        <Download v-else class="w-4 h-4" />
                        <span>Export</span>
                        <ChevronDown class="w-3 h-3" :class="showExportMenu ? 'rotate-180' : ''" />
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <Transition name="dropdown">
                        <div v-if="showExportMenu" class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-2xl border border-gray-100 py-2 z-[100]">
                            <button @click="exportBookings('csv')" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <Table2 class="w-4 h-4 text-emerald-600" />
                                Export CSV
                            </button>
                            <button @click="exportBookings('excel')" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <FileSpreadsheet class="w-4 h-4 text-green-600" />
                                Export Excel
                            </button>
                            <button @click="exportBookings('pdf')" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <FileText class="w-4 h-4 text-red-600" />
                                Export PDF
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <!-- Table with Premium Styling -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Order Info</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tamu</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Destinasi</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Jadwal</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="(booking, index) in bookings.data" 
                            :key="booking.id" 
                            class="table-row group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-indigo-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                                        <Ticket class="w-5 h-5 text-white/90" />
                                        <div class="absolute -bottom-1 -right-1 px-1.5 py-0.5 bg-white rounded-md shadow-sm border border-gray-100">
                                            <span class="text-[8px] font-bold text-indigo-600">{{ booking.order_number?.slice(-4) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-xs">{{ booking.order_number }}</p>
                                        <p class="text-[10px] text-gray-500">{{ formatDateTime(booking.created_at) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div v-if="booking.user_avatar" class="w-9 h-9 rounded-full overflow-hidden ring-2 ring-white shadow-md">
                                        <img :src="booking.user_avatar" :alt="booking.leader_name" class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-md ring-2 ring-white">
                                        {{ booking.leader_name?.charAt(0)?.toUpperCase() || 'G' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-xs">{{ booking.leader_name }}</p>
                                        <p class="text-[10px] text-gray-500">{{ booking.leader_phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <MapPin class="w-3 h-3" />{{ booking.destination?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 text-xs">{{ formatDate(booking.visit_date) }}</p>
                                <p class="text-[10px] text-gray-500">{{ booking.total_visitors }} pax</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="[
                                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm',
                                    getStatusConfig(booking.status).class
                                ]">
                                    <component :is="getStatusConfig(booking.status).icon" class="w-3 h-3" />
                                    {{ getStatusConfig(booking.status).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-gray-900 text-xs">{{ booking.formatted_total_amount }}</p>
                                <p class="text-[10px] text-gray-500">{{ booking.payment_type || 'Unpaid' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="`/admin/bookings/${booking.id}`" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200" title="Lihat Detail">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="`/admin/bookings/${booking.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-all duration-200" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(booking)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all duration-200" title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="!bookings.data?.length">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                                        <Ticket class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada pemesanan</p>
                                    <p class="text-xs text-gray-400">Booking yang masuk akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="bookings.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ bookings.from }}-{{ bookings.to }}</strong> dari <strong>{{ bookings.total }}</strong> booking
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in bookings.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active 
                                ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-md' 
                                : link.url 
                                    ? 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' 
                                    : 'bg-gray-50 text-gray-300 cursor-not-allowed border border-gray-100'
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <!-- Header -->
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertCircle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Booking</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus booking <strong class="text-gray-900">"{{ deleteTarget?.order_number }}"</strong>? Semua data termasuk tiket dan pembayaran akan dihapus.
                        </p>
                        
                        <div class="flex gap-3 justify-end">
                            <button 
                                @click="showDeleteModal = false"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                @click="deleteBooking"
                                :disabled="isDeleting"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
                                <Trash2 v-else class="w-4 h-4" />
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    transform: translateY(-16px);
}

.modal-enter-active, .modal-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
.modal-enter-from .relative, .modal-leave-to .relative {
    transform: scale(0.95) translateY(10px);
}

.dropdown-enter-active, .dropdown-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.dropdown-enter-from, .dropdown-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
}
</style>
