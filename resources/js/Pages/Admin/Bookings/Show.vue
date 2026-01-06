<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CalendarCheck, ArrowLeft, Edit, Mail, Phone, User, MapPin, Calendar, CreditCard, Ticket, Send, XCircle, CheckCircle, Clock, Ban } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({ booking: { type: Object, required: true } });

const sendingTicket = ref(false);
const cancelling = ref(false);

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
    await fetch(`/admin/bookings/${props.booking.id}/send-ticket`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
    sendingTicket.value = false;
    router.reload();
};

const cancelBooking = async () => {
    if (!confirm('Yakin ingin membatalkan booking ini?')) return;
    cancelling.value = true;
    await fetch(`/admin/bookings/${props.booking.id}/cancel`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
    cancelling.value = false;
    router.reload();
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link href="/admin/bookings" class="p-2 rounded-xl bg-blue-100 hover:bg-blue-200 transition-colors">
                    <ArrowLeft class="w-5 h-5 text-blue-600" />
                </Link>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-bold text-gray-900">Detail Booking</h1>
                        <span :class="['px-3 py-1 rounded-full text-xs font-bold uppercase', getStatusClasses(booking.status)]">
                            {{ booking.status }}
                        </span>
                    </div>
                    <p class="text-gray-500 text-sm font-mono">{{ booking.order_number }}</p>
                </div>
            </div>
            <Link :href="`/admin/bookings/${booking.id}/edit`"
                class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 text-amber-700 font-bold rounded-xl hover:bg-amber-200 transition-colors">
                <Edit class="w-4 h-4" />Edit
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Visitor Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <CalendarCheck class="w-5 h-5 text-blue-600" />Informasi Kunjungan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Destinasi</p>
                            <p class="font-semibold text-gray-900 flex items-center gap-1">
                                <MapPin class="w-4 h-4 text-blue-500" />{{ booking.destination?.name || '-' }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Tanggal Kunjungan</p>
                            <p class="font-semibold text-gray-900 flex items-center gap-1">
                                <Calendar class="w-4 h-4 text-blue-500" />{{ formatDate(booking.visit_date) }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-blue-50 rounded-xl">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Rincian Pengunjung</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                            <div><span class="text-gray-500">Dewasa:</span> <span class="font-bold">{{ booking.total_adults || 0 }}</span></div>
                            <div><span class="text-gray-500">Anak:</span> <span class="font-bold">{{ booking.total_children || 0 }}</span></div>
                            <div><span class="text-gray-500">Pelajar:</span> <span class="font-bold">{{ booking.total_seniors || 0 }}</span></div>
                            <div><span class="text-gray-500">Motor:</span> <span class="font-bold">{{ booking.total_motorcycles || 0 }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- Tickets -->
                <div v-if="booking.tickets?.length" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Ticket class="w-5 h-5 text-green-600" />E-Tickets ({{ booking.tickets.length }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div v-for="ticket in booking.tickets" :key="ticket.id"
                            class="p-3 border border-gray-200 rounded-xl flex items-center justify-between">
                            <div>
                                <p class="font-mono text-sm font-bold text-gray-900">{{ ticket.ticket_code }}</p>
                                <p class="text-xs text-gray-500">{{ ticket.visitor_name || 'Pengunjung' }}</p>
                            </div>
                            <span :class="['px-2 py-1 rounded-full text-xs font-bold', ticket.is_used ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700']">
                                {{ ticket.is_used ? 'Used' : 'Valid' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Contact -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <User class="w-5 h-5 text-blue-600" />Data Pemesan
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-2">
                            <User class="w-4 h-4 text-gray-400" />
                            <span class="font-medium">{{ booking.leader_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Mail class="w-4 h-4 text-gray-400" />
                            <a :href="`mailto:${booking.leader_email}`" class="text-blue-600 hover:underline">{{ booking.leader_email }}</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <Phone class="w-4 h-4 text-gray-400" />
                            <a :href="`tel:${booking.leader_phone}`" class="text-blue-600 hover:underline">{{ booking.leader_phone }}</a>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <CreditCard class="w-5 h-5 text-green-600" />Pembayaran
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Total</span><span class="font-black text-xl text-blue-600">{{ formatRupiah(booking.total_amount) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Metode</span><span class="font-medium">{{ booking.payment_type || 'Manual' }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Dibuat</span><span>{{ formatDateTime(booking.created_at) }}</span></div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 space-y-3">
                    <button v-if="['paid', 'confirmed'].includes(booking.status)" @click="sendTicket" :disabled="sendingTicket"
                        class="w-full py-2.5 bg-green-100 text-green-700 font-bold rounded-xl hover:bg-green-200 transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                        <Send class="w-4 h-4" />{{ sendingTicket ? 'Mengirim...' : 'Kirim Ulang Tiket' }}
                    </button>
                    <button v-if="!['cancelled', 'used'].includes(booking.status)" @click="cancelBooking" :disabled="cancelling"
                        class="w-full py-2.5 bg-red-50 text-red-600 font-bold rounded-xl hover:bg-red-100 transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                        <Ban class="w-4 h-4" />{{ cancelling ? 'Membatalkan...' : 'Batalkan Booking' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
