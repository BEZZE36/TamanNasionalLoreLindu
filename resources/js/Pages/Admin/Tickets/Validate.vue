<script setup>
/**
 * Validate.vue - Simplified Ticket Validation
 * Only 2 functions:
 * 1. Cash payment confirmation (for unpaid orders)
 * 2. Auto check-in (for valid tickets)
 */
import { ref, onMounted, onUnmounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    QrCode, ArrowLeft, RotateCcw, Keyboard, CheckCircle, 
    Wallet, MapPin, Calendar, User, Ticket, CreditCard, Loader2,
    Camera, X, Sparkles
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const manualCode = ref('');
const state = ref('idle'); // idle, loading, payment_required, success, error
const message = ref('');
const bookingData = ref(null);
const ticketData = ref(null);
let ctx;

// Camera scanner state
const scanning = ref(false);
const cameraReady = ref(false);
const cameras = ref([]);
const selectedCamera = ref(null);
const scanner = ref(null);

// Load Html5Qrcode library
const loadQrCodeLibrary = () => {
    return new Promise((resolve, reject) => {
        if (typeof Html5Qrcode !== 'undefined') {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
};

// Load cameras
const loadCameras = async () => {
    let attempts = 0;
    const checkLibrary = setInterval(async () => {
        attempts++;
        if (typeof Html5Qrcode !== 'undefined') {
            cameraReady.value = true;
            clearInterval(checkLibrary);
            try {
                const devices = await Html5Qrcode.getCameras();
                cameras.value = devices;
                if (devices.length > 0) {
                    const saved = localStorage.getItem('selectedCamera');
                    selectedCamera.value = saved && devices.find(d => d.deviceId === saved)
                        ? saved : devices[0].deviceId;
                }
            } catch (e) {
                console.error('Failed to get cameras:', e);
            }
        } else if (attempts > 20) {
            clearInterval(checkLibrary);
        }
    }, 100);
};

const toggleScanner = async () => {
    if (scanning.value) {
        await stopScanner();
    } else {
        await startScanner();
    }
};

const startScanner = async () => {
    if (!cameraReady.value || !selectedCamera.value) {
        window.showToast?.('Kamera belum siap. Pastikan izin kamera diberikan', 'warning');
        return;
    }

    resetResult();

    try {
        scanner.value = new Html5Qrcode("scanner-video");
        await scanner.value.start(
            selectedCamera.value,
            { fps: 10, qrbox: { width: 200, height: 200 } },
            (code) => onScanSuccess(code),
            () => {}
        );
        scanning.value = true;
    } catch (e) {
        console.error('Scanner start error:', e);
        window.showToast?.('Gagal mengakses kamera. Kamera memerlukan HTTPS atau localhost.', 'warning');
    }
};

const stopScanner = async () => {
    if (scanner.value) {
        try {
            await scanner.value.stop();
            await scanner.value.clear();
        } catch (e) {}
    }
    scanning.value = false;
};

const switchCamera = async () => {
    localStorage.setItem('selectedCamera', selectedCamera.value);
    if (scanning.value) {
        await stopScanner();
        await startScanner();
    }
};

const onScanSuccess = async (code) => {
    await stopScanner();
    manualCode.value = code;
    await processCode(code);
};

// Main processing function - simplified logic
const processCode = async (code) => {
    if (!code?.trim()) return;
    
    state.value = 'loading';
    message.value = 'Memproses...';
    
    try {
        const res = await fetch('/admin/tickets/validate', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ticket_code: code.trim() })
        });
        const data = await res.json();
        
        // SIMPLE LOGIC:
        // 1. If payment_required → show payment button
        // 2. If ticket is ready → auto check-in immediately
        // 3. Everything else → show error
        
        if (data.status === 'payment_required') {
            state.value = 'payment_required';
            message.value = 'Silakan konfirmasi pembayaran tunai';
            bookingData.value = data.booking;
        } else if (data.valid || data.status === 'ready' || data.status === 'ready_for_entry') {
            // Auto check-in for valid tickets
            await autoCheckIn(data.ticket?.code || data.ticket?.ticket_code || code);
        } else if (data.status === 'already_used') {
            state.value = 'success';
            message.value = 'Tiket sudah digunakan sebelumnya';
            ticketData.value = data.ticket;
            bookingData.value = data.booking;
        } else {
            state.value = 'error';
            message.value = data.message || 'Kode tidak valid atau tidak ditemukan';
        }
    } catch (err) {
        console.error('Process error:', err);
        state.value = 'error';
        message.value = 'Terjadi kesalahan. Silakan coba lagi.';
    }
};

// Auto check-in function
const autoCheckIn = async (ticketCode) => {
    if (!ticketCode) {
        state.value = 'error';
        message.value = 'Kode tiket tidak ditemukan';
        return;
    }
    
    message.value = 'Memvalidasi tiket...';
    
    try {
        const res = await fetch('/admin/tickets/redeem', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ticket_code: ticketCode })
        });
        const data = await res.json();
        
        if (data.success) {
            state.value = 'success';
            message.value = 'Check-in Berhasil! ✓';
            ticketData.value = data.ticket;
        } else {
            state.value = 'error';
            message.value = data.message || 'Gagal check-in';
        }
    } catch (err) {
        state.value = 'error';
        message.value = 'Gagal check-in. Silakan coba lagi.';
    }
};

