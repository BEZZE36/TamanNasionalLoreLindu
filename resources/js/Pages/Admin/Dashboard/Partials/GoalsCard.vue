<script setup>
import { onMounted, onBeforeUnmount, computed, nextTick } from 'vue';
import { gsap } from 'gsap';
import { Target, TrendingUp, Wallet, Calendar, Flag, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    goals: Object,
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const fills = document.querySelectorAll('.progress-fill');
        if (fills.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(fills, 
                    { width: '0%' }, 
                    { width: 'var(--progress)', duration: 1.2, delay: 0.3, ease: 'power2.out' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const formatCurrency = (amount) => {
    if (amount >= 1000000) return `${(amount / 1000000).toFixed(1)}jt`;
    if (amount >= 1000) return `${(amount / 1000).toFixed(0)}rb`;
    return amount?.toString() || '0';
};

const bookingProgress = computed(() => props.goals?.booking_progress || 0);
const revenueProgress = computed(() => props.goals?.revenue_progress || 0);
</script>

<template>
    <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <Target class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Target Bulanan</h3>
                        <p class="text-[10px] text-gray-500">Progress pencapaian bulan ini</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 px-2 py-1 bg-white rounded-lg shadow-sm">
                    <Calendar class="w-3 h-3 text-gray-400" />
                    <span class="text-[10px] font-medium text-gray-600">{{ new Date().toLocaleDateString('id-ID', { month: 'long' }) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-4 space-y-4">
            <!-- Booking Goal -->
            <div class="group">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-100 to-purple-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <TrendingUp class="w-3.5 h-3.5 text-violet-600" />
                        </div>
                        <span class="text-xs font-semibold text-gray-700">Booking</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-gray-800">{{ goals?.monthly_bookings || 0 }}</span>
                        <span class="text-[10px] text-gray-400"> / {{ goals?.monthly_booking_goal || 100 }}</span>
                    </div>
                </div>
                <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div 
                        class="progress-fill absolute inset-y-0 left-0 bg-gradient-to-r from-violet-500 to-purple-600 rounded-full transition-all duration-1000"
                        :style="{ '--progress': `${bookingProgress}%` }"
                    >
                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                    <!-- Milestone markers -->
                    <div class="absolute top-0 left-1/4 w-px h-full bg-gray-300"></div>
                    <div class="absolute top-0 left-1/2 w-px h-full bg-gray-300"></div>
                    <div class="absolute top-0 left-3/4 w-px h-full bg-gray-300"></div>
                </div>
                <div class="flex items-center justify-between mt-1.5">
                    <div class="flex items-center gap-1">
                        <div :class="['w-1.5 h-1.5 rounded-full', bookingProgress >= 100 ? 'bg-emerald-500' : 'bg-violet-500']"></div>
                        <span :class="['text-[10px] font-bold', bookingProgress >= 100 ? 'text-emerald-600' : 'text-violet-600']">{{ bookingProgress }}%</span>
                    </div>
                    <span v-if="bookingProgress >= 100" class="flex items-center gap-1 text-[10px] text-emerald-600 font-medium">
                        <Sparkles class="w-3 h-3" /> Target tercapai!
                    </span>
                </div>
            </div>
            
            <!-- Revenue Goal -->
            <div class="group">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <Wallet class="w-3.5 h-3.5 text-emerald-600" />
                        </div>
                        <span class="text-xs font-semibold text-gray-700">Pendapatan</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black text-gray-800">Rp {{ formatCurrency(goals?.monthly_revenue || 0) }}</span>
                        <span class="text-[10px] text-gray-400"> / {{ formatCurrency(goals?.monthly_revenue_goal || 5000000) }}</span>
                    </div>
                </div>
                <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div 
                        class="progress-fill absolute inset-y-0 left-0 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full transition-all duration-1000"
                        :style="{ '--progress': `${revenueProgress}%` }"
                    >
                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                    <!-- Milestone markers -->
                    <div class="absolute top-0 left-1/4 w-px h-full bg-gray-300"></div>
                    <div class="absolute top-0 left-1/2 w-px h-full bg-gray-300"></div>
                    <div class="absolute top-0 left-3/4 w-px h-full bg-gray-300"></div>
                </div>
                <div class="flex items-center justify-between mt-1.5">
                    <div class="flex items-center gap-1">
                        <div :class="['w-1.5 h-1.5 rounded-full', revenueProgress >= 100 ? 'bg-emerald-500' : 'bg-emerald-500']"></div>
                        <span :class="['text-[10px] font-bold', revenueProgress >= 100 ? 'text-emerald-600' : 'text-emerald-600']">{{ revenueProgress }}%</span>
                    </div>
                    <span v-if="revenueProgress >= 100" class="flex items-center gap-1 text-[10px] text-emerald-600 font-medium">
                        <Sparkles class="w-3 h-3" /> Target tercapai!
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.progress-fill {
    width: var(--progress, 0%);
}
</style>
