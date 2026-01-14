<script setup>
import { useForm, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    CalendarCheck, ArrowLeft, Save, MapPin, User, Calendar, CreditCard, 
    Users, Car, Minus, Plus, Ticket, Bike, Bus, Loader2, AlertCircle,
    CheckCircle, Info, Sparkles
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    destinations: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const form = useForm({
    destination_id: '', visit_date: '', user_id: '',
    leader_name: '', leader_email: '', leader_phone: '', status: 'confirmed',
    total_adults: 0, total_children: 0, total_seniors: 0,
    total_motorcycles: 0, total_cars: 0, total_buses: 0
});

const qtyVisitors = ref({ adult: 0, child: 0, senior: 0 });
const qtyVehicles = ref({ motor: 0, car: 0, bus: 0 });
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

const visitorTypes = [
    { key: 'adult', label: 'Dewasa', field: 'total_adults', icon: User, color: 'blue' },
    { key: 'child', label: 'Anak-anak', field: 'total_children', icon: Users, color: 'green' },
    { key: 'senior', label: 'Pelajar/Mahasiswa', field: 'total_seniors', icon: Sparkles, color: 'purple' }
];
const vehicleTypes = [
    { key: 'motor', label: 'Motor', field: 'total_motorcycles', icon: Bike, color: 'amber' },
    { key: 'car', label: 'Mobil', field: 'total_cars', icon: Car, color: 'indigo' },
    { key: 'bus', label: 'Bus', field: 'total_buses', icon: Bus, color: 'rose' }
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
const totalVisitors = computed(() => qtyVisitors.value.adult + qtyVisitors.value.child + qtyVisitors.value.senior);

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

const fillUser = () => {
    const user = props.users.find(u => u.id == form.user_id);
    if (user) { 
        form.leader_name = user.name; 
        form.leader_email = user.email; 
        form.leader_phone = user.phone || ''; 
    }
    else { 
        form.leader_name = ''; 
        form.leader_email = ''; 
        form.leader_phone = ''; 
    }
};

// Sync quantities to form
watch(qtyVisitors, (val) => { 
    form.total_adults = val.adult; 
    form.total_children = val.child; 
    form.total_seniors = val.senior; 
}, { deep: true });

watch(qtyVehicles, (val) => { 
    form.total_motorcycles = val.motor; 
    form.total_cars = val.car; 
    form.total_buses = val.bus; 
}, { deep: true });

const submit = () => form.post('/admin/bookings', { onSuccess: () => router.visit('/admin/bookings') });
</script>

<template>
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Premium Header -->
        <div class="header-section flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link href="/admin/bookings" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 hover:from-blue-200 hover:to-indigo-200 transition-all shadow-sm">
                    <ArrowLeft class="w-5 h-5 text-blue-600 group-hover:-translate-x-0.5 transition-transform" />
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30">
                            <CalendarCheck class="w-4 h-4 text-white" />
                        </div>
                        <h1 class="text-lg font-bold text-gray-900">Buat Booking Baru</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Input data pemesanan manual untuk pengunjung</p>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <Transition name="slide">
            <div v-if="form.errors.error" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 border border-red-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-400 to-rose-500 shadow-lg">
                    <AlertCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-red-800">{{ form.errors.error }}</p>
            </div>
        </Transition>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <!-- Left Column -->
            <div class="lg:col-span-3 space-y-4">
                <!-- Visit Info Card -->
                <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 shadow-md">
                                <MapPin class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Informasi Kunjungan</h3>
                                <p class="text-[10px] text-gray-500">Pilih destinasi dan tanggal kunjungan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Pilih Destinasi <span class="text-red-500">*</span></label>
                            <select v-model="form.destination_id" required 
                                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all">
                                <option value="">-- Pilih Destinasi --</option>
                                <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-medium text-gray-700 mb-1.5">
                                <Calendar class="w-3.5 h-3.5" />Tanggal Kunjungan <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.visit_date" type="date" :min="new Date().toISOString().split('T')[0]" required
                                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all">
                        </div>
                    </div>
                </div>

                <!-- Leader Info Card -->
                <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md">
                                <User class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Data Pemesan</h3>
                                <p class="text-[10px] text-gray-500">Informasi kontak ketua rombongan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Pilih User (Opsional)</label>
                            <select v-model="form.user_id" @change="fillUser" 
                                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                                <option value="">-- Guest / Tidak Ada Akun --</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input v-model="form.leader_name" type="text" required 
                                class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                            <p v-if="form.errors.leader_name" class="text-[10px] text-red-500 mt-1">{{ form.errors.leader_name }}</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                                <input v-model="form.leader_email" type="email" required 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                                <p v-if="form.errors.leader_email" class="text-[10px] text-red-500 mt-1">{{ form.errors.leader_email }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">No. HP/WA <span class="text-red-500">*</span></label>
                                <input v-model="form.leader_phone" type="text" required 
                                    class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                                <p v-if="form.errors.leader_phone" class="text-[10px] text-red-500 mt-1">{{ form.errors.leader_phone }}</p>
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
                                <h3 class="font-bold text-gray-900 text-sm">Status Pembayaran</h3>
                                <p class="text-[10px] text-gray-500">Pilih status awal booking</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <!-- Status is auto-determined, display only -->
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-100 bg-gray-50/50">
                            <span class="text-2xl">⏳</span>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Pending (Menunggu Pembayaran)</p>
                                <p class="text-[10px] text-gray-500">Status awal booking baru</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 mt-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                            <Info class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" />
                            <p class="text-[10px] text-blue-700">Untuk langsung konfirmasi booking, gunakan pembayaran tunai di halaman Scan Tiket setelah booking dibuat.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Pricing -->
            <div class="lg:col-span-2 space-y-4">
                <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden sticky top-4">
                    <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 shadow-md">
                                <Ticket class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">Rincian Pengunjung</h3>
                                <p class="text-[10px] text-gray-500">Pilih jumlah tiket dan kendaraan</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <div v-if="!form.destination_id" class="text-center py-8">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mx-auto mb-3">
                                <MapPin class="w-7 h-7 text-gray-400" />
                            </div>
                            <p class="text-sm font-medium text-gray-600">Pilih destinasi terlebih dahulu</p>
                            <p class="text-[10px] text-gray-400 mt-1">Harga akan ditampilkan setelah memilih destinasi</p>
                        </div>

                        <div v-else class="space-y-5">
                            <!-- Visitors -->
                            <div class="space-y-3">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 pb-2">Tiket Masuk</p>
                                <div v-for="type in visitorTypes" :key="type.key" v-show="hasPrice(type.key)" 
                                    class="flex items-center justify-between p-3 rounded-xl bg-gray-50/50 border border-gray-100 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <div :class="`w-8 h-8 rounded-lg bg-gradient-to-br from-${type.color}-100 to-${type.color}-200 flex items-center justify-center`">
                                            <component :is="type.icon" :class="`w-4 h-4 text-${type.color}-600`" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-xs">{{ type.label }}</p>
                                            <p class="text-[10px] text-gray-500">{{ formatRupiah(getPrice(type.key)) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="qtyVisitors[type.key] = Math.max(0, qtyVisitors[type.key] - 1)"
                                            class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-gray-600 transition-colors">
                                            <Minus class="w-3.5 h-3.5" />
                                        </button>
                                        <span class="w-8 text-center font-bold text-sm">{{ qtyVisitors[type.key] }}</span>
                                        <button type="button" @click="qtyVisitors[type.key]++"
                                            class="w-7 h-7 rounded-lg bg-blue-500 hover:bg-blue-600 flex items-center justify-center text-white transition-colors">
                                            <Plus class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicles -->
                            <div class="space-y-3">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 pb-2 mt-4">Kendaraan</p>
                                <div v-for="type in vehicleTypes" :key="type.key" v-show="hasPrice(type.key)" 
                                    class="flex items-center justify-between p-3 rounded-xl bg-gray-50/50 border border-gray-100 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <div :class="`w-8 h-8 rounded-lg bg-gradient-to-br from-${type.color}-100 to-${type.color}-200 flex items-center justify-center`">
                                            <component :is="type.icon" :class="`w-4 h-4 text-${type.color}-600`" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-xs">{{ type.label }}</p>
                                            <p class="text-[10px] text-gray-500">{{ formatRupiah(getPrice(type.key)) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="qtyVehicles[type.key] = Math.max(0, qtyVehicles[type.key] - 1)"
                                            class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-gray-600 transition-colors">
                                            <Minus class="w-3.5 h-3.5" />
                                        </button>
                                        <span class="w-8 text-center font-bold text-sm">{{ qtyVehicles[type.key] }}</span>
                                        <button type="button" @click="qtyVehicles[type.key]++"
                                            class="w-7 h-7 rounded-lg bg-blue-500 hover:bg-blue-600 flex items-center justify-center text-white transition-colors">
                                            <Plus class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 space-y-2 mt-4 border border-gray-200">
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Total Pengunjung</span>
                                    <span class="font-semibold">{{ totalVisitors }} orang</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Subtotal</span>
                                    <span class="font-semibold">{{ formatRupiah(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Biaya Layanan</span>
                                    <span class="font-semibold">{{ formatRupiah(serviceFee) }}</span>
                                </div>
                                <div class="flex justify-between font-black text-lg text-blue-700 border-t border-gray-300 pt-3 mt-2">
                                    <span>Total</span>
                                    <span>{{ formatRupiah(total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="form.processing || totalVisitors < 1"
                    class="form-card w-full py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                    <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                    <Save v-else class="w-5 h-5" />
                    {{ form.processing ? 'Menyimpan...' : 'Buat Booking Sekarang' }}
                </button>
            </div>
        </form>
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
