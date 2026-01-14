<script setup>
/**
 * Index.vue - Ticket Management Page
 * Premium redesign matching Newsletter design system
 * Modern, animated, responsive
 */
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Ticket, Search, QrCode, Calendar, Check, Clock, XCircle, Eye, 
    ChevronLeft, ChevronRight, RotateCcw, Filter, Sparkles, Users,
    CheckCircle, AlertTriangle, Loader2
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    tickets: Object,
    todayStats: { type: Object, default: () => ({ total: 0, validated: 0, pending: 0 }) },
    filters: { type: Object, default: () => ({}) }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const date = ref(props.filters?.date || '');
const isValidating = ref(null);
let ctx;

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/tickets', {
        search: search.value || undefined,
        status: status.value || undefined,
        date: date.value || undefined
    }, { preserveState: true, replace: true });
};

const resetFilters = () => { search.value = ''; status.value = ''; date.value = ''; applyFilters(); };

const manualValidate = async (ticket) => {
    if (!confirm('Validasi tiket ini secara manual?')) return;
    isValidating.value = ticket.id;
    await fetch(`/admin/tickets/${ticket.id}/manual-validate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    isValidating.value = null;
    router.reload();
};

const getStatusConfig = (s) => {
    const configs = {
        valid: { class: 'from-emerald-500 to-teal-600 text-white', bg: 'bg-emerald-50', icon: CheckCircle },
        used: { class: 'from-blue-500 to-indigo-600 text-white', bg: 'bg-blue-50', icon: Check },
        expired: { class: 'from-gray-500 to-gray-600 text-white', bg: 'bg-gray-50', icon: Clock },
        cancelled: { class: 'from-red-500 to-rose-600 text-white', bg: 'bg-red-50', icon: XCircle }
    };
    return configs[s] || { class: 'from-gray-500 to-gray-600 text-white', bg: 'bg-gray-50', icon: AlertTriangle };
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// GSAP Animations
onMounted(() => {
    ctx = gsap.context(() => {
        // Animate stats cards
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        
        // Animate table rows
        gsap.fromTo('.table-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 via-emerald-600 to-cyan-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-cyan-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-emerald-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
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
                            <Ticket class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Manajemen Tiket</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ tickets?.total || 0 }} Total
                            </span>
                        </div>
                        <p class="text-teal-100/80 text-xs">Kelola dan validasi tiket pengunjung</p>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <Link 
                    href="/admin/tickets/validate"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-teal-600 text-xs font-bold hover:bg-teal-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                >
                    <QrCode class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                    <span>Scan QR Code</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
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

        <!-- Stats Cards - Clickable to filter -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- Today's Tickets -->
            <div 
                @click="date = new Date().toISOString().split('T')[0]; status = ''; applyFilters()"
                class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ todayStats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Tiket Hari Ini</p>
                    </div>
                </div>
            </div>
            
            <!-- Validated -->
            <div 
                @click="status = 'used'; applyFilters()"
                class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ todayStats.validated }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Sudah Divalidasi</p>
                    </div>
                </div>
            </div>
            
            <!-- Pending - Belum Digunakan -->
            <div 
                @click="status = 'valid'; applyFilters()"
                class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <Clock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ todayStats.pending }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Belum Digunakan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3 justify-between">
                <!-- Search & Filter -->
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-teal-500 transition-colors" />
                        <input 
                            type="text" 
                            v-model="search"
                            placeholder="Cari kode tiket..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:bg-white transition-all duration-300"
                        />
                    </div>
                    <select 
                        v-model="status"
                        @change="applyFilters"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Status</option>
                        <option value="valid">Valid</option>
                        <option value="used">Sudah Digunakan</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                    <input 
                        v-model="date" 
                        type="date" 
                        @change="applyFilters"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:bg-white transition-all cursor-pointer"
                    />
                </div>

                <!-- Reset Button -->
                <button 
                    v-if="search || status || date"
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                >
                    <RotateCcw class="w-4 h-4" />
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Kode Tiket</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Booking</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tanggal Valid</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Divalidasi</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="ticket in tickets.data" :key="ticket.id" 
                            class="table-row group hover:bg-gradient-to-r hover:from-teal-50/50 hover:to-emerald-50/30 transition-all duration-200">
                            <td class="px-4 py-3">
                                <span class="font-mono font-bold text-teal-600 text-xs">{{ ticket.ticket_code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 text-xs">{{ ticket.destination_name || '-' }}</p>
                                <p class="text-[10px] text-gray-500">{{ ticket.order_number }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                                    <Calendar class="w-3 h-3" />
                                    {{ formatDate(ticket.valid_date) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm', getStatusConfig(ticket.status).class]">
                                    <component :is="getStatusConfig(ticket.status).icon" class="w-3 h-3" />
                                    {{ ticket.status?.charAt(0).toUpperCase() + ticket.status?.slice(1) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                <template v-if="ticket.used_at">
                                    <p class="text-[10px]">{{ formatDateTime(ticket.used_at) }}</p>
                                    <p class="text-[9px] text-gray-400">oleh {{ ticket.validator_name || '-' }}</p>
                                </template>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button 
                                        v-if="ticket.status === 'valid'" 
                                        @click="manualValidate(ticket)"
                                        :disabled="isValidating === ticket.id"
                                        class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 hover:shadow-sm transition-all duration-200"
                                        title="Validasi Manual"
                                    >
                                        <Loader2 v-if="isValidating === ticket.id" class="w-4 h-4 animate-spin" />
                                        <Check v-else class="w-4 h-4" />
                                    </button>
                                    <Link 
                                        :href="`/admin/tickets/${ticket.id}`" 
                                        class="p-2 rounded-lg text-gray-400 hover:text-teal-600 hover:bg-teal-50 hover:shadow-sm transition-all duration-200"
                                        title="Lihat Detail"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="!tickets.data?.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                                        <Ticket class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Tidak ada tiket ditemukan</p>
                                    <p class="text-xs text-gray-400">Coba ubah filter pencarian Anda</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden divide-y divide-gray-100">
                <div v-for="ticket in tickets.data" :key="ticket.id"
                     class="table-row p-4 hover:bg-gray-50/80 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="font-mono font-bold text-teal-600 text-sm">{{ ticket.ticket_code }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ ticket.destination_name || '-' }}</p>
                        </div>
                        <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-gradient-to-r', getStatusConfig(ticket.status).class]">
                            <component :is="getStatusConfig(ticket.status).icon" class="w-2.5 h-2.5" />
                            {{ ticket.status?.charAt(0).toUpperCase() + ticket.status?.slice(1) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-3 text-gray-500">
                            <span class="flex items-center gap-1 text-[10px]">
                                <Calendar class="w-3.5 h-3.5" />
                                {{ formatDate(ticket.valid_date) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button 
                                v-if="ticket.status === 'valid'" 
                                @click="manualValidate(ticket)"
                                class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600"
                            >
                                <Check class="w-3.5 h-3.5" />
                            </button>
                            <Link :href="`/admin/tickets/${ticket.id}`" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-teal-50 text-teal-600 rounded-lg font-semibold text-[10px]">
                                Detail
                                <ChevronRight class="w-3 h-3" />
                            </Link>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Empty State -->
                <div v-if="!tickets.data?.length" class="p-8 text-center">
                    <Ticket class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                    <p class="text-sm font-medium text-gray-600">Tidak ada tiket ditemukan</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="tickets.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ tickets.from }}-{{ tickets.to }}</strong> dari <strong>{{ tickets.total }}</strong> tiket
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in tickets.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active 
                                ? 'bg-gradient-to-r from-teal-500 to-emerald-600 text-white shadow-md' 
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
</style>
