<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    Search, X, MapPin, Leaf, Bird, Image, Megaphone, FileText, Newspaper, 
    Mail, Settings, ClipboardList, Ticket, Users, QrCode, Clock, BarChart3, 
    UserCircle, Shield, Database, LayoutDashboard, ArrowRight
} from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close']);

const searchQuery = ref('');
const searchInputRef = ref(null);
const isLoading = ref(false);
const menuResults = ref([]);
const contentResults = ref([]);
const selectedIndex = ref(0);

// Icon mapping
const iconMap = {
    'layout-dashboard': LayoutDashboard,
    'map-pin': MapPin,
    'leaf': Leaf,
    'bird': Bird,
    'image': Image,
    'megaphone': Megaphone,
    'file-text': FileText,
    'newspaper': Newspaper,
    'mail': Mail,
    'settings': Settings,
    'clipboard-list': ClipboardList,
    'ticket': Ticket,
    'users': Users,
    'qr-code': QrCode,
    'clock': Clock,
    'bar-chart-3': BarChart3,
    'user-circle': UserCircle,
    'shield': Shield,
    'database': Database,
};

// Group icon colors
const groupColors = {
    'Destinasi': 'from-emerald-500 to-teal-600',
    'Flora': 'from-lime-500 to-green-600',
    'Fauna': 'from-amber-500 to-orange-600',
    'Gallery': 'from-pink-500 to-rose-600',
    'Pengumuman': 'from-orange-500 to-red-600',
    'Artikel': 'from-indigo-500 to-violet-600',
    'Berita': 'from-red-500 to-rose-600',
    'Newsletter': 'from-teal-500 to-cyan-600',
    'Pengguna': 'from-violet-500 to-purple-600',
    'Pemesanan': 'from-sky-500 to-blue-600',
    'Admin': 'from-slate-500 to-gray-600',
    'Tiket': 'from-purple-500 to-indigo-600',
};

// All results combined
const allResults = computed(() => {
    const menus = menuResults.value.map(m => ({ ...m, type: 'menu' }));
    const contents = contentResults.value;
    return [...menus, ...contents];
});

// Debounced search
let searchTimeout = null;
const searchError = ref('');

const performSearch = async () => {
    if (searchQuery.value.length < 2) {
        menuResults.value = [];
        contentResults.value = [];
        isLoading.value = false;
        searchError.value = '';
        return;
    }

    isLoading.value = true;
    selectedIndex.value = 0;
    searchError.value = '';

    try {
        const response = await axios.get('/admin/global-search', {
            params: { q: searchQuery.value },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.data) {
            menuResults.value = Array.isArray(response.data.menus) ? response.data.menus : [];
            contentResults.value = Array.isArray(response.data.results) ? response.data.results : [];
        }
    } catch (error) {
        console.error('Search error:', error);
        searchError.value = error.response?.data?.message || error.message || 'Terjadi kesalahan';
        menuResults.value = [];
        contentResults.value = [];
    } finally {
        isLoading.value = false;
    }
};

watch(searchQuery, (newVal) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    if (newVal.length < 2) {
        menuResults.value = [];
        contentResults.value = [];
        isLoading.value = false;
        return;
    }
    isLoading.value = true;
    searchTimeout = setTimeout(performSearch, 300);
});

// Focus input when modal opens
watch(() => props.show, (newVal) => {
    if (newVal) {
        searchQuery.value = '';
        menuResults.value = [];
        contentResults.value = [];
        selectedIndex.value = 0;
        setTimeout(() => searchInputRef.value?.focus(), 100);
    }
});

// Navigate to result
const navigateTo = (item) => {
    emit('close');
    const url = item.url || item.href;
    if (url) {
        router.visit(url);
    }
};