// Process cash payment
const processPayment = async () => {
    if (!bookingData.value?.id) return;
    
    state.value = 'loading';
    message.value = 'Memproses pembayaran...';
    
    try {
        const res = await fetch('/admin/tickets/process-payment', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ booking_id: bookingData.value.id })
        });
        const data = await res.json();
        
        if (data.success) {
            state.value = 'success';
            message.value = 'Pembayaran Berhasil! ✓';
            bookingData.value = data.booking;
        } else {
            state.value = 'error';
            message.value = data.message || 'Gagal memproses pembayaran';
        }
    } catch (err) {
        state.value = 'error';
        message.value = 'Gagal memproses pembayaran';
    }
};

const resetResult = () => {
    state.value = 'idle';
    message.value = '';
    bookingData.value = null;
    ticketData.value = null;
    manualCode.value = '';
};

const submitManual = () => processCode(manualCode.value);

// Initialize
onMounted(async () => {
    try {
        await loadQrCodeLibrary();
    } catch (e) {
        console.error('Failed to load Html5Qrcode library:', e);
    }
    loadCameras();

    ctx = gsap.context(() => {
        gsap.fromTo('.content-card', 
            { opacity: 0, y: 20, scale: 0.98 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );
    });
});

onUnmounted(() => { stopScanner(); });
onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Header -->
        <div class="content-card relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 via-emerald-600 to-cyan-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link href="/admin/tickets" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-lg hover:bg-white/30 transition-all">
                        <ArrowLeft class="h-5 w-5 text-white" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Kasir & Validasi</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm flex items-center gap-1">
                                <QrCode class="w-3 h-3" />
                                Scanner
                            </span>
                        </div>
                        <p class="text-teal-100/80 text-xs">Pembayaran tunai & check-in otomatis</p>
                    </div>
                </div>
                <button 
                    @click="resetResult" 
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/20 backdrop-blur-sm text-white text-xs font-bold hover:bg-white/30 transition-all ring-2 ring-white/30"
                >
                    <RotateCcw class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" />
                    Reset
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Scanner Section -->
            <div class="lg:col-span-5 space-y-5">
                <!-- Camera Scanner -->
                <div class="content-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-teal-500/30">
                                    <QrCode class="w-4 h-4 text-white" />
                                </div>
                                Scanner QR Code
                            </h3>
                            <select 
                                v-model="selectedCamera"
                                @change="switchCamera"
                                class="text-[10px] bg-white border border-gray-200 rounded-lg py-1.5 px-2 text-gray-600">
                                <option v-for="camera in cameras" :key="camera.deviceId" :value="camera.deviceId">
                                    {{ camera.label || 'Camera ' + (cameras.indexOf(camera) + 1) }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="relative aspect-square bg-gray-900">
                        <div id="scanner-video" class="w-full h-full"></div>
                        <div class="absolute inset-0 pointer-events-none">
                            <div v-show="scanning" class="absolute inset-0 flex items-center justify-center">
                                <div class="relative w-48 h-48">
                                    <div class="absolute inset-0 border-2 border-teal-500/30 rounded-2xl"></div>
                                    <div class="absolute top-0 left-0 w-10 h-10 border-t-4 border-l-4 border-teal-400 rounded-tl-2xl"></div>
                                    <div class="absolute top-0 right-0 w-10 h-10 border-t-4 border-r-4 border-teal-400 rounded-tr-2xl"></div>
                                    <div class="absolute bottom-0 left-0 w-10 h-10 border-b-4 border-l-4 border-teal-400 rounded-bl-2xl"></div>
                                    <div class="absolute bottom-0 right-0 w-10 h-10 border-b-4 border-r-4 border-teal-400 rounded-br-2xl"></div>
                                </div>
                            </div>
                            <div v-show="!scanning" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900/80 backdrop-blur-sm">
                                <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mb-4 border border-white/20">
                                    <Camera class="w-10 h-10 text-white/60" />
                                </div>
                                <p class="text-white/70 text-xs">Klik tombol untuk aktifkan kamera</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <button 
                            @click="toggleScanner" 
                            :disabled="!cameraReady"
                            :class="[
                                'w-full py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2 text-xs',
                                scanning 
                                    ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-lg' 
                                    : 'bg-gradient-to-r from-teal-500 to-emerald-600 text-white shadow-lg disabled:opacity-50'
                            ]">
                            <component :is="scanning ? X : Camera" class="w-4 h-4" />
                            {{ scanning ? 'Matikan Kamera' : (cameraReady ? 'Aktifkan Kamera' : 'Memuat...') }}
                        </button>
                    </div>
                </div>

                <!-- Manual Input -->
                <div class="content-card rounded-xl bg-white p-5 shadow-lg border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                            <Keyboard class="w-4 h-4 text-white" />
                        </div>
                        Input Manual
                    </h3>
                    <form @submit.prevent="submitManual" class="space-y-3">
                        <input 
                            v-model="manualCode" 
                            type="text" 
                            placeholder="Kode Order (TNLL-...) atau Tiket (TKT-...)"
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-teal-500 font-mono text-sm uppercase transition-all"
                        />
                        <button 
                            type="submit" 
                            :disabled="!manualCode.trim() || state === 'loading'"
                            class="w-full py-3 bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg disabled:opacity-50 text-xs flex items-center justify-center gap-2"
                        >
                            <Loader2 v-if="state === 'loading'" class="w-4 h-4 animate-spin" />
                            <QrCode v-else class="w-4 h-4" />
                            Proses
                        </button>
                    </form>
                </div>
            </div>

            <!-- Result Section -->
            <div class="lg:col-span-7">
                <div :class="[
                    'content-card rounded-xl shadow-lg border border-gray-100 min-h-[500px] flex flex-col overflow-hidden transition-all bg-gradient-to-br',
                    state === 'idle' ? 'from-gray-50 to-gray-100' : '',
                    state === 'loading' ? 'from-blue-50 to-indigo-100' : '',
                    state === 'payment_required' ? 'from-amber-50 to-orange-100' : '',
                    state === 'success' ? 'from-emerald-50 to-teal-100' : '',
                    state === 'error' ? 'from-red-50 to-rose-100' : ''
                ]">
                    <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                        
                        <!-- Idle State -->
                        <div v-if="state === 'idle'" class="text-center animate-pulse">
                            <div class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                <QrCode class="w-12 h-12 text-gray-400" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-400 mb-2">Menunggu Scan</h2>
                            <p class="text-gray-400 text-sm">Scan QR Code atau input kode manual</p>
                        </div>

                        <!-- Loading State -->
                        <div v-else-if="state === 'loading'" class="text-center">
                            <div class="w-16 h-16 border-4 border-teal-500 border-t-transparent rounded-full animate-spin mx-auto mb-6"></div>
                            <h2 class="text-lg font-bold text-gray-600">{{ message }}</h2>
                        </div>

                        <!-- Payment Required State -->
                        <div v-else-if="state === 'payment_required'" class="w-full max-w-md">
                            <div class="mb-8">
                                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                                    <Wallet class="w-10 h-10 text-white" />
                                </div>
                                <h2 class="text-xl font-bold text-amber-600 uppercase">Menunggu Pembayaran</h2>
                                <p class="text-sm font-medium mt-2 text-gray-600">{{ message }}</p>
                            </div>

                            <!-- Booking Details -->
                            <div v-if="bookingData" class="bg-white/80 backdrop-blur rounded-xl p-4 text-left space-y-3 text-xs border border-amber-200 mb-6">
                                <div class="flex items-center gap-2">
                                    <Ticket class="w-4 h-4 text-amber-500" />
                                    <span class="font-mono font-bold text-amber-600">{{ bookingData.order_number }}</span>
                                </div>
                                <div v-if="bookingData.destination" class="flex items-center gap-2">
                                    <MapPin class="w-4 h-4 text-gray-400" />
                                    <span>{{ typeof bookingData.destination === 'object' ? bookingData.destination.name : bookingData.destination }}</span>
                                </div>
                                <div v-if="bookingData.visit_date" class="flex items-center gap-2">
                                    <Calendar class="w-4 h-4 text-gray-400" />
                                    <span>{{ bookingData.visit_date }}</span>
                                </div>
                                <div v-if="bookingData.customer_name" class="flex items-center gap-2">
                                    <User class="w-4 h-4 text-gray-400" />
                                    <span>{{ bookingData.customer_name }}</span>
                                </div>
                                <div class="flex items-center gap-2 pt-3 mt-3 border-t border-amber-200">
                                    <CreditCard class="w-5 h-5 text-amber-500" />
                                    <span class="font-black text-lg text-amber-600">{{ bookingData.formatted_amount || 'Rp ' + Number(bookingData.total_amount || 0).toLocaleString('id-ID') }}</span>
                                </div>
                            </div>

                            <!-- Payment Button -->
                            <button 
                                @click="processPayment"
                                class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-black text-sm rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3"
                            >
                                <Wallet class="w-6 h-6" />
                                KONFIRMASI PEMBAYARAN TUNAI
                            </button>

                            <button @click="resetResult" class="mt-4 px-6 py-2 bg-white/50 text-gray-600 font-medium rounded-xl hover:bg-white text-xs">
                                Scan Berikutnya
                            </button>
                        </div>

                        <!-- Success State -->
                        <div v-else-if="state === 'success'" class="w-full max-w-md">
                            <!-- Success Animation -->
                            <div class="mb-8 relative">
                                <!-- Confetti effect -->
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div class="absolute -top-4 -left-4 w-3 h-3 bg-amber-400 rounded-full animate-ping"></div>
                                    <div class="absolute -top-6 right-8 w-2 h-2 bg-emerald-400 rounded-full animate-ping" style="animation-delay: 0.2s"></div>
                                    <div class="absolute top-0 -right-4 w-3 h-3 bg-teal-400 rounded-full animate-ping" style="animation-delay: 0.4s"></div>
                                    <div class="absolute -bottom-2 left-6 w-2 h-2 bg-cyan-400 rounded-full animate-ping" style="animation-delay: 0.6s"></div>
                                </div>
                                
                                <div class="w-28 h-28 mx-auto mb-6 rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 flex items-center justify-center shadow-2xl shadow-emerald-500/40 animate-bounce relative">
                                    <div class="absolute inset-0 rounded-3xl bg-white/20 animate-pulse"></div>
                                    <CheckCircle class="w-14 h-14 text-white relative z-10" />
                                </div>
                                
                                <h2 class="text-2xl font-black text-emerald-600 uppercase tracking-tight">{{ message }}</h2>
                                
                                <div class="mt-4 flex justify-center gap-3">
                                    <Sparkles class="w-6 h-6 text-amber-400 animate-pulse" />
                                    <Sparkles class="w-7 h-7 text-amber-500 animate-pulse" style="animation-delay: 0.2s" />
                                    <Sparkles class="w-6 h-6 text-amber-400 animate-pulse" style="animation-delay: 0.4s" />
                                </div>
                                
                                <p class="mt-4 text-gray-500 text-sm">Pengunjung dapat memasuki area wisata</p>
                            </div>

                            <!-- Ticket/Booking Info (only show if has data to display) -->
                            <div v-if="(ticketData?.code) || (bookingData?.order_number && !ticketData)" 
                                 class="bg-white/90 backdrop-blur rounded-2xl p-5 text-center mb-6 border-2 border-emerald-200 shadow-lg">
                                <div v-if="ticketData?.code" class="flex flex-col items-center gap-1">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Kode Tiket</span>
                                    <span class="font-mono font-black text-lg text-emerald-600">{{ ticketData.code }}</span>
                                </div>
                                <div v-else-if="bookingData?.order_number" class="flex flex-col items-center gap-1">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Order Number</span>
                                    <span class="font-mono font-bold text-teal-600">{{ bookingData.order_number }}</span>
                                </div>
                            </div>

                            <button 
                                @click="resetResult" 
                                class="w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-sm rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2"
                            >
                                <QrCode class="w-5 h-5" />
                                Scan Berikutnya
                            </button>
                        </div>

                        <!-- Error State -->
                        <div v-else-if="state === 'error'" class="w-full max-w-md">
                            <div class="mb-8">
                                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg">
                                    <X class="w-10 h-10 text-white" />
                                </div>
                                <h2 class="text-xl font-bold text-red-600">{{ message }}</h2>
                            </div>
                            <button @click="resetResult" class="px-6 py-3 bg-white text-gray-600 font-medium rounded-xl hover:bg-gray-100 text-xs">
                                Coba Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes scan {
    0% { top: 0; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
</style>
