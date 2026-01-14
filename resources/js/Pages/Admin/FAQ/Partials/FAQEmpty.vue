<script setup>
import { HelpCircle, Plus, Sparkles } from 'lucide-vue-next';

defineProps({
    search: String,
    category: String
});

const emit = defineEmits(['add']);
</script>

<template>
    <div class="text-center py-16 bg-white rounded-xl border border-gray-100 shadow-lg">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-100 to-purple-100 mb-4">
            <HelpCircle class="w-8 h-8 text-violet-400" />
        </div>
        <h3 class="text-sm font-bold text-gray-800 mb-1">
            {{ search ? 'Tidak Ditemukan' : (category === 'all' ? 'Belum Ada FAQ' : 'Tidak Ada FAQ di Kategori Ini') }}
        </h3>
        <p class="text-gray-500 text-xs mb-6 max-w-sm mx-auto">
            {{ search ? 'Coba kata kunci lain atau tambah FAQ baru' : 'Klik tombol di bawah untuk menambahkan FAQ baru.' }}
        </p>
        <button 
            type="button" 
            @click="emit('add')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white text-xs font-bold shadow-xl shadow-violet-500/30 hover:shadow-violet-500/50 hover:-translate-y-0.5 hover:scale-105 transition-all"
        >
            <Plus class="w-4 h-4" />
            Tambah Pertanyaan
            <Sparkles class="w-3.5 h-3.5 text-amber-300" />
        </button>
    </div>
</template>
