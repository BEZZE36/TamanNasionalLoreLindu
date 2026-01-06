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
    <div class="space-y-4">
        <!-- Search -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Search class="h-5 w-5 text-gray-400" />
            </div>
            <input 
                :value="search"
                @input="emit('update:search', $event.target.value)"
                type="text" 
                placeholder="Cari pertanyaan atau jawaban..."
                class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-200 bg-white shadow-lg focus:border-violet-400 focus:ring-4 focus:ring-violet-100 transition-all text-sm"
            >
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap items-center gap-3">
            <button 
                type="button" 
                @click="emit('update:category', 'all')"
                :class="[
                    'inline-flex items-center gap-2 px-5 py-3 rounded-2xl text-sm font-semibold border-2 transition-all hover:scale-105',
                    category === 'all' 
                        ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white border-transparent shadow-lg' 
                        : 'bg-white text-gray-600 border-gray-200 hover:shadow-lg'
                ]"
            >
                <Filter class="w-4 h-4" />
                Semua
                <span :class="['px-2 py-0.5 rounded-lg text-xs font-bold', category === 'all' ? 'bg-white/20' : 'bg-gray-100']">
                    {{ getCount('all') }}
                </span>
            </button>

            <button 
                v-for="cat in categories" 
                :key="cat.name"
                type="button" 
                @click="emit('update:category', cat.name)"
                :class="[
                    'inline-flex items-center gap-2 px-5 py-3 rounded-2xl text-sm font-semibold border-2 transition-all hover:scale-105',
                    category === cat.name 
                        ? `bg-gradient-to-r ${cat.gradient} text-white border-transparent shadow-lg` 
                        : 'bg-white text-gray-600 border-gray-200 hover:shadow-lg'
                ]"
            >
                <Tag class="w-4 h-4" />
                {{ cat.name }}
                <span :class="['px-2 py-0.5 rounded-lg text-xs font-bold', category === cat.name ? 'bg-white/20' : 'bg-gray-100']">
                    {{ getCount(cat.name) }}
                </span>
            </button>
        </div>
    </div>
</template>
