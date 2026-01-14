<script setup>
import { useForm, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    CalendarCheck, ArrowLeft, Save, MapPin, User, Calendar, CreditCard, 
    Loader2, AlertCircle, Info, X
} from 'lucide-vue-next';
import { onMounted, onBeforeUnmount, computed } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    booking: { type: Object, required: true },
    destinations: { type: Array, default: () => [] }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Helper to format date for input[type=date]
// IMPORTANT: Avoid using new Date().toISOString() as it causes timezone shift
const formatDateForInput = (dateValue) => {
    if (!dateValue) return '';
    
    // Convert to string if not already
    const dateStr = String(dateValue);
    
    // Handle ISO string like "2026-01-09T00:00:00.000000Z" or "2026-01-09 00:00:00"
    // Extract just the YYYY-MM-DD part without timezone conversion
    const isoMatch = dateStr.match(/^(\d{4}-\d{2}-\d{2})/);
    if (isoMatch) {
        return isoMatch[1];
    }
    
    // Handle date string like "2026-01-09" 
    if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return dateStr;
    }
    
    // Handle other formats like "09/01/2026" or "09 Jan 2026"
    // Use local date methods to avoid timezone issues
    try {
        const date = new Date(dateValue);
        if (!isNaN(date.getTime())) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    } catch (e) {}
    
    return '';
};

const form = useForm({
    visit_date: formatDateForInput(props.booking.visit_date),
    leader_name: props.booking.leader_name || '',
    leader_email: props.booking.leader_email || '',
    leader_phone: props.booking.leader_phone || '',
    status: props.booking.status || 'pending'
});

let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power2.out' }
        );
        gsap.fromTo('.header-section',
            { opacity: 0, x: -20 },
            { opacity: 1, x: 0, duration: 0.4, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);

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
        pending: { emoji: '⏳', label: 'Pending (Menunggu Pembayaran)' },
        confirmed: { emoji: '✅', label: 'Confirmed (Terkonfirmasi)' },
        used: { emoji: '✔️', label: 'Used (Sudah Digunakan)' },
        cancelled: { emoji: '❌', label: 'Cancelled (Dibatalkan)' }
    };
    return configs[normalizedStat] || configs.pending;
};

const submit = () => form.put(`/admin/bookings/${props.booking.id}`, { 
    onSuccess: () => router.visit(`/admin/bookings/${props.booking.id}`) 
});
</script>

<template>
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Premium Header -->
        <div class="header-section flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link :href="`/admin/bookings/${booking.id}`" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 hover:from-blue-200 hover:to-indigo-200 transition-all shadow-sm">
                    <ArrowLeft class="w-5 h-5 text-blue-600 group-hover:-translate-x-0.5 transition-transform" />
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30">
                            <CalendarCheck class="w-4 h-4 text-white" />
                        </div>
                        <h1 class="text-lg font-bold text-gray-900">Edit Booking</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ booking.order_number }}</p>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <Transition name="slide">
            <div v-if="Object.keys(form.errors).length" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 border border-red-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-400 to-rose-500 shadow-lg">
                    <AlertCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-red-800">Ada kesalahan dalam form. Silakan periksa kembali.</p>
            </div>
        </Transition>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Main Form -->
            <form @submit.prevent="submit" class="lg:col-span-2 space-y-4">
                <!-- Visit Info Card -->
                <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 shadow-md">
                                <MapPin class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Informasi Kunjungan</h3>
                                <p class="text-[10px] text-gray-500">Detail destinasi dan tanggal kunjungan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Destinasi</label>
                            <input :value="booking.destination?.name" type="text" readonly
                                class="w-full px-4 py-2.5 text-sm bg-gray-100 text-gray-500 rounded-xl cursor-not-allowed border-2 border-gray-200">
                            <p class="text-[10px] text-gray-400 mt-1">Destinasi tidak dapat diubah</p>
                        </div>
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-medium text-gray-700 mb-1.5">
                                <Calendar class="w-3.5 h-3.5" />Tanggal Kunjungan
                            </label>
                            <input v-model="form.visit_date" type="date" 
                                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all">
                        </div>
                    </div>
                </div>

                <!-- Contact Info Card -->
                <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md">
                                <User class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Data Kontak (Ketua)</h3>
                                <p class="text-[10px] text-gray-500">Informasi kontak pemesan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                            <input v-model="form.leader_name" type="text" 
                                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Email</label>
                                <input v-model="form.leader_email" type="email" 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">No. HP / WA</label>
                                <input v-model="form.leader_phone" type="text" 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-violet-50 to-purple-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 shadow-md">
                                <CreditCard class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Status & Pembayaran</h3>
                                <p class="text-[10px] text-gray-500">Ubah status booking</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <!-- Status is auto-determined, display only -->
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-100 bg-gray-50/50">
                            <span class="text-2xl">{{ getStatusConfig(form.status).emoji }}</span>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ getStatusConfig(form.status).label }}</p>
                                <p class="text-[10px] text-gray-500">Status otomatis berdasarkan kondisi booking</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 mt-3 p-3 rounded-lg bg-amber-50 border border-amber-100">
                            <Info class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" />
                            <p class="text-[10px] text-amber-700">Status akan berubah otomatis: Pending → Confirmed (setelah bayar) → Used (setelah validasi) / Cancelled (jika expired)</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                        <Save v-else class="w-5 h-5" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                    <Link :href="`/admin/bookings/${booking.id}`"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-medium text-sm rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <X class="w-4 h-4" />
                        Batal
                    </Link>
                </div>
            </form>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Info Card -->
                <div class="form-card bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 p-4 rounded-xl">
                    <div class="flex items-start gap-2">
                        <Info class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" />
                        <div>
                            <p class="font-bold text-blue-800 text-xs mb-1">Catatan:</p>
                            <p class="text-[10px] text-blue-700 leading-relaxed">Jumlah pengunjung tidak dapat diubah melalui menu Edit. Jika ada perubahan jumlah tamu, silakan buat booking baru.</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-sm">Rincian Saat Ini</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Total Tamu</span>
                            <span class="font-bold text-gray-900">{{ booking.total_visitors || 0 }} pax</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Total Biaya</span>
                            <span class="font-black text-lg text-blue-600">{{ formatRupiah(booking.total_amount) }}</span>
                        </div>
                        <div v-if="booking.items?.length" class="border-t border-gray-100 pt-3 mt-3 space-y-2">
                            <div v-for="item in booking.items" :key="item.id" class="flex justify-between text-[10px]">
                                <span class="text-gray-500">{{ item.quantity }}x {{ item.label }}</span>
                                <span class="font-medium text-gray-700">{{ formatRupiah(item.total_price) }}</span>
                            </div>
                        </div>
                    </div>
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
