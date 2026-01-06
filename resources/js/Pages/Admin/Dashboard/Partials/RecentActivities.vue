<script setup>
import { Activity } from 'lucide-vue-next';

defineProps({
    activities: { type: Array, default: () => [] }
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30">
                    <Activity class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h2>
                    <p class="text-[11px] text-gray-500">Riwayat aktivitas sistem</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div v-for="(activity, index) in activities" :key="index" class="group flex items-start gap-4 p-4 rounded-2xl bg-gradient-to-br from-gray-50 to-white border border-gray-100 hover:shadow-lg hover:border-violet-200 transition-all">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-violet-100 to-purple-100 text-violet-600 group-hover:from-violet-500 group-hover:to-purple-600 group-hover:text-white transition-all">
                        <Activity class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors">{{ activity.title }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ activity.description }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ formatDate(activity.time) }}</p>
                    </div>
                </div>
                <div v-if="!activities?.length" class="col-span-full text-center py-12">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                        <Activity class="w-8 h-8 text-gray-400" />
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada aktivitas</p>
                </div>
            </div>
        </div>
    </div>
</template>
