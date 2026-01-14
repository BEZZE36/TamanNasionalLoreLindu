<script setup>
/**
 * IndexFilters.vue - Premium Floating Filter Bar
 * Follows home-design-system.md: text-[11px] for filters, px-3 py-1.5 for buttons
 */
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Search, SlidersHorizontal, ArrowUpDown, ChevronDown, X, Mountain, TreePine, Waves, Building, Camera, Tent } from 'lucide-vue-next';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    minPrice: { type: Number, default: 0 },
    maxPrice: { type: Number, default: 1000000 },
    allFacilities: { type: Array, default: () => [] }
});

const filterRef = ref(null);
const search = ref(props.filters.search || '');
const sortOpen = ref(false);
const filterModalOpen = ref(false);
const priceMin = ref(props.filters.min_price || props.minPrice);
const priceMax = ref(props.filters.max_price || props.maxPrice);
const selectedFacilities = ref(props.filters.facilities || []);

const categoryIcons = { 'alam': Mountain, 'air-terjun': Waves, 'danau': Waves, 'budaya': Building, 'megalitik': Building, 'foto': Camera, 'camping': Tent, 'default': TreePine };
const getCategoryIcon = (slug) => { for (const [key, icon] of Object.entries(categoryIcons)) { if (slug?.toLowerCase().includes(key)) return icon; } return categoryIcons.default; };

const sortOptions = [
    { key: 'popular', label: 'Populer', icon: '🔥' },
    { key: 'newest', label: 'Terbaru', icon: '🆕' },
    { key: 'rating', label: 'Rating', icon: '⭐' },
    { key: 'cheapest', label: 'Termurah', icon: '💰' },
];

const currentSort = computed(() => sortOptions.find(o => o.key === props.filters.sort)?.label || 'Populer');
const hasActiveFilters = computed(() => props.filters.min_price || props.filters.facilities?.length > 0);

const handleSearch = () => { router.get('/destinations', { ...props.filters, search: search.value, page: undefined }, { preserveState: true }); };
const setCategory = (slug) => { router.get('/destinations', { ...props.filters, category: slug || undefined, page: undefined }, { preserveState: true }); };
const setSort = (key) => { router.get('/destinations', { ...props.filters, sort: key, page: undefined }, { preserveState: true }); sortOpen.value = false; };
const applyFilters = () => {
    router.get('/destinations', { ...props.filters, min_price: priceMin.value > props.minPrice ? priceMin.value : undefined, max_price: priceMax.value < props.maxPrice ? priceMax.value : undefined, facilities: selectedFacilities.value.length > 0 ? selectedFacilities.value : undefined, page: undefined }, { preserveState: true });
    filterModalOpen.value = false;
};
const clearFilters = () => { priceMin.value = props.minPrice; priceMax.value = props.maxPrice; selectedFacilities.value = []; router.get('/destinations', { search: props.filters.search, category: props.filters.category, sort: props.filters.sort }, { preserveState: true }); filterModalOpen.value = false; };

onMounted(() => { gsap.fromTo(filterRef.value, { y: -15, opacity: 0 }, { y: 0, opacity: 1, duration: 0.4, ease: 'power2.out' }); });
</script>

<template>
    <section ref="filterRef" class="sticky top-16 z-40 py-2">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative bg-white/90 backdrop-blur-xl rounded-xl shadow-lg border border-gray-100 p-2.5">
                <div class="flex flex-col lg:flex-row items-center gap-2.5">
                    <!-- Search -->
                    <div class="w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
                        <div class="relative group">
                            <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                            <input v-model="search" @keyup.enter="handleSearch" type="text" placeholder="Cari destinasi..." class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[11px] text-gray-700 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-emerald-400 transition-all">
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="flex-1 overflow-x-auto scrollbar-hide">
                        <div class="flex items-center gap-1.5 min-w-max">
                            <button @click="setCategory(null)" :class="['flex items-center gap-1 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all whitespace-nowrap', !filters.category ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                <TreePine class="w-3 h-3" />
                                Semua
                            </button>
                            <button v-for="category in categories" :key="category.id" @click="setCategory(category.slug)" :class="['flex items-center gap-1 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all whitespace-nowrap', filters.category === category.slug ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                <component :is="getCategoryIcon(category.slug)" class="w-3 h-3" />
                                {{ category.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Filter & Sort -->
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button @click="filterModalOpen = true" class="relative flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-[11px] font-semibold text-gray-700 transition-all">
                            <SlidersHorizontal class="w-3.5 h-3.5" />
                            Filter
                            <span v-if="hasActiveFilters" class="absolute -top-1 -right-1 w-2 h-2 bg-emerald-500 rounded-full"></span>
                        </button>
                        <div class="relative">
                            <button @click="sortOpen = !sortOpen" class="flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-[11px] font-semibold text-gray-700 transition-all">
                                <ArrowUpDown class="w-3.5 h-3.5" />
                                <span class="hidden sm:inline">{{ currentSort }}</span>
                                <ChevronDown :class="['w-3 h-3 transition-transform', sortOpen && 'rotate-180']" />
                            </button>
                            <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                                <div v-if="sortOpen" class="absolute right-0 mt-1.5 w-36 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-50 overflow-hidden">
                                    <button v-for="option in sortOptions" :key="option.key" @click="setSort(option.key)" :class="['flex items-center gap-1.5 w-full px-3 py-2 text-[11px] font-medium text-left transition-colors', filters.sort === option.key ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50']">
                                        <span>{{ option.icon }}</span>
                                        {{ option.label }}
                                    </button>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="filterModalOpen" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm" @click="filterModalOpen = false"></div>
            </Transition>
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
                <div v-if="filterModalOpen" class="fixed inset-x-4 bottom-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 z-50 w-auto md:w-full md:max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center"><SlidersHorizontal class="w-4 h-4 text-white" /></div>
                            <div><h3 class="text-white font-bold text-sm">Filter</h3><p class="text-white/70 text-[10px]">Sesuaikan pencarian</p></div>
                        </div>
                        <button @click="filterModalOpen = false" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center"><X class="w-4 h-4 text-white" /></button>
                    </div>
                    <div class="p-5 space-y-5 max-h-[50vh] overflow-y-auto">
                        <div>
                            <label class="text-[11px] font-bold text-gray-900 mb-2 block">Rentang Harga</label>
                            <div class="flex items-center gap-3">
                                <input v-model.number="priceMin" type="number" placeholder="Min" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-[11px] focus:outline-none focus:border-emerald-400">
                                <span class="text-gray-300">—</span>
                                <input v-model.number="priceMax" type="number" placeholder="Max" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-[11px] focus:outline-none focus:border-emerald-400">
                            </div>
                        </div>
                        <div v-if="allFacilities.length > 0">
                            <label class="text-[11px] font-bold text-gray-900 mb-2 block">Fasilitas</label>
                            <div class="flex flex-wrap gap-1.5">
                                <button v-for="facility in allFacilities" :key="facility" @click="selectedFacilities.includes(facility) ? selectedFacilities = selectedFacilities.filter(f => f !== facility) : selectedFacilities.push(facility)" :class="['px-2.5 py-1.5 rounded-lg text-[10px] font-medium transition-all border', selectedFacilities.includes(facility) ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100']">{{ facility }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <button @click="clearFilters" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-lg text-[11px] font-semibold text-gray-700 hover:bg-white transition-all">Reset</button>
                        <button @click="applyFilters" class="flex-1 px-3 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-lg text-[11px] font-bold shadow-md transition-all">Terapkan</button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>

<style scoped>
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
</style>
