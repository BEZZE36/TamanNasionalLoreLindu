<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { QrCode, ArrowLeft, RotateCcw, Keyboard, CheckCircle, XCircle, AlertTriangle, Clock, MapPin, Calendar, User, Ticket, CreditCard, ScanLine } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const manualCode = ref('');
const state = ref('idle'); // idle, loading, valid, used, invalid, expired, cancelled, not_found
const message = ref('');
const ticketData = ref(null);
const bookingData = ref(null);

const getStateTitle = () => {
    const titles = {
        idle: 'Siap Scan', loading: 'Memproses...', valid: 'Tiket Valid',
        used: 'Sudah Digunakan', used_success: 'Check-in Berhasil',
        invalid: 'Tiket Invalid', expired: 'Tiket Expired',
        cancelled: 'Tiket Dibatalkan', not_found: 'Tiket Tidak Ditemukan'
    };
    return titles[state.value] || 'Unknown';
};

const getStateIcon = () => {
    if (['valid', 'used_success'].includes(state.value)) return CheckCircle;
    if (['used', 'expired'].includes(state.value)) return Clock;
    if (['invalid', 'cancelled', 'not_found'].includes(state.value)) return XCircle;
    return QrCode;
};

const getStateColor = () => {
    if (['valid', 'used_success'].includes(state.value)) return 'text-green-600';
    if (state.value === 'used') return 'text-blue-600';
    if (['invalid', 'cancelled', 'not_found', 'expired'].includes(state.value)) return 'text-red-600';
    return 'text-gray-400';
};

const getStateBgColor = () => {
    if (['valid', 'used_success'].includes(state.value)) return 'bg-gradient-to-br from-green-50 to-emerald-100';
    if (state.value === 'used') return 'bg-gradient-to-br from-blue-50 to-indigo-100';
    if (['invalid', 'cancelled', 'not_found', 'expired'].includes(state.value)) return 'bg-gradient-to-br from-red-50 to-orange-100';
    return 'bg-gradient-to-br from-gray-50 to-gray-100';
};

const checkTicket = async (code) => {
    if (!code?.trim()) return;
    state.value = 'loading';
    message.value = 'Memeriksa tiket...';
    
    try {
        const res = await fetch('/admin/tickets/check', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify({ ticket_code: code.trim() })
        });
        const data = await res.json();
        
        if (data.success) {
            state.value = 'valid';
            message.value = data.message || 'Tiket siap digunakan';
            ticketData.value = data.ticket;
            bookingData.value = data.booking || data.ticket?.booking;
        } else {
            state.value = data.reason || 'invalid';
            message.value = data.message || 'Tiket tidak valid';
            ticketData.value = data.ticket;
            bookingData.value = data.booking || data.ticket?.booking;
        }
    } catch (err) {
        state.value = 'invalid';
        message.value = 'Terjadi kesalahan saat memeriksa tiket';
    }
};

const redeemTicket = async () => {
    if (!ticketData.value?.ticket_code) return;
    state.value = 'loading';
    message.value = 'Memproses check-in...';
    
    try {
        const res = await fetch('/admin/tickets/redeem', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify({ ticket_code: ticketData.value.ticket_code })
        });
        const data = await res.json();
        
        if (data.success) {
            state.value = 'used_success';
            message.value = data.message || 'Tiket berhasil digunakan!';
            ticketData.value = data.ticket;
        } else {
            state.value = 'invalid';
            message.value = data.message || 'Gagal memproses check-in';
        }
    } catch (err) {
        state.value = 'invalid';
        message.value = 'Terjadi kesalahan saat check-in';
    }
};

const resetScanner = () => {
    state.value = 'idle';
    message.value = '';
    ticketData.value = null;
    bookingData.value = null;
    manualCode.value = '';
};

