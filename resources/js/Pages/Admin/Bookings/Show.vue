<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    CalendarCheck, ArrowLeft, Edit, Mail, Phone, User, MapPin, Calendar, 
    CreditCard, Ticket, Send, Ban, CheckCircle, Clock, XCircle, 
    Loader2, AlertCircle, Users, Car, Bike, Bus, ExternalLink, Copy, Check, Printer
} from 'lucide-vue-next';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({ booking: { type: Object, required: true } });

const page = usePage();
const flash = computed(() => page.props.flash || {});

const sendingTicket = ref(false);
const cancelling = ref(false);
const copiedCode = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.info-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power2.out' }
        );
        gsap.fromTo('.ticket-item', 
            { opacity: 0, scale: 0.95 }, 
            { opacity: 1, scale: 1, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.header-section',
            { opacity: 0, x: -20 },
            { opacity: 1, x: 0, duration: 0.4, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

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
            bgClass: 'bg-amber-50 border-amber-200',
            icon: Clock,
            label: 'Pending',
            description: 'Menunggu Pembayaran'
        },
        confirmed: { 
            class: 'from-emerald-500 to-teal-600 text-white', 
            bgClass: 'bg-emerald-50 border-emerald-200',
            icon: CheckCircle,
            label: 'Terkonfirmasi',
            description: 'Pembayaran Terkonfirmasi'
        },
        used: { 
            class: 'from-blue-500 to-indigo-600 text-white', 
            bgClass: 'bg-blue-50 border-blue-200',
            icon: Ticket,
            label: 'Sudah Digunakan',
            description: 'Tiket Sudah Digunakan'
        },
        cancelled: { 
            class: 'from-red-500 to-rose-600 text-white', 
            bgClass: 'bg-red-50 border-red-200',
            icon: XCircle,
            label: 'Dibatalkan',
            description: 'Dibatalkan'
        }
    };
    return configs[normalizedStat] || configs.pending;
};

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const sendTicket = async () => {
    if (!confirm('Kirim ulang tiket ke email?')) return;
    sendingTicket.value = true;
    await fetch(`/admin/bookings/${props.booking.id}/send-ticket`, { 
        method: 'POST', 
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } 
    });
    sendingTicket.value = false;
    router.reload();
};

const cancelBooking = async () => {
    if (!confirm('Yakin ingin membatalkan booking ini?')) return;
    cancelling.value = true;
    await fetch(`/admin/bookings/${props.booking.id}/cancel`, { 
        method: 'POST', 
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } 
    });
    cancelling.value = false;
    router.reload();
};

const copyTicketCode = (code) => {
    navigator.clipboard.writeText(code);
    copiedCode.value = code;
    setTimeout(() => { copiedCode.value = null; }, 2000);
};
</script>

