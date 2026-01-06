<script setup>
import { Search } from 'lucide-vue-next';

defineProps({ scrolled: Boolean });

const openSearch = () => { window.dispatchEvent(new CustomEvent('open-search')); };
</script>

<template>
    <button 
        @click="openSearch"
        :class="[
            'group relative w-7 h-7 sm:w-8 sm:h-8 lg:w-9 lg:h-9 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-105',
            scrolled 
                ? 'text-gray-600 hover:text-emerald-600 hover:bg-gray-100' 
                : 'text-white hover:bg-white/15'
        ]"
        title="Cari (Ctrl+K)"
        aria-label="Buka pencarian"
    >
        <Search class="w-3.5 h-3.5 sm:w-4 sm:h-4 transition-all duration-300 group-hover:scale-110 group-hover:rotate-12" />
        
        <!-- Keyboard shortcut hint - hidden on mobile -->
        <span 
            :class="[
                'hidden sm:block absolute -bottom-1 -right-1 px-1 py-0.5 rounded text-[7px] font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-200',
                scrolled ? 'bg-gray-200 text-gray-600' : 'bg-white/20 text-white/80'
            ]"
        >⌘K</span>
    </button>
</template>