const submitManual = () => checkTicket(manualCode.value);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link href="/admin/tickets" class="p-2 rounded-xl bg-teal-100 hover:bg-teal-200 transition-colors">
                    <ArrowLeft class="w-5 h-5 text-teal-600" />
                </Link>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <QrCode class="w-7 h-7 text-teal-600" />Validasi Tiket
                    </h1>
                    <p class="text-gray-500 text-sm">Scan QR Code atau input manual kode tiket</p>
                </div>
            </div>
            <button @click="resetScanner" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition flex items-center gap-2">
                <RotateCcw class="w-5 h-5" />Reset Scanner
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
            <!-- Left: Manual Input -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Scanner Placeholder -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><QrCode class="w-5 h-5 text-teal-600" />QR Scanner</h3>
                    <div class="aspect-square bg-gray-900 rounded-xl flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-4 border-2 border-teal-400 rounded-lg opacity-50"></div>
                        <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-teal-400 animate-pulse"></div>
                        <ScanLine class="w-16 h-16 text-teal-400 opacity-50" />
                    </div>
                    <p class="text-xs text-gray-500 text-center mt-3">QR Scanner aktif - arahkan kamera ke tiket</p>
                </div>

                <!-- Manual Input -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><Keyboard class="w-5 h-5 text-teal-600" />Input Manual</h3>
                    <form @submit.prevent="submitManual" class="space-y-4">
                        <input v-model="manualCode" type="text" placeholder="Masukkan kode tiket..."
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 font-mono text-lg uppercase">
                        <button type="submit" :disabled="!manualCode.trim() || state === 'loading'"
                            class="w-full py-3 bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 transition-all disabled:opacity-50">
                            Cek Tiket
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Result -->
            <div class="lg:col-span-7">
                <div :class="['rounded-2xl shadow-lg border border-gray-100 min-h-[500px] flex flex-col overflow-hidden transition-all duration-500', getStateBgColor()]">
                    <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                        
                        <!-- Idle State -->
                        <div v-if="state === 'idle'" class="text-center animate-pulse">
                            <QrCode class="w-24 h-24 mx-auto text-gray-300 mb-6" />
                            <h2 class="text-2xl font-bold text-gray-400">Menunggu Scan</h2>
                            <p class="text-gray-400 mt-2">Scan QR Code tiket atau input kode manual</p>
                        </div>

                        <!-- Loading State -->
                        <div v-else-if="state === 'loading'" class="text-center">
                            <div class="w-16 h-16 border-4 border-teal-500 border-t-transparent rounded-full animate-spin mx-auto mb-6"></div>
                            <h2 class="text-xl font-bold text-gray-600">{{ message }}</h2>
                        </div>

                        <!-- Result State -->
                        <div v-else class="w-full max-w-md">
                            <div class="mb-8">
                                <component :is="getStateIcon()" :class="['w-20 h-20 mx-auto', getStateColor()]" />
                                <h2 :class="['text-xl font-bold mt-6 uppercase tracking-tight', getStateColor()]">{{ getStateTitle() }}</h2>
                                <p class="text-lg font-medium mt-2 text-gray-600">{{ message }}</p>
                            </div>

                            <!-- Check-in Button -->
                            <button v-if="state === 'valid'" @click="redeemTicket"
                                class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-black text-xl rounded-xl shadow-lg shadow-green-500/30 hover:shadow-green-500/50 hover:scale-[1.02] transition-all mb-6 flex items-center justify-center gap-3">
                                <CheckCircle class="w-7 h-7" />CHECK-IN SEKARANG
                            </button>

                            <!-- Ticket Details -->
                            <div v-if="ticketData || bookingData" class="bg-white/80 backdrop-blur rounded-xl p-4 text-left space-y-3 text-sm border border-gray-200">
                                <div v-if="ticketData?.ticket_code" class="flex items-center gap-2">
                                    <Ticket class="w-4 h-4 text-gray-400" />
                                    <span class="font-mono font-bold text-teal-600">{{ ticketData.ticket_code }}</span>
                                </div>
                                <div v-if="bookingData?.destination?.name" class="flex items-center gap-2">
                                    <MapPin class="w-4 h-4 text-gray-400" />
                                    <span>{{ bookingData.destination.name }}</span>
                                </div>
                                <div v-if="ticketData?.valid_date" class="flex items-center gap-2">
                                    <Calendar class="w-4 h-4 text-gray-400" />
                                    <span>{{ formatDate(ticketData.valid_date) }}</span>
                                </div>
                                <div v-if="bookingData?.leader_name" class="flex items-center gap-2">
                                    <User class="w-4 h-4 text-gray-400" />
                                    <span>{{ bookingData.leader_name }}</span>
                                </div>
                            </div>

                            <button @click="resetScanner" class="mt-6 px-6 py-2 bg-white/50 text-gray-600 font-medium rounded-xl hover:bg-white transition-colors">
                                Scan Tiket Berikutnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
