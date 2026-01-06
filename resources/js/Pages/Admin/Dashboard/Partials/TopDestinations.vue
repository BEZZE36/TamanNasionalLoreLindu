<script setup>
import { Link } from '@inertiajs/vue3';
import { MapPin, ChevronRight } from 'lucide-vue-next';

defineProps({
    destinations: { type: Array, default: () => [] }
});

const getRankClass = (index) => {
    if (index === 0) return 'bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-amber-500/30';
    if (index === 1) return 'bg-gradient-to-br from-gray-300 to-gray-400 text-white shadow-gray-400/30';
    if (index === 2) return 'bg-gradient-to-br from-amber-600 to-amber-700 text-white shadow-amber-600/30';
    return 'bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-violet-500/30';
};
</script>

<template>
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-violet-50 to-purple-50">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30">
                    <MapPin class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-lg font-bold text-gray-900">Destinasi Populer</h2>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <Link 
                v-for="(dest, index) in destinations?.slice(0, 5)" 
                :key="dest.id"
                :href="`/admin/destinations/${dest.id}/edit`"
                class="group flex items-center gap-4 p-3 rounded-xl hover:bg-gradient-to-r hover:from-violet-50 hover:to-purple-50 transition-all"
            >
                <div :class="['flex items-center justify-center w-10 h-10 rounded-xl font-bold text-sm shadow-lg', getRankClass(index)]">
                    {{ index + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate group-hover:text-violet-600 transition-colors">{{ dest.name }}</p>
                    <p class="text-[11px] text-gray-500">{{ dest.bookings_count }} booking</p>
                </div>
                <ChevronRight class="w-5 h-5 text-gray-300 group-hover:text-violet-500 group-hover:translate-x-1 transition-all" />
            </Link>
            <div v-if="!destinations?.length" class="text-center py-8">
                <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                    <MapPin class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 font-medium">Belum ada data</p>
            </div>
        </div>
    </div>
</template>