<template>
    <div class="space-y-5">
        <!-- Premium Header -->
        <div class="header-section flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link href="/admin/bookings" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 hover:from-blue-200 hover:to-indigo-200 transition-all shadow-sm">
                    <ArrowLeft class="w-5 h-5 text-blue-600 group-hover:-translate-x-0.5 transition-transform" />
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-bold text-gray-900">Detail Booking</h1>
                        <span :class="[
                            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm',
                            getStatusConfig(booking.status).class
                        ]">
                            <component :is="getStatusConfig(booking.status).icon" class="w-3 h-3" />
                            {{ getStatusConfig(booking.status).label }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5 font-mono">{{ booking.order_number }}</p>
                </div>
            </div>
            <Link :href="`/admin/bookings/${booking.id}/edit`"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all shadow-md">
                <Edit class="w-4 h-4" />Edit Booking
            </Link>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Visit Info Card -->
                <div class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 shadow-md">
                                <CalendarCheck class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Informasi Kunjungan</h3>
                                <p class="text-[10px] text-gray-500">Detail destinasi dan jadwal</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1.5">Destinasi</p>
                                <p class="font-semibold text-gray-900 text-sm flex items-center gap-1.5">
                                    <MapPin class="w-4 h-4 text-blue-500" />{{ booking.destination?.name || '-' }}
                                </p>
                            </div>
                            <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1.5">Tanggal Kunjungan</p>
                                <p class="font-semibold text-gray-900 text-sm flex items-center gap-1.5">
                                    <Calendar class="w-4 h-4 text-blue-500" />{{ formatDate(booking.visit_date) }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Visitor Details -->
                        <div class="mt-4 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                            <p class="text-[10px] text-gray-500 uppercase font-bold mb-3">Rincian Pengunjung</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="flex items-center gap-2 bg-white p-2.5 rounded-lg border border-blue-100">
                                    <User class="w-4 h-4 text-blue-500" />
                                    <div>
                                        <p class="text-[10px] text-gray-500">Dewasa</p>
                                        <p class="font-bold text-sm text-gray-900">{{ booking.total_adults || 0 }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-white p-2.5 rounded-lg border border-green-100">
                                    <Users class="w-4 h-4 text-green-500" />
                                    <div>
                                        <p class="text-[10px] text-gray-500">Anak</p>
                                        <p class="font-bold text-sm text-gray-900">{{ booking.total_children || 0 }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-white p-2.5 rounded-lg border border-purple-100">
                                    <Users class="w-4 h-4 text-purple-500" />
                                    <div>
                                        <p class="text-[10px] text-gray-500">Pelajar</p>
                                        <p class="font-bold text-sm text-gray-900">{{ booking.total_seniors || 0 }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-white p-2.5 rounded-lg border border-amber-100">
                                    <Bike class="w-4 h-4 text-amber-500" />
                                    <div>
                                        <p class="text-[10px] text-gray-500">Motor</p>
                                        <p class="font-bold text-sm text-gray-900">{{ booking.total_motorcycles || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- E-Tickets Card -->
                <div v-if="booking.tickets?.length" class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md">
                                <Ticket class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">E-Tickets</h3>
                                <p class="text-[10px] text-gray-500">{{ booking.tickets.length }} tiket diterbitkan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div v-for="ticket in booking.tickets" :key="ticket.id"
                                class="ticket-item group p-3 border-2 border-dashed rounded-xl flex items-center justify-between transition-all"
                                :class="ticket.is_used ? 'border-gray-200 bg-gray-50' : 'border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50'">
                                <div class="flex items-center gap-3">
                                    <div :class="[
                                        'w-10 h-10 rounded-lg flex items-center justify-center shadow-sm',
                                        ticket.is_used ? 'bg-gray-200' : 'bg-gradient-to-br from-emerald-400 to-teal-500'
                                    ]">
                                        <Ticket :class="ticket.is_used ? 'w-5 h-5 text-gray-500' : 'w-5 h-5 text-white'" />
                                    </div>
                                    <div>
                                        <p class="font-mono text-xs font-bold text-gray-900">{{ ticket.ticket_code }}</p>
                                        <p class="text-[10px] text-gray-500">{{ ticket.visitor_name || 'Pengunjung' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="copyTicketCode(ticket.ticket_code)" class="p-1.5 rounded-lg hover:bg-white transition-colors" title="Copy">
                                        <Check v-if="copiedCode === ticket.ticket_code" class="w-4 h-4 text-emerald-600" />
                                        <Copy v-else class="w-4 h-4 text-gray-400" />
                                    </button>
                                    <span :class="[
                                        'px-2.5 py-1 rounded-full text-[10px] font-bold',
                                        ticket.is_used ? 'bg-gray-200 text-gray-600' : 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-sm'
                                    ]">
                                        {{ ticket.is_used ? 'Used' : 'Valid' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Items -->
                <div v-if="booking.items?.length" class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-violet-50 to-purple-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 shadow-md">
                                <CreditCard class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Rincian Biaya</h3>
                                <p class="text-[10px] text-gray-500">Detail item yang dipesan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="space-y-2">
                            <div v-for="item in booking.items" :key="item.id" class="flex justify-between items-center py-2 px-3 rounded-lg bg-gray-50 border border-gray-100">
                                <span class="text-xs text-gray-700">{{ item.quantity }}x {{ item.label }}</span>
                                <span class="text-xs font-bold text-gray-900">{{ formatRupiah(item.total_price) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="font-black text-xl text-blue-600">{{ formatRupiah(booking.total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Contact Card -->
                <div class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 shadow-md">
                                <User class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Data Pemesan</h3>
                                <p class="text-[10px] text-gray-500">Informasi kontak</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <User class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="font-medium text-sm text-gray-900">{{ booking.leader_name }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <Mail class="w-4 h-4 text-emerald-600" />
                            </div>
                            <a :href="`mailto:${booking.leader_email}`" class="text-sm text-blue-600 hover:underline">{{ booking.leader_email }}</a>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <Phone class="w-4 h-4 text-purple-600" />
                            </div>
                            <a :href="`tel:${booking.leader_phone}`" class="text-sm text-blue-600 hover:underline">{{ booking.leader_phone }}</a>
                        </div>
                    </div>
                </div>

                <!-- Payment Card -->
                <div class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-violet-50 to-purple-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 shadow-md">
                                <CreditCard class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Pembayaran</h3>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Total</span>
                            <span class="font-black text-lg text-blue-600">{{ formatRupiah(booking.total_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Metode</span>
                            <span class="font-medium text-xs text-gray-900">{{ booking.payment_type || 'Manual' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Dibuat</span>
                            <span class="text-xs text-gray-700">{{ formatDateTime(booking.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5 space-y-3">
                    <button v-if="booking.status === 'confirmed'" @click="sendTicket" :disabled="sendingTicket"
                        class="w-full py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-xs rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Loader2 v-if="sendingTicket" class="w-4 h-4 animate-spin" />
                        <Send v-else class="w-4 h-4" />
                        {{ sendingTicket ? 'Mengirim...' : 'Kirim Ulang Tiket' }}
                    </button>
                    <a :href="`/admin/bookings/${booking.id}/invoice`" target="_blank"
                        class="w-full py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-xs rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <Printer class="w-4 h-4" />
                        Cetak Invoice (POS 80mm)
                    </a>
                    <button v-if="!['cancelled', 'used'].includes(booking.status)" @click="cancelBooking" :disabled="cancelling"
                        class="w-full py-2.5 bg-gradient-to-r from-red-50 to-rose-50 text-red-600 border border-red-200 font-bold text-xs rounded-xl hover:from-red-100 hover:to-rose-100 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Loader2 v-if="cancelling" class="w-4 h-4 animate-spin" />
                        <Ban v-else class="w-4 h-4" />
                        {{ cancelling ? 'Membatalkan...' : 'Batalkan Booking' }}
                    </button>
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
