<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import { Search, X, Loader2, Clock, Trash2, ArrowRight, MapPin, Leaf, Bird, Image, Sparkles, TrendingUp, Compass } from 'lucide-vue-next';
import { gsap } from 'gsap';
import SearchResultSkeleton from '@/Components/Skeleton/SearchResultSkeleton.vue';

const props = defineProps({
    user: Object,
});

const isOpen = ref(false);
const query = ref('');
const results = ref([]);
const history = ref([]);
const loading = ref(false);
const searchInput = ref(null);
const modalRef = ref(null);

const isAuthenticated = computed(() => !!props.user);

let searchTimeout = null;

const openSearch = () => {
    isOpen.value = true;
    document.body.classList.add('search-modal-open');
    setTimeout(() => {
        searchInput.value?.focus();
        // Animate modal entrance
        if (modalRef.value) {
            gsap.fromTo(modalRef.value, 
                { scale: 0.9, y: -20, opacity: 0 },
                { scale: 1, y: 0, opacity: 1, duration: 0.4, ease: 'power3.out' }
            );
        }
    }, 50);
    if (isAuthenticated.value) loadHistory();
};

const closeSearch = () => {
    if (modalRef.value) {
        gsap.to(modalRef.value, {
            scale: 0.9, y: -20, opacity: 0, duration: 0.25, ease: 'power2.in',
            onComplete: () => {
                isOpen.value = false;
                query.value = '';
                results.value = [];
                document.body.classList.remove('search-modal-open');
            }
        });
    } else {
        isOpen.value = false;
        query.value = '';
        results.value = [];
        document.body.classList.remove('search-modal-open');
    }
};

const clearSearch = () => {
    query.value = '';
    results.value = [];
    searchInput.value?.focus();
};

onMounted(() => {
    window.addEventListener('open-search', openSearch);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('open-search', openSearch);
    window.removeEventListener('keydown', handleKeydown);
});

const handleKeydown = (e) => {
    if (e.key === 'Escape' && isOpen.value) closeSearch();
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        openSearch();
    }
};

watch(query, (newQuery) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    if (!newQuery || newQuery.trim().length < 2) {
        results.value = [];
        return;
    }
    searchTimeout = setTimeout(() => search(), 300);
});

