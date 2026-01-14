<script setup>
import { HelpCircle, Plus, Sparkles } from 'lucide-vue-next';

defineProps({
    count: { type: Number, default: 0 },
    categoriesCount: { type: Number, default: 5 }
});

const emit = defineEmits(['add']);
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-6 shadow-2xl">
        <!-- Animated Background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute top-10 right-40 w-32 h-32 bg-purple-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
        </div>
        
        <!-- Floating particles -->
        <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-purple-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
        
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <HelpCircle class="h-6 w-6 text-white" />
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Kelola FAQ</h1>
                        <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                            {{ count }} Pertanyaan
                        </span>
                    </div>
                    <p class="text-purple-100/80 text-xs">{{ categoriesCount }} kategori tersedia</p>
                </div>
            </div>
            <button 
                @click="emit('add')" 
                type="button"
                class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-purple-600 text-xs font-bold hover:bg-purple-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
            >
                <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform duration-500" />
                <span>Tambah Pertanyaan</span>
                <Sparkles class="w-3.5 h-3.5 text-amber-500" />
            </button>
        </div>
    </div>
</template>
