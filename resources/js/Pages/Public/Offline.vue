<script setup>
import { WifiOff, RefreshCw, CheckCircle } from 'lucide-vue-next';

// Reload page
const reloadPage = () => {
    window.location.reload();
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 p-5">
        <!-- Background effects -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
        </div>

        <div class="relative bg-white rounded-3xl p-12 max-w-md text-center shadow-2xl">
            <!-- Icon -->
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-amber-500 to-orange-500 rounded-full flex items-center justify-center animate-pulse shadow-lg shadow-amber-500/30">
                <WifiOff class="w-10 h-10 text-white" />
            </div>

            <!-- Content -->
            <h1 class="text-3xl font-extrabold text-gray-900 mb-3">Anda Sedang Offline</h1>
            <p class="text-gray-500 mb-8 leading-relaxed">
                Sepertinya koneksi internet Anda terputus. Periksa koneksi dan coba lagi.
            </p>

            <!-- Button -->
            <button 
                @click="reloadPage"
                class="inline-flex items-center gap-3 px-7 py-4 bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold rounded-xl hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-500/30 transition-all duration-300"
            >
                <RefreshCw class="w-5 h-5" />
                Coba Lagi
            </button>

            <!-- Tips -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-gray-600 mb-4 flex items-center justify-center gap-2">
                    💡 Tips saat offline:
                </h3>
                <ul class="text-left space-y-3">
                    <li class="flex items-start gap-3 text-sm text-gray-500">
                        <CheckCircle class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" />
                        <span>Periksa koneksi WiFi atau data seluler</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-500">
                        <CheckCircle class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" />
                        <span>Coba restart router atau modem</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-500">
                        <CheckCircle class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" />
                        <span>Halaman yang pernah dikunjungi mungkin tersimpan</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
