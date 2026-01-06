<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Ticket, ArrowLeft, MapPin, Calendar, User, Mail, Phone, CreditCard, Check, Clock, XCircle, QrCode } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({ ticket: { type: Object, required: true } });

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
    return new Date(date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);

const manualValidate = async () => {
    if (!confirm('Validasi tiket ini secara manual?')) return;
    await fetch(`/admin/tickets/${props.ticket.id}/manual-validate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    router.reload();
};
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/admin/tickets" class="p-2 rounded-xl bg-teal-100 hover:bg-teal-200 transition-colors">
                    <ArrowLeft class="w-5 h-5 text-teal-600" />
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-bold text-gray-900">Detail Tiket</h1>
                        <span :class="['px-3 py-1 rounded-full text-xs font-bold uppercase', getStatusClasses(ticket.status)]">
                            {{ ticket.status }}
                        </span>
                    </div>
                    <p class="text-gray-500 font-mono text-sm">{{ ticket.ticket_code }}</p>
                </div>
            </div>
            <button v-if="ticket.status === 'valid'" @click="manualValidate"
                class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/30 hover:shadow-green-500/50 transition-all flex items-center gap-2">
                <Check class="w-5 h-5" />Validasi Manual
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Ticket Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Ticket class="w-5 h-5 text-teal-600" />Informasi Tiket
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Kode Tiket</p>
                            <p class="font-mono font-bold text-teal-600 text-lg">{{ ticket.ticket_code }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Tanggal Valid</p>
                            <p class="font-semibold text-gray-900">{{ formatDate(ticket.valid_date) }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Status</p>
                            <span :class="['px-3 py-1 rounded-full text-sm font-bold', getStatusClasses(ticket.status)]">
                                {{ ticket.status?.charAt(0).toUpperCase() + ticket.status?.slice(1) }}
                            </span>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pengunjung</p>
                            <p class="font-semibold text-gray-900">{{ ticket.visitor_name || 'Tidak Ada Data' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Info -->
                <div v-if="ticket.booking" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <CreditCard class="w-5 h-5 text-blue-600" />Informasi Booking
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <MapPin class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="font-medium text-gray-900">{{ ticket.booking.destination?.name || '-' }}</p>
                                <p class="text-[11px] text-gray-500">Destinasi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <User class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="font-medium text-gray-900">{{ ticket.booking.leader_name }}</p>
                                <p class="text-[11px] text-gray-500">Ketua Rombongan</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Ticket class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="font-mono font-medium text-gray-900">{{ ticket.booking.order_number }}</p>
                                <p class="text-[11px] text-gray-500">Order Number</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Validation Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Check class="w-5 h-5 text-green-600" />Validasi
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Digunakan</span>
                            <span class="font-medium">{{ ticket.used_at ? formatDateTime(ticket.used_at) : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Oleh</span>
                            <span class="font-medium">{{ ticket.validator_name || '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-center">
                    <div class="w-32 h-32 mx-auto bg-gray-100 rounded-xl flex items-center justify-center mb-4">
                        <QrCode class="w-16 h-16 text-gray-400" />
                    </div>
                    <p class="font-mono font-bold text-teal-600 text-sm">{{ ticket.ticket_code }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