const search = async () => {
    const q = query.value.trim();
    if (!q || q.length < 2) return;
    
    loading.value = true;
    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(q)}`);
        if (response.ok) {
            const data = await response.json();
            results.value = data.results || [];
        }
    } catch (e) {
        console.error('Search failed', e);
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const loadHistory = async () => {
    if (!isAuthenticated.value) return;
    try {
        const response = await fetch('/api/search/history');
        if (response.ok) {
            const data = await response.json();
            history.value = data.history || [];
        }
    } catch (e) {
        history.value = [];
    }
};

const saveToHistory = async (result) => {
    if (!isAuthenticated.value) return;
    try {
        await fetch('/api/search/history', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                query: query.value,
                result_type: result.type,
                result_id: result.id,
                result_title: result.name,
                result_url: result.url
            })
        });
    } catch (e) {
        console.error('Failed to save history', e);
    }
};

const clearHistory = async () => {
    if (!isAuthenticated.value) return;
    try {
        await fetch('/api/search/history', { 
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
        });
        history.value = [];
    } catch (e) {
        console.error('Failed to clear history', e);
    }
};

const deleteHistoryItem = async (id) => {
    if (!isAuthenticated.value) return;
    try {
        await fetch(`/api/search/history/${id}`, { 
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
        });
        history.value = history.value.filter(h => h.id !== id);
    } catch (e) {
        console.error('Failed to delete history item', e);
    }
};

const navigateToResult = (result) => {
    saveToHistory(result);
    closeSearch();
    window.location.href = result.url;
};

const navigateToFirst = () => {
    if (results.value.length > 0) navigateToResult(results.value[0]);
};

const highlightMatch = (text, searchQuery) => {
    if (!text || !searchQuery) return text || '';
    const escapedQuery = searchQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return text.replace(new RegExp(`(${escapedQuery})`, 'gi'), '<mark class="bg-gradient-to-r from-emerald-200 to-teal-200 text-emerald-900 px-0.5 rounded font-medium">$1</mark>');
};

const getResultIcon = (type) => {
    switch (type) {
        case 'destination': return MapPin;
        case 'flora': return Leaf;
        case 'fauna': return Bird;
        case 'gallery': return Image;
        default: return Search;
    }
};

const getResultGradient = (type) => {
    switch (type) {
        case 'destination': return 'from-emerald-500 to-teal-500';
        case 'flora': return 'from-green-500 to-lime-500';
        case 'fauna': return 'from-amber-500 to-orange-500';
        case 'gallery': return 'from-purple-500 to-pink-500';
        default: return 'from-gray-500 to-slate-500';
    }
};

const quickLinks = [
    { name: 'Destinasi Populer', href: '/destinations', icon: MapPin, gradient: 'from-emerald-500 to-teal-500' },
    { name: 'Flora Endemik', href: '/flora', icon: Leaf, gradient: 'from-green-500 to-lime-500' },
    { name: 'Fauna Langka', href: '/fauna', icon: Bird, gradient: 'from-amber-500 to-orange-500' },
    { name: 'Explore Map', href: '/explore/map', icon: Compass, gradient: 'from-blue-500 to-cyan-500' },
];
</script>

<template>
    <!-- Teleport modal to body to avoid blur filter from parent -->
    <Teleport to="body">
        <!-- Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isOpen" 
                @click="closeSearch"
                class="search-modal-wrapper fixed inset-0 z-[99998] bg-black/60 backdrop-blur-sm"
            ></div>
        </Transition>

    <!-- Modal -->
    <div 
        v-if="isOpen"
        class="fixed inset-0 z-[99999] flex items-start justify-center pt-[10vh] md:pt-[12vh] px-4 pointer-events-none"
    >
        <div ref="modalRef" @click.stop class="w-full max-w-2xl pointer-events-auto">
            <!-- Main Container -->
            <div class="relative bg-white rounded-3xl shadow-2xl shadow-black/20 border border-slate-200/50 overflow-hidden" style="isolation: isolate">
                
                <!-- Decorative gradient orbs (reduced blur) -->
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-gradient-to-br from-emerald-300/20 to-teal-300/10 rounded-full blur-2xl pointer-events-none" style="z-index: -1"></div>
                <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-gradient-to-br from-cyan-300/10 to-blue-300/10 rounded-full blur-2xl pointer-events-none" style="z-index: -1"></div>

                <!-- Header with Search Input -->
                <div class="relative p-6 pb-4">
                    <!-- Close Button -->
                    <button 
                        @click="closeSearch"
                        class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 text-gray-400 hover:text-red-500 transition-all duration-200 hover:scale-110 hover:rotate-90"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <!-- Search Icon & Title -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                            <Search class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Pencarian</h2>
                            <p class="text-xs text-gray-400">Temukan destinasi, flora, dan fauna</p>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative group">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 rounded-2xl opacity-0 group-focus-within:opacity-100 blur transition-all duration-300"></div>
                        <div class="relative flex items-center bg-gray-50 rounded-xl border border-gray-200 group-focus-within:bg-white group-focus-within:border-transparent transition-all duration-300">
                            <div class="pl-4">
                                <Loader2 v-if="loading" class="w-5 h-5 text-emerald-500 animate-spin" />
                                <Search v-else class="w-5 h-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors" />
                            </div>
                            <input 
                                ref="searchInput"
                                v-model="query"
                                @keydown.enter="navigateToFirst"
                                type="text"
                                placeholder="Ketik untuk mencari..."
                                class="flex-1 px-4 py-4 bg-transparent text-gray-900 placeholder-gray-400 focus:outline-none text-sm"
                                autocomplete="off"
                            >
                            <button 
                                v-if="query"
                                @click="clearSearch"
                                class="mr-3 w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all duration-200"
                            >
                                <X class="w-4 h-4" />
                            </button>
                            <div class="pr-4">
                                <kbd class="hidden md:inline-flex items-center gap-1 px-2 py-1 bg-gray-200/50 rounded-lg text-[10px] font-medium text-gray-500">
                                    ESC
                                </kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="max-h-[55vh] overflow-y-auto px-6 pb-6">

                    <!-- Search History (when no query and authenticated) -->
                    <div v-if="!query && isAuthenticated && history.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <Clock class="w-3.5 h-3.5" />
                                Riwayat Pencarian
                            </h3>
                            <button 
                                @click="clearHistory"
                                class="flex items-center gap-1 text-xs text-gray-400 hover:text-red-500 transition-colors"
                            >
                                <Trash2 class="w-3 h-3" />
                                Hapus Semua
                            </button>
                        </div>
                        <div class="space-y-1">
                            <div 
                                v-for="item in history.slice(0, 5)" 
                                :key="item.id"
                                class="group flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-all duration-200"
                            >
                                <Clock class="w-4 h-4 text-gray-300" />
                                <a 
                                    :href="item.result_url"
                                    @click="closeSearch"
                                    class="flex-1 text-sm text-gray-600 hover:text-emerald-600 font-medium"
                                >
                                    {{ item.result_title || item.query }}
                                </a>
                                <button 
                                    @click="deleteHistoryItem(item.id)"
                                    class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State (no query and no history) -->
                    <div v-if="!query && (!isAuthenticated || history.length === 0)" class="py-8 text-center">
                        <div class="relative w-20 h-20 mx-auto mb-4">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/20 to-teal-400/20 rounded-2xl blur-xl animate-pulse"></div>
                            <div class="relative w-full h-full rounded-2xl bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                <Search class="w-9 h-9 text-emerald-500" />
                            </div>
                        </div>
                        <p class="text-gray-600 font-medium">Mulai pencarian Anda</p>
                        <p class="text-xs text-gray-400 mt-1">Gunakan <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-500 font-mono">Ctrl+K</kbd> untuk membuka kapan saja</p>
                    </div>

                    <!-- Loading State - Skeleton -->
                    <div v-if="query && loading" class="py-4">
                        <SearchResultSkeleton :count="3" />
                    </div>

                    <!-- No Results -->
                    <div v-if="query && !loading && results.length === 0" class="py-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
                            <Search class="w-8 h-8 text-gray-300" />
                        </div>
                        <p class="text-gray-600 font-medium">Tidak ada hasil untuk "{{ query }}"</p>
                        <p class="text-xs text-gray-400 mt-1">Coba kata kunci yang berbeda</p>
                    </div>

                    <!-- Results -->
                    <div v-if="query && !loading && results.length > 0">
                        <p class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                            <Search class="w-3.5 h-3.5" />
                            {{ results.length }} hasil ditemukan
                        </p>
                        <div class="space-y-2">
                            <a 
                                v-for="(result, index) in results"
                                :key="result.id"
                                :href="result.url"
                                @click.prevent="navigateToResult(result)"
                                class="group flex items-center gap-4 p-4 rounded-2xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-200 hover:shadow-xl transition-all duration-300 cursor-pointer"
                                :style="{ animationDelay: `${index * 50}ms` }"
                            >
                                <!-- Image -->
                                <div class="relative flex-shrink-0">
                                    <div class="absolute -inset-1 bg-gradient-to-br rounded-xl blur opacity-0 group-hover:opacity-50 transition-opacity duration-300" :class="getResultGradient(result.type)"></div>
                                    <img 
                                        :src="result.image"
                                        :alt="result.name"
                                        class="relative w-16 h-16 rounded-xl object-cover bg-gray-200"
                                        @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                    >
                                    <div :class="['absolute -bottom-1 -right-1 w-6 h-6 rounded-lg bg-gradient-to-br flex items-center justify-center shadow-lg', getResultGradient(result.type)]">
                                        <component :is="getResultIcon(result.type)" class="w-3 h-3 text-white" />
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <p 
                                        class="font-bold text-gray-900 group-hover:text-emerald-700 truncate transition-colors"
                                        v-html="highlightMatch(result.name, query)"
                                    ></p>
                                    <p 
                                        class="text-sm text-gray-500 truncate mt-0.5"
                                        v-html="highlightMatch(result.description, query)"
                                    ></p>
                                    <span :class="['inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-gradient-to-r', getResultGradient(result.type), 'text-white']">
                                        <component :is="getResultIcon(result.type)" class="w-2.5 h-2.5" />
                                        {{ result.type_label }}
                                    </span>
                                </div>

                                <!-- Arrow -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-emerald-500 flex items-center justify-center transition-all duration-300">
                                    <ArrowRight class="w-5 h-5 text-gray-400 group-hover:text-white group-hover:translate-x-0.5 transition-all" />
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </Teleport>
</template>

<style>
body.search-modal-open {
    overflow: hidden;
}

@keyframes pulse-slow {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 0.6; }
}

.animate-pulse-slow {
    animation: pulse-slow 3s ease-in-out infinite;
}
</style>
