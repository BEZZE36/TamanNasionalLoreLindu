<script setup>
import { HelpCircle, Plus } from 'lucide-vue-next';

defineProps({
    search: String,
    category: String
});

const emit = defineEmits(['add']);
</script>

<template>
    <div class="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-xl">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 mb-6">
            <HelpCircle class="w-12 h-12 text-violet-400" />
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">
            {{ search ? 'Tidak Ditemukan' : (category === 'all' ? 'Belum Ada FAQ' : 'Tidak Ada FAQ di Kategori Ini') }}
        </h3>
        <p class="text-gray-500 text-sm mb-8 max-w-md mx-auto">
            {{ search ? 'Coba kata kunci lain atau tambah FAQ baru' : 'Klik tombol di bawah untuk menambahkan FAQ baru.' }}
        </p>
        <button 
            type="button" 
            @click="emit('add')"
            class="inline-flex items-center gap-3 px-6 py-4 rounded-2xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold shadow-xl shadow-violet-500/30 hover:shadow-violet-500/50 hover:-translate-y-1 hover:scale-105 transition-all"
        >
            <Plus class="w-4 h-4" />
            Tambah Pertanyaan
        </button>
    </div>
</template>
