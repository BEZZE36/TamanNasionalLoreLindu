<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CalendarCheck, ArrowLeft, Save, MapPin, User, Calendar, CreditCard, Users, Car, Minus, Plus } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    destinations: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] }
});

const form = useForm({
    destination_id: '', visit_date: '', user_id: '',
    leader_name: '', leader_email: '', leader_phone: '', status: 'paid',
    total_adults: 0, total_children: 0, total_seniors: 0,
    total_motorcycles: 0, total_cars: 0, total_buses: 0
});

const qtyVisitors = ref({ adult: 0, child: 0, senior: 0 });
const qtyVehicles = ref({ motor: 0, car: 0, bus: 0 });

const visitorTypes = [
    { key: 'adult', label: 'Dewasa', field: 'total_adults' },
    { key: 'child', label: 'Anak-anak', field: 'total_children' },
    { key: 'senior', label: 'Pelajar/Mahasiswa', field: 'total_seniors' }
];
const vehicleTypes = [
    { key: 'motor', label: 'Motor', field: 'total_motorcycles' },
    { key: 'car', label: 'Mobil', field: 'total_cars' },
    { key: 'bus', label: 'Bus', field: 'total_buses' }
];

const currentDestination = computed(() => props.destinations.find(d => d.id == form.destination_id));

const getPrice = (category) => {
    if (!currentDestination.value?.prices) return 0;
    const price = currentDestination.value.prices.find(p => p.category === category);
    return price ? parseFloat(price.price) : 0;
};

const hasPrice = (category) => {
    if (!currentDestination.value?.prices) return false;
    return currentDestination.value.prices.some(p => p.category === category);
};

const subtotal = computed(() => {
    let total = 0;
    for (const [key, qty] of Object.entries(qtyVisitors.value)) total += qty * getPrice(key);
    for (const [key, qty] of Object.entries(qtyVehicles.value)) total += qty * getPrice(key);
    return total;
});

const serviceFee = 5000;
const total = computed(() => subtotal.value + serviceFee);

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

const fillUser = () => {
    const user = props.users.find(u => u.id == form.user_id);
    if (user) { form.leader_name = user.name; form.leader_email = user.email; form.leader_phone = user.phone_number || ''; }
    else { form.leader_name = ''; form.leader_email = ''; form.leader_phone = ''; }
};

// Sync quantities to form
watch(qtyVisitors, (val) => { form.total_adults = val.adult; form.total_children = val.child; form.total_seniors = val.senior; }, { deep: true });
watch(qtyVehicles, (val) => { form.total_motorcycles = val.motor; form.total_cars = val.car; form.total_buses = val.bus; }, { deep: true });

const submit = () => form.post('/admin/bookings', { onSuccess: () => router.visit('/admin/bookings') });
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/admin/bookings" class="p-2 rounded-xl bg-blue-100 hover:bg-blue-200 transition-colors">
                    <ArrowLeft class="w-5 h-5 text-blue-600" />
                </Link>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <CalendarCheck class="w-7 h-7 text-blue-600" />Buat Booking Baru
                    </h1>
                    <p class="text-gray-500 text-sm">Input data pemesanan manual untuk pengunjung</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Left Column -->
            <div class="space-y-4">
                <!-- Visit Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><MapPin class="w-5 h-5 text-blue-500" />Informasi Kunjungan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Destinasi *</label>
                            <select v-model="form.destination_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                <option value="">-- Pilih Destinasi --</option>
                                <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><Calendar class="w-4 h-4 inline" /> Tanggal Kunjungan *</label>
                            <input v-model="form.visit_date" type="date" :min="new Date().toISOString().split('T')[0]" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                    </div>
                </div>

                <!-- Leader Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><User class="w-5 h-5 text-blue-500" />Data Pemesan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User (Opsional)</label>
                            <select v-model="form.user_id" @change="fillUser" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm">
                                <option value="">-- Guest / Tidak Ada Akun --</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                            <input v-model="form.leader_name" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input v-model="form.leader_email" type="email" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP/WA *</label>
                                <input v-model="form.leader_phone" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><CreditCard class="w-5 h-5 text-blue-500" />Status Pembayaran</h3>
                    <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-bold">
                        <option value="pending">Pending (Menunggu Pembayaran)</option>
                        <option value="paid">Paid (Lunas - Generate Tiket)</option>
                        <option value="confirmed">Confirmed (Terkonfirmasi)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">*Jika "Paid" atau "Confirmed", E-Ticket otomatis dikirim ke email.</p>
                </div>
            </div>

            <!-- Right Column: Pricing -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <Users class="w-32 h-32" />
                    </div>
                    <h3 class="font-bold text-gray-900 mb-6 relative z-10">Rincian Pengunjung</h3>

                    <div v-if="!form.destination_id" class="text-center py-10 text-gray-500">
                        Pilih destinasi terlebih dahulu untuk melihat harga.
                    </div>

                    <div v-else class="space-y-6 relative z-10">
                        <!-- Visitors -->
                        <div class="space-y-3">
                            <p class="text-sm font-bold text-gray-700 uppercase border-b pb-2">Tiket Masuk</p>
                            <div v-for="type in visitorTypes" :key="type.key" v-show="hasPrice(type.key)" class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ type.label }}</p>
                                    <p class="text-xs text-gray-500">{{ formatRupiah(getPrice(type.key)) }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="qtyVisitors[type.key] = Math.max(0, qtyVisitors[type.key] - 1)"
                                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center font-bold text-gray-600">
                                        <Minus class="w-4 h-4" />
                                    </button>
                                    <span class="w-10 text-center font-bold">{{ qtyVisitors[type.key] }}</span>
                                    <button type="button" @click="qtyVisitors[type.key]++"
                                        class="w-8 h-8 rounded-full bg-blue-100 hover:bg-blue-200 flex items-center justify-center font-bold text-blue-600">
                                        <Plus class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicles -->
                        <div class="space-y-3">
                            <p class="text-sm font-bold text-gray-700 uppercase border-b pb-2 mt-6">Kendaraan</p>
                            <div v-for="type in vehicleTypes" :key="type.key" v-show="hasPrice(type.key)" class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ type.label }}</p>
                                    <p class="text-xs text-gray-500">{{ formatRupiah(getPrice(type.key)) }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="qtyVehicles[type.key] = Math.max(0, qtyVehicles[type.key] - 1)"
                                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center font-bold text-gray-600">
                                        <Minus class="w-4 h-4" />
                                    </button>
                                    <span class="w-10 text-center font-bold">{{ qtyVehicles[type.key] }}</span>
                                    <button type="button" @click="qtyVehicles[type.key]++"
                                        class="w-8 h-8 rounded-full bg-blue-100 hover:bg-blue-200 flex items-center justify-center font-bold text-blue-600">
                                        <Plus class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="bg-gray-50 rounded-xl p-4 space-y-2 mt-6">
                            <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span>{{ formatRupiah(subtotal) }}</span></div>
                            <div class="flex justify-between text-sm text-gray-600"><span>Biaya Layanan</span><span>{{ formatRupiah(serviceFee) }}</span></div>
                            <div class="flex justify-between font-black text-xl text-blue-700 border-t border-gray-200 pt-2 mt-2"><span>Total</span><span>{{ formatRupiah(total) }}</span></div>
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-lg rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                    <Save class="w-5 h-5" />{{ form.processing ? 'Menyimpan...' : 'Buat Booking Sekarang' }}
                </button>
            </div>
        </form>
    </div>
</template>
