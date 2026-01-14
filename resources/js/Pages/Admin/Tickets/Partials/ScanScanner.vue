<script setup>
/**
 * ScanScanner.vue - Premium Scanner Component
 * Matching Newsletter design system
 */
import { Camera, X, Search, Edit3, History, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    scanning: Boolean,
    loading: Boolean,
    cameraReady: Boolean,
    cameras: Array,
    selectedCamera: String,
    recentSearches: Array,
    manualCode: String
});

const emit = defineEmits(['update:scanning', 'update:selectedCamera', 'update:manualCode', 'toggleScanner', 'switchCamera', 'validateCode', 'quickSearch']);
</script>

<template>
    <!-- Scanner Card -->
    <div class="content-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <Camera class="w-4 h-4 text-white" />
                    </div>
                    Scanner QR Code
                </h3>
                <!-- Camera Selector -->
                <select 
                    :value="selectedCamera"
                    @change="emit('update:selectedCamera', $event.target.value); emit('switchCamera')"
                    class="text-[10px] bg-white border border-gray-200 rounded-lg py-1.5 px-2 text-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option v-for="camera in cameras" :key="camera.deviceId" :value="camera.deviceId">
                        {{ camera.label || 'Camera ' + (cameras.indexOf(camera) + 1) }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Video Container -->
        <div class="relative aspect-square bg-gray-900">
            <div id="scanner-video" class="w-full h-full"></div>

            <!-- Overlay States -->
            <div class="absolute inset-0 pointer-events-none">
                <!-- Scanning Animation -->
                <div v-show="scanning" class="absolute inset-0 flex items-center justify-center">
                    <div class="relative w-64 h-64">
                        <div class="absolute inset-0 border-2 border-indigo-500/30 rounded-2xl"></div>
                        <div class="absolute top-0 left-0 w-12 h-12 border-t-4 border-l-4 border-indigo-500 rounded-tl-2xl"></div>
                        <div class="absolute top-0 right-0 w-12 h-12 border-t-4 border-r-4 border-indigo-500 rounded-tr-2xl"></div>
                        <div class="absolute bottom-0 left-0 w-12 h-12 border-b-4 border-l-4 border-indigo-500 rounded-bl-2xl"></div>
                        <div class="absolute bottom-0 right-0 w-12 h-12 border-b-4 border-r-4 border-indigo-500 rounded-br-2xl"></div>
                        <div class="absolute left-4 right-4 h-0.5 bg-gradient-to-r from-transparent via-indigo-400 to-transparent animate-scan"></div>
                    </div>
                </div>

                <!-- Idle State -->
                <div v-show="!scanning && !loading"
                    class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900/80 backdrop-blur-sm">
                    <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mb-4 border border-white/20">
                        <Camera class="w-10 h-10 text-white/60" />
                    </div>
                    <p class="text-white/70 text-xs">Klik tombol untuk memulai scan</p>
                </div>

                <!-- Loading -->
                <div v-show="loading"
                    class="absolute inset-0 flex items-center justify-center bg-gray-900/80 backdrop-blur-sm">
                    <div class="text-center">
                        <div class="w-16 h-16 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                        <p class="text-white font-medium text-sm">Memproses...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scanner Controls -->
        <div class="p-4">
            <button 
                type="button" 
                @click="emit('toggleScanner')" 
                :disabled="!cameraReady"
                :class="[
                    'w-full py-3 rounded-xl font-bold transition-all duration-300 flex items-center justify-center gap-2 text-xs',
                    scanning 
                        ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-lg shadow-red-500/30 hover:shadow-red-500/50' 
                        : 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed'
                ]">
                <component :is="scanning ? X : Camera" class="w-4 h-4" />
                <span>{{ scanning ? 'Matikan Kamera' : (cameraReady ? 'Aktifkan Kamera' : 'Memuat Library...') }}</span>
            </button>
        </div>
    </div>

    <!-- Manual Input Card -->
    <div class="content-card rounded-xl bg-white p-5 shadow-lg border border-gray-100">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-teal-500/30">
                <Edit3 class="w-4 h-4 text-white" />
            </div>
            Input Manual
        </h3>
        <form @submit.prevent="emit('validateCode')" class="space-y-3">
            <div class="relative">
                <input 
                    type="text" 
                    :value="manualCode"
                    @input="emit('update:manualCode', $event.target.value)"
                    placeholder="Kode Tiket (TKT-...) atau Order (TNLL-...)"
                    class="w-full pl-4 pr-14 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 focus:bg-white transition-all font-mono uppercase tracking-wider text-xs text-gray-900 placeholder:text-gray-400 placeholder:normal-case placeholder:tracking-normal">
                <button 
                    type="submit" 
                    :disabled="!manualCode || loading"
                    class="absolute right-2 top-2 bottom-2 aspect-square bg-gradient-to-br from-teal-500 to-emerald-600 hover:shadow-lg text-white rounded-lg flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                    <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                    <Search v-else class="w-4 h-4" />
                </button>
            </div>
            <p class="text-[10px] text-gray-400">Tekan Enter atau klik tombol untuk mencari</p>
        </form>

        <!-- Recent Searches -->
        <div v-show="recentSearches?.length > 0" class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-[10px] text-gray-400 mb-2 flex items-center gap-1 font-medium">
                <History class="w-3 h-3" />
                Pencarian Terakhir:
            </p>
            <div class="flex flex-wrap gap-2">
                <button 
                    v-for="search in recentSearches?.slice(0, 5)" 
                    :key="search"
                    @click="emit('quickSearch', search)"
                    class="px-3 py-1 bg-gray-100 hover:bg-teal-100 hover:text-teal-700 rounded-full text-[10px] font-mono text-gray-600 transition-colors">
                    {{ search }}
                </button>
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
.animate-scan {
    animation: scan 2s ease-in-out infinite;
}
</style>
