<script setup>
import { Sparkles, Calendar } from 'lucide-vue-next';

defineProps({
    periodLabel: String,
    todayBookings: Number,
    pendingBookings: Number,
});
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-5 shadow-xl shadow-violet-500/25">
        <!-- Animated Background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
        </div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="space-y-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-white/20 backdrop-blur-xl ring-1 ring-white/30">
                        <Sparkles class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-lg font-bold text-white tracking-tight">Selamat Datang!</h1>
                        <p class="text-purple-100/90 text-xs font-medium">Dashboard Admin TNLL Explore</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 backdrop-blur-xl ring-1 ring-white/20">
                        <Calendar class="w-3.5 h-3.5 text-purple-200" />
                        <span class="text-purple-100 text-xs font-medium">{{ periodLabel }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-purple-100/80 text-[10px]">Hari ini</p>
                    <p class="text-white font-bold text-sm">{{ todayBookings || 0 }} Booking</p>
                </div>
                <div class="w-px h-8 bg-white/20"></div>
                <div class="text-right">
                    <p class="text-purple-100/80 text-[10px]">Pending</p>
                    <p class="text-amber-300 font-bold text-sm">{{ pendingBookings || 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
