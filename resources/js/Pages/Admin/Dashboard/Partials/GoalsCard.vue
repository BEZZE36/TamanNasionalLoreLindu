<script setup>
import { Target } from 'lucide-vue-next';

defineProps({
    goals: Object
});
</script>

<template>
    <div v-if="goals" class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30">
                    <Target class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-lg font-bold text-gray-900">Target Bulanan</h2>
            </div>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-medium text-gray-700">Booking</span>
                    <span class="text-gray-500">{{ goals.monthly_bookings || 0 }} / {{ goals.monthly_booking_goal || 100 }}</span>
                </div>
                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-violet-500 to-purple-600 rounded-full transition-all duration-1000" :style="{ width: `${goals.booking_progress || 0}%` }"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-medium text-gray-700">Pendapatan</span>
                    <span class="text-gray-500">{{ goals.revenue_progress || 0 }}%</span>
                </div>
                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full transition-all duration-1000" :style="{ width: `${goals.revenue_progress || 0}%` }"></div>
                </div>
            </div>
        </div>
    </div>
</template>
