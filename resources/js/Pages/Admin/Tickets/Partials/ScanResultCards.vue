<script setup>
import { Banknote, CheckCircle, User, MapPin, Calendar, Users, Phone, CreditCard, LogIn } from 'lucide-vue-next';

const props = defineProps({
    result: Object,
    loading: Boolean
});

const emit = defineEmits(['processPayment', 'validateEntry', 'resetResult']);
</script>

<template>
    <!-- Empty State -->
    <div v-show="!result && !loading" class="bg-white rounded-3xl shadow-xl border border-gray-100 p-12 text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h2m14 0h-4M4 4h4m12 0h-4m0 0V4" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Siap untuk Scan</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Scan QR Code pada tiket atau masukkan kode secara manual untuk memulai
            proses pembayaran atau validasi masuk.</p>
    </div>

    <!-- Result Card: Payment Required -->
    <Transition name="fade">
        <div v-if="result?.status === 'payment_required'"
            class="bg-white rounded-3xl shadow-xl border border-amber-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <Banknote class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white">{{ result?.title }}</h4>
                            <p class="text-white/80 text-sm">Silakan konfirmasi pembayaran tunai</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-white text-xs font-bold uppercase tracking-wider">Belum
                        Bayar</span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Amount Display -->
                <div class="text-center py-4 mb-5 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium mb-1">Total Tagihan</p>
                    <h2 class="text-xl font-bold text-gray-900">{{ result?.booking?.formatted_amount }}</h2>
                </div>

                <!-- Booking Details Grid -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <CreditCard class="w-3 h-3" /> Nomor Order
                        </p>
                        <p class="font-bold text-gray-900 font-mono">{{ result?.booking?.order_number }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <User class="w-3 h-3" /> Nama Pemesan
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.customer_name }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <MapPin class="w-3 h-3" /> Destinasi
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.destination }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Calendar class="w-3 h-3" /> Tanggal Kunjungan
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.visit_day }}, {{ result?.booking?.visit_date }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Users class="w-3 h-3" /> Jumlah Pengunjung
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.total_visitors }} Orang</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Phone class="w-3 h-3" /> Telepon
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.customer_phone || '-' }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button 
                        @click="emit('processPayment')" 
                        :disabled="loading"
                        class="flex-1 py-4 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/30 disabled:opacity-50">
                        <CheckCircle class="w-4 h-4" />
                        Konfirmasi Pembayaran Tunai
                    </button>
                    <button 
                        @click="emit('resetResult')"
                        class="px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition-all">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Result Card: Ready for Entry (Valid Ticket) -->
    <Transition name="fade">
        <div v-if="result?.status === 'ready_for_entry'"
            class="bg-white rounded-3xl shadow-xl border border-green-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-500 to-green-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <CheckCircle class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white">{{ result?.title }}</h4>
                            <p class="text-white/80 text-sm">{{ result?.message }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-white text-xs font-bold uppercase tracking-wider">Lunas</span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Payment Info Banner -->
                <div v-if="result?.booking?.payment"
                    class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <CheckCircle class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-green-800">Pembayaran Dikonfirmasi</p>
                                <p class="text-xs text-green-600">
                                    {{ result?.booking?.payment?.type_label }} •
                                    {{ result?.booking?.payment?.paid_datetime }}
                                </p>
                            </div>
                        </div>
                        <p class="font-bold text-green-800">{{ result?.booking?.payment?.amount }}</p>
                    </div>
                </div>

                <!-- Ticket & Booking Details -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Kode Tiket</p>
                        <p class="font-bold text-gray-900 font-mono">{{ result?.ticket?.code }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Order Number</p>
                        <p class="font-bold text-gray-900 font-mono">{{ result?.booking?.order_number }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama</p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.customer_name }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Destinasi</p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.destination }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal</p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.visit_day }}, {{ result?.booking?.visit_date }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Pengunjung</p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.total_visitors }} Orang</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button 
                        @click="emit('validateEntry')" 
                        :disabled="loading"
                        class="flex-1 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/30 disabled:opacity-50">
                        <LogIn class="w-5 h-5" />
                        Validasi Masuk
                    </button>
                    <button 
                        @click="emit('resetResult')"
                        class="px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition-all">
                        Scan Lagi
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