// Keyboard navigation
const handleKeydown = (e) => {
    if (!props.show) return;
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, allResults.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (allResults.value[selectedIndex.value]) {
            navigateTo(allResults.value[selectedIndex.value]);
        }
    } else if (e.key === 'Escape') {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const getIcon = (iconName) => {
    return iconMap[iconName] || Search;
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="fixed inset-0 z-[100] flex items-start justify-center pt-[10vh] sm:pt-[15vh]">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')"></div>
                
                <!-- Modal -->
                <div class="relative w-full max-w-2xl mx-3 sm:mx-4 bg-gradient-to-b from-slate-900 to-slate-950 rounded-2xl shadow-2xl border border-white/10 overflow-hidden">
                    <!-- Search Header -->
                    <div class="flex items-center gap-3 px-4 py-4 border-b border-white/10">
                        <Search class="w-5 h-5 text-emerald-400" />
                        <input 
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari menu, destinasi, artikel, flora, fauna..."
                            class="flex-1 bg-transparent border-none outline-none text-white placeholder-slate-500 text-sm"
                        />
                        <div class="flex items-center gap-2">
                            <span v-if="isLoading" class="flex items-center gap-1.5 text-xs text-slate-500">
                                <div class="w-3 h-3 border-2 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></div>
                                Mencari...
                            </span>
                            <button @click="$emit('close')" class="text-xs text-slate-500 bg-slate-800 hover:bg-slate-700 px-2 py-1 rounded transition-colors">
                                ESC
                            </button>
                        </div>
                    </div>
                    
                    <!-- Results Container -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <!-- Loading Skeleton -->
                        <div v-if="isLoading" class="p-3 space-y-2">
                            <div v-for="i in 5" :key="i" class="flex items-center gap-3 px-3 py-3 rounded-lg bg-white/5 animate-pulse">
                                <div class="w-9 h-9 rounded-lg bg-slate-700"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-4 bg-slate-700 rounded w-2/3"></div>
                                    <div class="h-3 bg-slate-800 rounded w-1/3"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- No Query -->
                        <div v-else-if="searchQuery.length < 2" class="px-4 py-8 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-800 flex items-center justify-center">
                                <Search class="w-6 h-6 text-slate-600" />
                            </div>
                            <p class="text-slate-500 text-sm">Ketik minimal 2 karakter untuk mencari</p>
                            <p class="text-slate-600 text-xs mt-1">Cari menu, konten, pengguna, dan lainnya</p>
                        </div>
                        
                        <!-- Error -->
                        <div v-else-if="searchError" class="px-4 py-8 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-900/30 flex items-center justify-center">
                                <X class="w-6 h-6 text-red-500" />
                            </div>
                            <p class="text-red-400 text-sm">{{ searchError }}</p>
                            <p class="text-slate-600 text-xs mt-1">Coba lagi nanti</p>
                        </div>
                        
                        <!-- No Results -->
                        <div v-else-if="!isLoading && allResults.length === 0" class="px-4 py-8 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-800 flex items-center justify-center">
                                <X class="w-6 h-6 text-slate-600" />
                            </div>
                            <p class="text-slate-400 text-sm">Tidak ada hasil untuk "<span class="text-white">{{ searchQuery }}</span>"</p>
                            <p class="text-slate-600 text-xs mt-1">Coba kata kunci lain</p>
                        </div>
                        
                        <!-- Results -->
                        <div v-else class="py-2">
                            <!-- Menu Results -->
                            <div v-if="menuResults.length > 0" class="mb-2">
                                <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Menu & Navigasi
                                </div>
                                <button 
                                    v-for="(item, index) in menuResults" 
                                    :key="'menu-' + index"
                                    @click="navigateTo(item)"
                                    :class="[
                                        'w-full flex items-center gap-3 px-4 py-3 transition-all text-left',
                                        selectedIndex === index ? 'bg-emerald-500/10' : 'hover:bg-white/5'
                                    ]"
                                >
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500/20 to-teal-600/20 flex items-center justify-center">
                                        <component :is="getIcon(item.icon)" class="w-4 h-4 text-emerald-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white text-sm font-medium truncate">{{ item.name }}</p>
                                        <p class="text-slate-500 text-xs truncate">{{ item.url }}</p>
                                    </div>
                                    <ArrowRight class="w-4 h-4 text-slate-600" />
                                </button>
                            </div>
                            
                            <!-- Content Results -->
                            <div v-if="contentResults.length > 0">
                                <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider border-t border-white/5">
                                    Konten & Data
                                </div>
                                <button 
                                    v-for="(item, index) in contentResults" 
                                    :key="'content-' + index"
                                    @click="navigateTo(item)"
                                    :class="[
                                        'w-full flex items-center gap-3 px-4 py-3 transition-all text-left',
                                        selectedIndex === (menuResults.length + index) ? 'bg-violet-500/10' : 'hover:bg-white/5'
                                    ]"
                                >
                                    <div :class="['w-9 h-9 rounded-lg bg-gradient-to-br flex items-center justify-center', groupColors[item.group] || 'from-slate-500 to-gray-600']">
                                        <component :is="getIcon(item.icon)" class="w-4 h-4 text-white" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white text-sm font-medium truncate">{{ item.title }}</p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-500 text-xs truncate">{{ item.subtitle }}</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">{{ item.group }}</span>
                                        </div>
                                    </div>
                                    <ArrowRight class="w-4 h-4 text-slate-600" />
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer Tips -->
                    <div class="px-4 py-2.5 border-t border-white/5 bg-slate-900/50 flex items-center justify-between text-[10px] text-slate-600">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">↑↓</kbd>
                                Navigasi
                            </span>
                            <span class="flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Enter</kbd>
                                Buka
                            </span>
                            <span class="flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Esc</kbd>
                                Tutup
                            </span>
                        </div>
                        <span v-if="allResults.length > 0">{{ allResults.length }} hasil</span>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active {
    animation: modalIn 0.2s ease-out;
}
.modal-leave-active {
    animation: modalOut 0.15s ease-in;
}

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

@keyframes modalOut {
    from { opacity: 1; transform: scale(1) translateY(0); }
    to { opacity: 0; transform: scale(0.95) translateY(-10px); }
}
</style>
