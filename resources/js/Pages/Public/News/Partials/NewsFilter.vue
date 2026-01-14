<script setup>
/**
 * NewsFilter.vue - Modern Glassmorphism Sticky Filter
 * Features: Search, archive filter, responsive design
 * Theme: Rose/Red for News
 */
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, Calendar, X, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    archives: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    totalNews: { type: Number, default: 0 }
});

const search = ref(props.filters.search || '');
const archive = ref(props.filters.archive || '');
const isSearchFocused = ref(false);

const handleSearch = () => {
    router.get('/news', {
        search: search.value || undefined,
        archive: archive.value || undefined
    }, { preserveState: true, preserveScroll: true });
};

const selectArchive = (value) => {
    archive.value = archive.value === value ? '' : value;
    router.get('/news', {
        search: props.filters.search || undefined,
        archive: archive.value || undefined
    }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
    search.value = '';
    archive.value = '';
    router.get('/news', {}, { preserveState: true, preserveScroll: true });
};

const hasActiveFilters = computed(() => search.value || archive.value);

// Format archive for display
const formatArchive = (archiveItem) => {
    if (!archiveItem) return '';
    if (typeof archiveItem === 'string') return archiveItem;
    return `${archiveItem.month_name || ''} ${archiveItem.year || ''}`.trim();
};
</script>

<template>
    <section id="news-content" class="py-4 sm:py-6 bg-white/80 backdrop-blur-xl border-b border-gray-100 sticky top-16 sm:top-20 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Mobile: Stacked Layout -->
            <div class="flex flex-col gap-3 sm:gap-4">
                <!-- Search Bar -->
                <form @submit.prevent="handleSearch" class="relative">
                    <div :class="['relative transition-all duration-300', isSearchFocused ? 'scale-[1.02]' : '']">
                        <input v-model="search" 
                               type="text" 
                               placeholder="Cari berita..."
                               @focus="isSearchFocused = true"
                               @blur="isSearchFocused = false"
                               class="w-full pl-10 sm:pl-12 pr-20 sm:pr-24 py-2.5 sm:py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all text-sm bg-white/80 backdrop-blur-sm placeholder:text-gray-400">
                        <Search class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-4 sm:w-5 h-4 sm:h-5 text-gray-400" />
                        <button type="submit" 
                                class="absolute right-2 top-1/2 -translate-y-1/2 px-3 sm:px-4 py-1.5 sm:py-2 bg-gradient-to-r from-rose-500 to-red-600 text-white font-semibold rounded-lg text-xs sm:text-sm shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 transition-all">
                            Cari
                        </button>
                    </div>
                </form>

                <!-- Archive Filter -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-hide">
                    <!-- All -->
                    <button @click="selectArchive('')"
                            :class="['shrink-0 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[10px] sm:text-xs font-semibold transition-all duration-300 border',
                                     !archive ? 'bg-gradient-to-r from-rose-500 to-red-600 text-white border-transparent shadow-lg shadow-rose-500/30' 
                                              : 'bg-white text-gray-600 border-gray-200 hover:border-rose-300 hover:text-rose-600']">
                        <span class="flex items-center gap-1">
                            <Sparkles class="w-3 h-3" />
                            Semua
                        </span>
                    </button>

                    <!-- Archive Buttons -->
                    <button v-for="arch in archives?.slice(0, 6)" 
                            :key="arch.value || arch"
                            @click="selectArchive(arch.value || arch)"
                            :class="['shrink-0 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[10px] sm:text-xs font-semibold transition-all duration-300 border flex items-center gap-1',
                                     archive === (arch.value || arch) ? 'bg-gradient-to-r from-rose-500 to-red-600 text-white border-transparent shadow-lg shadow-rose-500/30' 
                                                                      : 'bg-white text-gray-600 border-gray-200 hover:border-rose-300 hover:text-rose-600']">
                        <Calendar class="w-3 h-3" />
                        {{ formatArchive(arch) }}
                    </button>

                    <!-- Clear Filters -->
                    <button v-if="hasActiveFilters" 
                            @click="clearFilters"
                            class="shrink-0 px-3 py-1.5 sm:py-2 rounded-full text-[10px] sm:text-xs font-semibold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-all flex items-center gap-1">
                        <X class="w-3 h-3" />
                        Reset
                    </button>
                </div>

                <!-- Results Count -->
                <div v-if="totalNews > 0" class="text-[10px] sm:text-xs text-gray-500 text-center sm:text-left">
                    Menampilkan <span class="font-semibold text-gray-700">{{ totalNews }}</span> berita
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
