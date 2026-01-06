<script setup>
import { AlertCircle, Clock, CheckCircle, RotateCcw, Ticket, User, MapPin, Calendar } from 'lucide-vue-next';

const props = defineProps({
    result: Object,
    successMessage: String
});

const emit = defineEmits(['resetResult']);

const errorStatuses = ['not_found', 'already_used', 'expired', 'not_yet_valid'];

const getHeaderClasses = (status) => {
    if (['not_found', 'expired'].includes(status)) return 'bg-gradient-to-r from-red-500 to-rose-600';
    if (status === 'already_used') return 'bg-gradient-to-r from-blue-500 to-indigo-600';
    if (status === 'not_yet_valid') return 'bg-gradient-to-r from-amber-500 to-orange-500';
    return 'bg-gradient-to-r from-gray-500 to-gray-600';
};

const getBorderClasses = (status) => {
    if (['not_found', 'expired'].includes(status)) return 'border-red-200';
    if (status === 'already_used') return 'border-blue-200';
    if (status === 'not_yet_valid') return 'border-amber-200';
    return 'border-gray-200';
};
</script>

<template>
    <!-- Result Card: Error States -->
    <Transition name="fade">
        <div v-if="result && errorStatuses.includes(result?.status)"
            :class="['bg-white rounded-3xl shadow-xl border overflow-hidden', getBorderClasses(result?.status)]">
            <!-- Header -->
            <div :class="['px-6 py-5', getHeaderClasses(result?.status)]">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <Clock v-if="result?.status === 'not_yet_valid'" class="w-4 h-4 text-white" />
                        <AlertCircle v-else class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-white">{{ result?.title }}</h4>
                        <p class="text-white/80 text-sm">{{ result?.message }}</p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Used At Info -->
                <div v-if="result?.status === 'already_used' && result?.ticket?.used_at"
                    class="mb-6 p-4 bg-blue-50 rounded-2xl border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <Clock class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-800">Waktu Penggunaan</p>
                            <p class="text-xs text-blue-600">{{ result?.ticket?.used_at }}</p>
                        </div>
                    </div>
                </div>

                <!-- Ticket Info if available -->
                <div v-if="result?.ticket || result?.booking" class="grid grid-cols-2 gap-4 mb-6">
                    <div v-if="result?.ticket?.code" class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Ticket class="w-3 h-3" /> Kode Tiket
                        </p>
                        <p class="font-bold text-gray-900 font-mono">{{ result?.ticket?.code }}</p>
                    </div>
                    <div v-if="result?.booking?.customer_name" class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <User class="w-3 h-3" /> Nama
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.customer_name }}</p>
                    </div>
                    <div v-if="result?.booking?.destination" class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <MapPin class="w-3 h-3" /> Destinasi
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.booking?.destination }}</p>
                    </div>
                    <div v-if="result?.ticket?.valid_date" class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <Calendar class="w-3 h-3" /> Tanggal Berlaku
                        </p>
                        <p class="font-bold text-gray-900">{{ result?.ticket?.valid_date }}</p>
                    </div>
                </div>

                <button 
                    @click="emit('resetResult')"
                    class="w-full py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                    <RotateCcw class="w-5 h-5" />
                    Scan Berikutnya
                </button>
            </div>
        </div>
    </Transition>

    <!-- Success State (After Payment or Entry) -->
    <Transition name="bounce">
        <div v-if="successMessage" class="bg-white rounded-3xl shadow-xl border border-green-200 overflow-hidden">
            <div class="p-12 text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-emerald-400 to-green-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-green-500/30 animate-bounce-slow">
                    <CheckCircle class="w-12 h-12 text-white" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">{{ successMessage }}</h3>
                <p class="text-gray-500 mb-6">Transaksi berhasil diproses</p>
                <button 
                    @click="emit('resetResult')"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:shadow-lg transition-all">
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
