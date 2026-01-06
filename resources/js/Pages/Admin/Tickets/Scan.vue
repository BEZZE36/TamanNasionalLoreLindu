<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ScanHeader from './Partials/ScanHeader.vue';
import ScanScanner from './Partials/ScanScanner.vue';
import ScanResultCards from './Partials/ScanResultCards.vue';
import ScanErrorSuccess from './Partials/ScanErrorSuccess.vue';
import ScanTransactions from './Partials/ScanTransactions.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    dbConnected: Boolean,
    ticketCount: Number,
    todayStats: Object,
    recentTransactions: Array
});

const page = usePage();
const adminName = page.props.auth?.admin?.name || 'Admin';

// Scanner state
const scanning = ref(false);
const loading = ref(false);
const result = ref(null);
const successMessage = ref(null);
const manualCode = ref('');
const scanner = ref(null);
const cameraReady = ref(false);
const cameras = ref([]);
const selectedCamera = ref(null);
const recentSearches = ref([]);

// Load Html5Qrcode library dynamically
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

// Load recent searches from localStorage
onMounted(async () => {
    try {
        recentSearches.value = JSON.parse(localStorage.getItem('recentScans') || '[]');
    } catch (e) {
        recentSearches.value = [];
    }
    
    // Load the QR library first
    try {
        await loadQrCodeLibrary();
    } catch (e) {
        console.error('Failed to load Html5Qrcode library:', e);
    }
    
    loadCameras();
});

onUnmounted(() => {
    stopScanner();
});

// Camera functions
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

    result.value = null;
    successMessage.value = null;

    try {
        scanner.value = new Html5Qrcode("scanner-video");
        await scanner.value.start(
            selectedCamera.value,
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (code) => onScanSuccess(code),
            () => { }
        );
        scanning.value = true;
    } catch (e) {
        console.error('Scanner start error:', e);
        let msg = 'Gagal mengakses kamera.';
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            msg += ' Kamera memerlukan koneksi HTTPS.';
        }
        window.showToast?.(msg, 'warning');
    }
};

const stopScanner = async () => {
    if (scanner.value) {
        try {
            await scanner.value.stop();
            await scanner.value.clear();
        } catch (e) { }
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
    await validateCode();
};

// Validation and processing
const validateCode = async () => {
    const code = manualCode.value.trim();
    if (!code) return;

    loading.value = true;
    result.value = null;
    successMessage.value = null;

    // Save to recent searches
    if (!recentSearches.value.includes(code)) {
        recentSearches.value.unshift(code);
        recentSearches.value = recentSearches.value.slice(0, 10);
        localStorage.setItem('recentScans', JSON.stringify(recentSearches.value));
    }

    try {
        const response = await fetch('/admin/tickets/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ticket_code: code })
        });
        result.value = await response.json();
    } catch (e) {
        console.error('Validation error:', e);
        result.value = { valid: false, status: 'not_found', title: 'Error', message: 'Gagal menghubungi server.' };
    }

    loading.value = false;
};

const processPayment = async () => {
    if (!result.value?.booking?.id) return;

    loading.value = true;
    try {
        const response = await fetch('/admin/tickets/process-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ booking_id: result.value.booking.id })
        });
        const data = await response.json();
        if (data.success) {
            result.value = null;
            successMessage.value = 'Pembayaran Berhasil!';
        } else {
            window.showToast?.(data.message || 'Gagal memproses pembayaran', 'error');
        }
    } catch (e) {
        console.error('Payment error:', e);
        window.showToast?.('Gagal menghubungi server', 'error');
    }
    loading.value = false;
};

const validateEntry = async () => {
    if (!result.value?.ticket?.id) return;

    loading.value = true;
    try {
        const response = await fetch('/admin/tickets/validate-entry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ticket_id: result.value.ticket.id })
        });
        const data = await response.json();
        if (data.success) {
            result.value = null;
            successMessage.value = 'Validasi Masuk Berhasil!';
        } else {
            window.showToast?.(data.message || 'Gagal memvalidasi tiket', 'error');
        }
    } catch (e) {
        console.error('Entry error:', e);
        window.showToast?.('Gagal menghubungi server', 'error');
    }
    loading.value = false;
};

const resetResult = () => {
    result.value = null;
    manualCode.value = '';
    successMessage.value = null;
};

const quickSearch = (code) => {
    manualCode.value = code;
    validateCode();
};
</script>

<template>
    <div class="min-h-screen">
        <ScanHeader 
            :todayStats="todayStats"
            :dbConnected="dbConnected"
            :ticketCount="ticketCount"
            :cameraReady="cameraReady"
        />

        <!-- Main Content Grid -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-8">
            <!-- Left Column: Scanner -->
            <div class="lg:col-span-2 space-y-6">
                <ScanScanner
                    :scanning="scanning"
                    :loading="loading"
                    :cameraReady="cameraReady"
                    :cameras="cameras"
                    :selectedCamera="selectedCamera"
                    :recentSearches="recentSearches"
                    :manualCode="manualCode"
                    @update:selectedCamera="selectedCamera = $event"
                    @update:manualCode="manualCode = $event"
                    @toggleScanner="toggleScanner"
                    @switchCamera="switchCamera"
                    @validateCode="validateCode"
                    @quickSearch="quickSearch"
                />
            </div>

            <!-- Right Column: Result & Details -->
            <div class="lg:col-span-3 space-y-6">
                <ScanResultCards
                    :result="result"
                    :loading="loading"
                    @processPayment="processPayment"
                    @validateEntry="validateEntry"
                    @resetResult="resetResult"
                />
                <ScanErrorSuccess
                    :result="result"
                    :successMessage="successMessage"
                    @resetResult="resetResult"
                />
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="max-w-7xl mx-auto">
            <ScanTransactions
                :transactions="recentTransactions"
                :adminName="adminName"
            />
        </div>
    </div>
</template>
