<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CalendarCheck, ArrowLeft, Save, MapPin, User, Calendar, CreditCard } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    booking: { type: Object, required: true },
    destinations: { type: Array, default: () => [] }
});

const form = useForm({
    visit_date: props.booking.visit_date?.split('T')[0] || '',
    leader_name: props.booking.leader_name || '',
    leader_email: props.booking.leader_email || '',
    leader_phone: props.booking.leader_phone || '',
    status: props.booking.status || 'pending'
});

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);

const submit = () => form.put(`/admin/bookings/${props.booking.id}`, { onSuccess: () => router.visit(`/admin/bookings/${props.booking.id}`) });
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link :href="`/admin/bookings/${booking.id}`" class="p-2 rounded-xl bg-blue-100 hover:bg-blue-200 transition-colors">
                    <ArrowLeft class="w-5 h-5 text-blue-600" />
                </Link>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <CalendarCheck class="w-7 h-7 text-blue-600" />Edit Booking
                    </h1>
                    <p class="text-gray-500 text-sm font-mono">#{{ booking.order_number }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Main Form -->
            <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
                <!-- Visit Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Informasi Kunjungan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Destinasi</label>
                            <input :value="booking.destination?.name" type="text" readonly
                                class="w-full px-4 py-2.5 bg-gray-100 rounded-xl text-gray-500 cursor-not-allowed">
                            <p class="text-xs text-gray-400 mt-1">Destinasi tidak dapat diubah.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><Calendar class="w-4 h-4 inline" /> Tanggal Kunjungan</label>
                            <input v-model="form.visit_date" type="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Data Kontak (Ketua)</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input v-model="form.leader_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="form.leader_email" type="email" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WA</label>
                                <input v-model="form.leader_phone" type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Status & Pembayaran</h3>
                    <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-bold">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="used">Used</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Save class="w-5 h-5" />{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                    <Link :href="`/admin/bookings/${booking.id}`"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors">
                        Batal
                    </Link>
                </div>
            </form>

            <!-- Sidebar -->
            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl text-sm text-blue-800">
                    <p class="font-bold mb-1">Catatan:</p>
                    <p>Jumlah pengunjung tidak dapat diubah melalui menu Edit. Jika ada perubahan jumlah tamu, silakan buat booking baru.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Rincian Saat Ini</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Tamu</span>
                            <span class="font-medium">{{ booking.total_visitors || 0 }} pax</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Biaya</span>
                            <span class="font-bold text-lg text-blue-600">{{ formatRupiah(booking.total_amount) }}</span>
                        </div>
                        <div v-if="booking.items?.length" class="border-t pt-3 mt-2">
                            <div v-for="item in booking.items" :key="item.id" class="flex justify-between text-xs py-1">
                                <span class="text-gray-500">{{ item.quantity }}x {{ item.label }}</span>
                                <span>{{ formatRupiah(item.total_price) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
