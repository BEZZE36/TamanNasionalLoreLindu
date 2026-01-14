<script setup>
/**
 * FloraFilter.vue - Premium Filter with Glassmorphism
 * Design: Modern, responsive, animated filter bar
 * Sizing: text-xs buttons, text-[11px] labels
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Search, Leaf, Sparkles, Flower2, SlidersHorizontal, X, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    categories: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) }
});

const filterRef = ref(null);
const showFilters = ref(false);
let ctx;

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const sort = ref(props.filters.sort || 'newest');

const hasActiveFilters = () => {
    return search.value || category.value || sort.value !== 'newest';
};

const handleSearch = () => {
    router.get('/flora', {
        search: search.value || undefined,
        category: category.value || undefined,
        sort: sort.value !== 'newest' ? sort.value : undefined
    }, { preserveState: true });
};

const onFilterChange = () => {
    router.get('/flora', {
        search: props.filters.search || undefined,
        category: category.value || undefined,
        sort: sort.value !== 'newest' ? sort.value : undefined
    }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    category.value = '';
    sort.value = 'newest';
    router.get('/flora', {}, { preserveState: true });
};

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.filter-item',
            { opacity: 0, y: -10 },
            { opacity: 1, y: 0, duration: 0.3, stagger: 0.05, ease: 'power2.out', delay: 0.5 }
        );
    }, filterRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <section ref="filterRef" class="py-4 sm:py-5 bg-white/95 backdrop-blur-xl border-b border-gray-100 sticky top-16 sm:top-20 z-30 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form @submit.prevent="handleSearch">
                <!-- Desktop Layout -->
                <div class="hidden md:flex items-center gap-3 flex-wrap">
                    <!-- Search Input -->
                    <div class="filter-item flex-1 min-w-[200px] max-w-sm relative">
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari flora..." 
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:bg-white transition-all"
                        >
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    </div>

                    <!-- Category Filter Buttons -->
                    <div class="filter-item flex items-center gap-1.5 p-1 rounded-xl bg-gray-100 border border-gray-200">
                        <button 
                            type="button" 
                            @click="category = ''; onFilterChange()"
                            :class="['px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all flex items-center gap-1', !category ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-600 hover:text-gray-900']"
                        >
                            <Leaf class="w-3 h-3" />Semua
                        </button>
                        <button 
                            type="button" 
                            @click="category = 'endemik'; onFilterChange()"
                            :class="['px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all flex items-center gap-1', category === 'endemik' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-600 hover:text-gray-900']"
                        >
                            <Sparkles class="w-3 h-3" />Endemik
                        </button>
                        <button 
                            type="button" 
                            @click="category = 'langka'; onFilterChange()"
                            :class="['px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all flex items-center gap-1', category === 'langka' ? 'bg-white text-amber-600 shadow-sm' : 'text-gray-600 hover:text-gray-900']"
                        >
                            <Flower2 class="w-3 h-3" />Langka
                        </button>
                    </div>

                    <!-- Sort -->
                    <div class="filter-item relative">
                        <select 
                            v-model="sort" 
                            @change="onFilterChange()"
                            class="appearance-none pl-3 pr-8 py-2.5 text-[11px] font-medium rounded-xl border border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:bg-white transition-all cursor-pointer"
                        >
                            <option value="newest">Terbaru</option>
                            <option value="alphabetical">A-Z</option>
                            <option value="popular">Terpopuler</option>
                        </select>
                        <ChevronDown class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" />
                    </div>

                    <!-- Search Button -->
                    <button 
                        type="submit" 
                        class="filter-item px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-[11px] rounded-xl shadow-md shadow-emerald-500/25 hover:shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-0.5 transition-all flex items-center gap-1.5"
                    >
                        <Search class="w-3.5 h-3.5" />
                        <span>Cari</span>
                    </button>

                    <!-- Clear Filters -->
                    <button 
                        v-if="hasActiveFilters()"
                        type="button"
                        @click="clearFilters"
                        class="filter-item px-3 py-2.5 rounded-xl border border-gray-200 text-gray-500 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-all"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Mobile Layout -->
                <div class="md:hidden space-y-3">
                    <!-- Search Row -->
                    <div class="flex items-center gap-2">
                        <div class="flex-1 relative">
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Cari flora..." 
                                class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                            >
                            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        </div>
                        <button 
                            type="submit" 
                            class="w-11 h-11 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white flex items-center justify-center shadow-md"
                        >
                            <Search class="w-4 h-4" />
                        </button>
                        <button 
                            type="button"
                            @click="showFilters = !showFilters"
                            :class="['w-11 h-11 rounded-xl border flex items-center justify-center transition-all', showFilters ? 'bg-emerald-50 border-emerald-300 text-emerald-600' : 'border-gray-200 text-gray-500']"
                        >
                            <SlidersHorizontal class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Category Filter Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
                        <button 
                            type="button" 
                            @click="category = ''; onFilterChange()"
                            :class="['flex-shrink-0 px-4 py-2 rounded-full text-[11px] font-semibold transition-all flex items-center gap-1.5', !category ? 'bg-emerald-500 text-white shadow-md' : 'bg-gray-100 text-gray-600']"
                        >
                            <Leaf class="w-3 h-3" />Semua
                        </button>
                        <button 
                            type="button" 
                            @click="category = 'endemik'; onFilterChange()"
                            :class="['flex-shrink-0 px-4 py-2 rounded-full text-[11px] font-semibold transition-all flex items-center gap-1.5', category === 'endemik' ? 'bg-purple-500 text-white shadow-md' : 'bg-gray-100 text-gray-600']"
                        >
                            <Sparkles class="w-3 h-3" />Endemik
                        </button>
                        <button 
                            type="button" 
                            @click="category = 'langka'; onFilterChange()"
                            :class="['flex-shrink-0 px-4 py-2 rounded-full text-[11px] font-semibold transition-all flex items-center gap-1.5', category === 'langka' ? 'bg-amber-500 text-white shadow-md' : 'bg-gray-100 text-gray-600']"
                        >
                            <Flower2 class="w-3 h-3" />Langka
                        </button>
                    </div>

                    <!-- Expanded Filters -->
                    <div v-if="showFilters" class="space-y-2.5 pt-2 border-t border-gray-100 animate-slide-down">
                        <select 
                            v-model="sort" 
                            @change="onFilterChange()"
                            class="w-full px-3 py-2.5 text-[11px] font-medium rounded-xl border border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                        >
                            <option value="newest">Terbaru</option>
                            <option value="alphabetical">A-Z</option>
                            <option value="popular">Terpopuler</option>
                        </select>

                        <button 
                            v-if="hasActiveFilters()"
                            type="button"
                            @click="clearFilters"
                            class="w-full py-2.5 rounded-xl border border-red-200 bg-red-50 text-red-600 text-[11px] font-semibold flex items-center justify-center gap-1.5"
                        >
                            <X class="w-3.5 h-3.5" />
                            Hapus Filter
                        </button>
                    </div>
                </div>
            </form>
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

@keyframes slide-down {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down { animation: slide-down 0.2s ease-out; }
</style>
