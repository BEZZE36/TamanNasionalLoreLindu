<script setup>
/**
 * Show.vue - Ticket Detail Page
 * Premium redesign matching Newsletter design system
 * Modern, animated, responsive
 */
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Ticket, ArrowLeft, MapPin, Calendar, User, Mail, Phone, CreditCard, 
    Check, Clock, XCircle, QrCode, CheckCircle, Loader2, AlertTriangle,
    Sparkles, Users, Eye
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({ ticket: { type: Object, required: true } });

const isValidating = ref(false);
let ctx;

const getStatusConfig = (s) => {
    const configs = {
        valid: { class: 'from-emerald-500 to-teal-600 text-white', bg: 'bg-emerald-50', icon: CheckCircle, label: 'Valid' },
        used: { class: 'from-blue-500 to-indigo-600 text-white', bg: 'bg-blue-50', icon: Check, label: 'Sudah Digunakan' },
        expired: { class: 'from-gray-500 to-gray-600 text-white', bg: 'bg-gray-50', icon: Clock, label: 'Expired' },
        cancelled: { class: 'from-red-500 to-rose-600 text-white', bg: 'bg-red-50', icon: XCircle, label: 'Dibatalkan' }
    };
    return configs[s] || { class: 'from-gray-500 to-gray-600 text-white', bg: 'bg-gray-50', icon: AlertTriangle, label: 'Unknown' };
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);

const manualValidate = async () => {
    if (!confirm('Validasi tiket ini secara manual?')) return;
    isValidating.value = true;
    await fetch(`/admin/tickets/${props.ticket.id}/manual-validate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    isValidating.value = false;
    router.reload();
};

// GSAP Animations
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.content-card', 
            { opacity: 0, y: 20, scale: 0.98 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="content-card relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 via-emerald-600 to-cyan-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-cyan-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Back Button -->
                    <Link href="/admin/tickets" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-lg hover:bg-white/30 transition-all">
                        <ArrowLeft class="h-5 w-5 text-white" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Detail Tiket</h1>
                            <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm', getStatusConfig(ticket.status).class]">
                                <component :is="getStatusConfig(ticket.status).icon" class="w-3 h-3" />
                                {{ getStatusConfig(ticket.status).label }}
                            </span>
                        </div>
                        <p class="text-teal-100/80 text-xs font-mono">{{ ticket.ticket_code }}</p>
                    </div>
                </div>
                
                <!-- Validate Button -->
                <button 
                    v-if="ticket.status === 'valid'" 
                    @click="manualValidate"
                    :disabled="isValidating"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-600 text-xs font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg disabled:opacity-50"
                >
                    <Loader2 v-if="isValidating" class="w-4 h-4 animate-spin" />
                    <CheckCircle v-else class="w-4 h-4 group-hover:scale-110 transition-transform" />
                    <span>Validasi Manual</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Ticket Info Card -->
                <div class="content-card rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-teal-500/30">
                            <Ticket class="w-4 h-4 text-white" />
                        </div>
                        Informasi Tiket
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Kode Tiket</p>
                            <p class="font-mono font-bold text-teal-600 text-sm">{{ ticket.ticket_code }}</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Tanggal Valid</p>
                            <p class="font-semibold text-gray-900 text-xs">{{ formatDate(ticket.valid_date) }}</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Status</p>
                            <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm', getStatusConfig(ticket.status).class]">
                                <component :is="getStatusConfig(ticket.status).icon" class="w-3 h-3" />
                                {{ getStatusConfig(ticket.status).label }}
                            </span>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Pengunjung</p>
                            <p class="font-semibold text-gray-900 text-xs">{{ ticket.visitor_name || 'Tidak Ada Data' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Info Card -->
                <div v-if="ticket.booking" class="content-card rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                            <CreditCard class="w-4 h-4 text-white" />
                        </div>
                        Informasi Booking
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center">
                                <MapPin class="w-4 h-4 text-gray-500" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-xs">{{ ticket.booking.destination?.name || '-' }}</p>
                                <p class="text-[10px] text-gray-500">Destinasi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center">
                                <User class="w-4 h-4 text-gray-500" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-xs">{{ ticket.booking.leader_name }}</p>
                                <p class="text-[10px] text-gray-500">Ketua Rombongan</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center">
                                <Ticket class="w-4 h-4 text-gray-500" />
                            </div>
                            <div>
                                <p class="font-mono font-medium text-gray-900 text-xs">{{ ticket.booking.order_number }}</p>
                                <p class="text-[10px] text-gray-500">Order Number</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <!-- Validation Info -->
                <div class="content-card rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <CheckCircle class="w-4 h-4 text-white" />
                        </div>
                        Validasi
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500">Digunakan</span>
                            <span class="font-medium text-gray-900">{{ ticket.used_at ? formatDateTime(ticket.used_at) : '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500">Oleh</span>
                            <span class="font-medium text-gray-900">{{ ticket.validator_name || '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="content-card rounded-xl bg-white p-5 shadow-lg border border-gray-100 text-center">
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center mb-4 border-2 border-dashed border-gray-300">
                        <QrCode class="w-16 h-16 text-gray-400" />
                    </div>
                    <p class="font-mono font-bold text-teal-600 text-sm">{{ ticket.ticket_code }}</p>
                    <p class="text-[10px] text-gray-500 mt-1">Scan untuk validasi</p>
                </div>
            </div>
        </div>
    </div>
</template>
