<script setup>
/**
 * ScanResultCards.vue - Premium Result Cards Component
 * Matching Newsletter design system
 */
import { Banknote, CheckCircle, User, MapPin, Calendar, Users, Phone, CreditCard, LogIn, QrCode, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    result: Object,
    loading: Boolean
});

const emit = defineEmits(['processPayment', 'validateEntry', 'resetResult']);
</script>

<template>
    <!-- Empty State -->
    <div v-show="!result && !loading" class="content-card rounded-xl bg-white p-12 shadow-lg border border-gray-100 text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-6 border-2 border-dashed border-gray-300">
            <QrCode class="w-12 h-12 text-gray-400" />
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Siap untuk Scan</h3>
        <p class="text-gray-500 max-w-sm mx-auto text-xs leading-relaxed">
            Scan QR Code pada tiket atau masukkan kode secara manual untuk memulai proses pembayaran atau validasi masuk.
        </p>
    </div>

    <!-- Result Card: Payment Required -->
    <Transition name="fade">
        <div v-if="result?.status === 'payment_required'"
            class="content-card rounded-xl bg-white shadow-xl border border-amber-200 overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-amber-500 to-orange-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <Banknote class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">{{ result?.title }}</h4>
                            <p class="text-white/80 text-[10px]">Silakan konfirmasi pembayaran tunai</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-white/20 rounded-full text-white text-[9px] font-bold uppercase tracking-wider">Belum Bayar</span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Amount Display -->
                <div class="text-center py-4 mb-5 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl border border-amber-100">
                    <p class="text-[10px] text-amber-700 font-medium mb-1">Total Tagihan</p>
                    <h2 class="text-xl font-black text-gray-900">{{ result?.booking?.formatted_amount }}</h2>
                </div>

                <!-- Booking Details Grid -->
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <CreditCard class="w-3 h-3" /> Nomor Order
                        </p>
                        <p class="font-bold text-gray-900 font-mono text-xs">{{ result?.booking?.order_number }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <User class="w-3 h-3" /> Nama Pemesan
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.customer_name }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <MapPin class="w-3 h-3" /> Destinasi
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.destination }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Calendar class="w-3 h-3" /> Tanggal Kunjungan
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.visit_day }}, {{ result?.booking?.visit_date }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Users class="w-3 h-3" /> Jumlah Pengunjung
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.total_visitors }} Orang</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Phone class="w-3 h-3" /> Telepon
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.customer_phone || '-' }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button 
                        @click="emit('processPayment')" 
                        :disabled="loading"
                        class="flex-1 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/30 disabled:opacity-50 text-xs">
                        <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                        <CheckCircle v-else class="w-4 h-4" />
                        Konfirmasi Pembayaran Tunai
                    </button>
                    <button 
                        @click="emit('resetResult')"
                        class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition-all text-xs">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Result Card: Ready for Entry (Valid Ticket) -->
    <Transition name="fade">
        <div v-if="result?.status === 'ready_for_entry'"
            class="content-card rounded-xl bg-white shadow-xl border border-green-200 overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-emerald-500 to-teal-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <CheckCircle class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">{{ result?.title }}</h4>
                            <p class="text-white/80 text-[10px]">{{ result?.message }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-white/20 rounded-full text-white text-[9px] font-bold uppercase tracking-wider">Lunas</span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Payment Info Banner -->
                <div v-if="result?.booking?.payment"
                    class="mb-5 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <CheckCircle class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-green-800">Pembayaran Dikonfirmasi</p>
                                <p class="text-[10px] text-green-600">
                                    {{ result?.booking?.payment?.type_label }} •
                                    {{ result?.booking?.payment?.paid_datetime }}
                                </p>
                            </div>
                        </div>
                        <p class="font-bold text-green-800 text-sm">{{ result?.booking?.payment?.amount }}</p>
                    </div>
                </div>

                <!-- Ticket & Booking Details -->
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1">Kode Tiket</p>
                        <p class="font-bold text-gray-900 font-mono text-xs">{{ result?.ticket?.code }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1">Order Number</p>
                        <p class="font-bold text-gray-900 font-mono text-xs">{{ result?.booking?.order_number }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1">Nama</p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.customer_name }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1">Destinasi</p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.destination }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1">Tanggal</p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.visit_day }}, {{ result?.booking?.visit_date }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1">Pengunjung</p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.total_visitors }} Orang</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button 
                        @click="emit('validateEntry')" 
                        :disabled="loading"
                        class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/30 disabled:opacity-50 text-xs">
                        <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                        <LogIn v-else class="w-5 h-5" />
                        Validasi Masuk
                    </button>
                    <button 
                        @click="emit('resetResult')"
                        class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl font-bold transition-all text-xs">
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
