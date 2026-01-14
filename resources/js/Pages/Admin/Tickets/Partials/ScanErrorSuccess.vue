<script setup>
/**
 * ScanErrorSuccess.vue - Premium Error/Success States Component
 * Matching Newsletter design system
 */
import { AlertCircle, Clock, CheckCircle, RotateCcw, Ticket, User, MapPin, Calendar, XCircle } from 'lucide-vue-next';

const props = defineProps({
    result: Object,
    successMessage: String
});

const emit = defineEmits(['resetResult']);

const errorStatuses = ['not_found', 'already_used', 'expired', 'not_yet_valid'];

const getHeaderConfig = (status) => {
    if (['not_found', 'expired'].includes(status)) return { bg: 'from-red-500 to-rose-600', icon: XCircle, border: 'border-red-200' };
    if (status === 'already_used') return { bg: 'from-blue-500 to-indigo-600', icon: Clock, border: 'border-blue-200' };
    if (status === 'not_yet_valid') return { bg: 'from-amber-500 to-orange-500', icon: Clock, border: 'border-amber-200' };
    return { bg: 'from-gray-500 to-gray-600', icon: AlertCircle, border: 'border-gray-200' };
};
</script>

<template>
    <!-- Result Card: Error States -->
    <Transition name="fade">
        <div v-if="result && errorStatuses.includes(result?.status)"
            :class="['content-card rounded-xl bg-white shadow-xl border overflow-hidden', getHeaderConfig(result?.status).border]">
            <!-- Header -->
            <div :class="['px-5 py-4 bg-gradient-to-r', getHeaderConfig(result?.status).bg]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <component :is="getHeaderConfig(result?.status).icon" class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">{{ result?.title }}</h4>
                        <p class="text-white/80 text-[10px]">{{ result?.message }}</p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Used At Info -->
                <div v-if="result?.status === 'already_used' && result?.ticket?.used_at"
                    class="mb-5 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <Clock class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-blue-800">Waktu Penggunaan</p>
                            <p class="text-[10px] text-blue-600">{{ result?.ticket?.used_at }}</p>
                        </div>
                    </div>
                </div>

                <!-- Ticket Info if available -->
                <div v-if="result?.ticket || result?.booking" class="grid grid-cols-2 gap-3 mb-5">
                    <div v-if="result?.ticket?.code" class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Ticket class="w-3 h-3" /> Kode Tiket
                        </p>
                        <p class="font-bold text-gray-900 font-mono text-xs">{{ result?.ticket?.code }}</p>
                    </div>
                    <div v-if="result?.booking?.customer_name" class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <User class="w-3 h-3" /> Nama
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.customer_name }}</p>
                    </div>
                    <div v-if="result?.booking?.destination" class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <MapPin class="w-3 h-3" /> Destinasi
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.booking?.destination }}</p>
                    </div>
                    <div v-if="result?.ticket?.valid_date" class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Calendar class="w-3 h-3" /> Tanggal Berlaku
                        </p>
                        <p class="font-bold text-gray-900 text-xs">{{ result?.ticket?.valid_date }}</p>
                    </div>
                </div>

                <button 
                    @click="emit('resetResult')"
                    class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold transition-all flex items-center justify-center gap-2 text-xs">
                    <RotateCcw class="w-4 h-4" />
                    Scan Berikutnya
                </button>
            </div>
        </div>
    </Transition>

    <!-- Success State (After Payment or Entry) -->
    <Transition name="bounce">
        <div v-if="successMessage" class="content-card rounded-xl bg-white shadow-xl border border-green-200 overflow-hidden">
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/30 animate-bounce-slow">
                    <CheckCircle class="w-12 h-12 text-white" />
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">{{ successMessage }}</h3>
                <p class="text-gray-500 mb-6 text-xs">Transaksi berhasil diproses</p>
                <button 
                    @click="emit('resetResult')"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all text-xs">
                    Scan Berikutnya
                </button>
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

.bounce-enter-active {
    animation: bounce-in 0.5s;
}
.bounce-leave-active {
    animation: bounce-in 0.3s reverse;
}
@keyframes bounce-in {
    0% { transform: scale(0.9); opacity: 0; }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); opacity: 1; }
}

.animate-bounce-slow {
    animation: bounce 1s ease-in-out 3;
}
</style>
