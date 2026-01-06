<script setup>
import { Megaphone } from 'lucide-vue-next';
</script>

<template>
    <section class="relative pt-32 pb-20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-600 via-red-700 to-rose-900"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-rose-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-red-400/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 animate-pulse" style="animation-delay: 1s"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4"><Megaphone class="w-4 h-4" />Berita TNLL</span>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">Berita Terkini 📢</h1>
                <p class="text-white/80 max-w-xl">Informasi dan berita terbaru seputar Taman Nasional Lore Lindu</p>
            </div>
        </div>
    </section>
</template>
