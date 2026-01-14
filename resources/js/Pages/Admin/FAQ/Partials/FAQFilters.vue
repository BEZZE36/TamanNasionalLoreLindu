<script setup>
import { Search, Filter, Tag } from 'lucide-vue-next';

const props = defineProps({
    search: String,
    category: String,
    categories: Array,
    getCount: Function
});

const emit = defineEmits(['update:search', 'update:category']);
</script>

<template>
    <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100 space-y-4 overflow-hidden">
        <!-- Search -->
        <div class="relative group">
            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-violet-500 transition-colors" />
            <input 
                :value="search"
                @input="emit('update:search', $event.target.value)"
                type="text" 
                placeholder="Cari pertanyaan atau jawaban..."
                class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 focus:bg-white transition-all duration-300"
            >
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap items-center gap-2">
            <button 
                type="button" 
                @click="emit('update:category', 'all')"
                :class="[
                    'inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:scale-105',
                    category === 'all' 
                        ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-lg' 
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
            >
                <Filter class="w-3.5 h-3.5" />
                Semua
                <span :class="['px-1.5 py-0.5 rounded-lg text-[10px] font-bold', category === 'all' ? 'bg-white/20' : 'bg-white']">
                    {{ getCount('all') }}
                </span>
            </button>

            <button 
                v-for="cat in categories" 
                :key="cat.name"
                type="button" 
                @click="emit('update:category', cat.name)"
                :class="[
                    'inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all hover:scale-105',
                    category === cat.name 
                        ? `bg-gradient-to-r ${cat.gradient} text-white shadow-lg` 
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
            >
                <Tag class="w-3.5 h-3.5" />
                {{ cat.name }}
                <span :class="['px-1.5 py-0.5 rounded-lg text-[10px] font-bold', category === cat.name ? 'bg-white/20' : 'bg-white']">
                    {{ getCount(cat.name) }}
                </span>
            </button>
        </div>
    </div>
</template>
