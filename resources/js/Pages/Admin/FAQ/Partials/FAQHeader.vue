<script setup>
import { HelpCircle, Plus } from 'lucide-vue-next';

defineProps({
    count: { type: Number, default: 0 },
    categoriesCount: { type: Number, default: 5 }
});

const emit = defineEmits(['add']);
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-500 via-purple-500 to-violet-600 p-5 shadow-xl shadow-violet-500/25">
        <!-- Animated Background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
        </div>
        
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute inset-0 bg-white/30 rounded-2xl blur-lg"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <HelpCircle class="h-8 w-8 text-white" />
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl lg:text-xl font-bold text-white tracking-tight">Kelola FAQ</h1>
                    <p class="mt-1 text-purple-100/90 text-lg font-medium">{{ count }} pertanyaan • {{ categoriesCount }} kategori</p>
                </div>
            </div>
            <button 
                @click="emit('add')" 
                type="button"
                class="group relative inline-flex items-center gap-3 rounded-2xl bg-white/20 backdrop-blur-sm px-6 py-4 text-white font-bold hover:bg-white/30 hover:scale-105 transition-all duration-300 border border-white/30 shadow-lg"
            >
                <Plus class="w-5 h-5 group-hover:rotate-90 transition-transform duration-500" />
                <span>Tambah Pertanyaan</span>
            </button>
        </div>
    </div>
</template>
