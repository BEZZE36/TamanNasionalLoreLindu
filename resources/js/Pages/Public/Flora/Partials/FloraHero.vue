<script setup>
import { Sparkles } from 'lucide-vue-next';
</script>

<template>
    <section class="relative pt-32 pb-20 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-600 via-emerald-700 to-green-900"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-green-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-emerald-400/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 animate-pulse" style="animation-delay: 1s"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl">
                <!-- Badge -->
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4 animate-fade-in-up">
                    <Sparkles class="w-4 h-4" />
                    Flora TNLL
                </span>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4 animate-fade-in-up" style="animation-delay: 0.1s">
                    Kekayaan Flora 🌿
                </h1>

                <!-- Description -->
                <p class="text-white/80 max-w-xl animate-fade-in-up" style="animation-delay: 0.2s">
                    Jelajahi keragaman tumbuhan endemik dan langka di Taman Nasional Lore Lindu, rumah bagi lebih dari 2.290 spesies flora
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
}
</style>
