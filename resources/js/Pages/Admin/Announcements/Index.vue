<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Bell, Plus, Search, Edit, Trash2, ChevronLeft, ChevronRight, 
    Copy, BarChart2, RotateCcw, AlertTriangle, Eye, EyeOff,
    Users, UserCheck, UserX, Globe, MousePointer, Loader2,
    Filter, Megaphone, TrendingUp, Sparkles, Calendar
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    announcements: Object,
    stats: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Local reactive state for optimistic updates
const localAnnouncements = ref(JSON.parse(JSON.stringify(props.announcements.data || [])));
const localStats = ref(JSON.parse(JSON.stringify(props.stats)));

// Sync with props
watch(() => props.announcements, (newVal) => {
    localAnnouncements.value = JSON.parse(JSON.stringify(newVal.data || []));
}, { deep: true });

watch(() => props.stats, (newVal) => {
    localStats.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

// Filter state
const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');
const status = ref(props.filters?.status || '');
const audience = ref(props.filters?.audience || '');

// UI state
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const isDeleting = ref(false);
const isToggling = ref(null);
const selectedIds = ref([]);
const selectAll = ref(false);

let ctx;

// GSAP Animations
onMounted(() => {
    nextTick(() => {
        ctx = gsap.context(() => {
            // Animate stats cards
            gsap.fromTo('.stat-card', 
                { opacity: 0, y: 20, scale: 0.95 }, 
                { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
            );
            
            // Animate table rows
            gsap.fromTo('.table-row', 
                { opacity: 0, x: -20 }, 
                { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
            );
        });
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Watch select all
watch(selectAll, (val) => {
    selectedIds.value = val ? localAnnouncements.value.map(a => a.id) : [];
});

// Search with debounce
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 500);
});

watch([type, status, audience], () => applyFilters());

const applyFilters = () => {
    router.get('/admin/announcements', {
        search: search.value || undefined,
        type: type.value || undefined,
        status: status.value || undefined,
        audience: audience.value || undefined
    }, { preserveState: true, replace: true });
};

const resetFilters = () => { 
    search.value = ''; 
    type.value = ''; 
    status.value = ''; 
    audience.value = '';
    applyFilters(); 
};

const hasActiveFilters = computed(() => search.value || type.value || status.value || audience.value);

// Actions
const confirmDelete = (item) => { 
    deleteTarget.value = item; 
    showDeleteModal.value = true; 
};

const closeModal = () => {
    showDeleteModal.value = false;
    deleteTarget.value = null;
};

const deleteItem = () => {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    
    const itemId = deleteTarget.value.id;
    const wasActive = deleteTarget.value.is_active;
    
    // Optimistic update
    const index = localAnnouncements.value.findIndex(a => a.id === itemId);
    let removedItem = null;
    if (index !== -1) {
        removedItem = localAnnouncements.value.splice(index, 1)[0];
        localStats.value.total--;
        if (wasActive) localStats.value.active--;
    }
    
    router.delete(`/admin/announcements/${itemId}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            closeModal();
        },
        onError: () => {
            // Revert on error
            if (removedItem && index !== -1) {
                localAnnouncements.value.splice(index, 0, removedItem);
                localStats.value.total++;
                if (wasActive) localStats.value.active++;
            }
        }
    });
};

const toggleStatus = (item) => {
    isToggling.value = item.id;
    
    // Optimistic update
    const index = localAnnouncements.value.findIndex(a => a.id === item.id);
    if (index !== -1) {
        const wasActive = localAnnouncements.value[index].is_active;
        localAnnouncements.value[index].is_active = !wasActive;
        
        if (wasActive) {
            localStats.value.active--;
        } else {
            localStats.value.active++;
        }
    }
    
    fetch(`/admin/announcements/${item.id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    }).then(() => {
        isToggling.value = null;
    }).catch(() => {
        // Revert on error
        if (index !== -1) {
            localAnnouncements.value[index].is_active = !localAnnouncements.value[index].is_active;
        }
        isToggling.value = null;
    });
};

const duplicate = async (item) => {
    await fetch(`/admin/announcements/${item.id}/duplicate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    router.reload();
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    if (!confirm(`Hapus ${selectedIds.value.length} pengumuman yang dipilih?`)) return;
    
    router.post('/admin/announcements/bulk-delete', {
        ids: selectedIds.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            selectAll.value = false;
        }
    });
};

// Labels and colors
const typeLabels = { 
    banner: 'Banner', 
    fullscreen: 'Fullscreen',
    popup: 'Popup', 
    floating: 'Floating', 
    marquee: 'Running Text' 
};

const typeColors = { 
    banner: 'from-blue-500 to-indigo-600', 
    fullscreen: 'from-purple-500 to-fuchsia-600',
    popup: 'from-violet-500 to-purple-600', 
    floating: 'from-pink-500 to-rose-600', 
    marquee: 'from-teal-500 to-cyan-600' 
};

const audienceLabels = {
    all: 'Semua',
    guest: 'Guest',
    authenticated: 'User Login',
    first_visit: 'Pengunjung Baru'
};

const audienceIcons = {
    all: Globe,
    guest: UserX,
    authenticated: UserCheck,
    first_visit: Sparkles
};

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 via-orange-600 to-yellow-500 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-yellow-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-orange-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-yellow-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Icon with Glow -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Bell class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Manajemen Pengumuman</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ localStats.total || 0 }} Total
                            </span>
                        </div>
                        <p class="text-amber-100/80 text-xs">Kelola banner, popup, dan notifikasi untuk pengunjung</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <Link 
                        href="/admin/announcements/statistics"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/20 backdrop-blur-sm text-white text-xs font-bold hover:bg-white/30 hover:shadow-lg transition-all duration-300"
                    >
                        <BarChart2 class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        <span>Statistik</span>
                    </Link>
                    <Link 
                        href="/admin/announcements/create"
                        class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-amber-600 text-xs font-bold hover:bg-amber-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                    >
                        <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                        <span>Buat Pengumuman</span>
                        <Sparkles class="w-3.5 h-3.5 text-yellow-500" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <Eye class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <Megaphone class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(localStats.total) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Pengumuman</p>
                    </div>
                </div>
            </div>
            
            <!-- Active -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <Eye class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(localStats.active) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Aktif Sekarang</p>
                    </div>
                </div>
            </div>
            
            <!-- Views -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(localStats.total_views) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Views</p>
                    </div>
                </div>
            </div>
            
            <!-- Clicks -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <MousePointer class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ formatNumber(localStats.total_clicks) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Clicks</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Actions Card -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3 justify-between">
                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
                        <input 
                            type="text" 
                            v-model="search"
                            placeholder="Cari pengumuman..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:bg-white transition-all duration-300"
                        />
                    </div>
                    <select 
                        v-model="type"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="banner">Banner</option>
                        <option value="fullscreen">Fullscreen</option>
                        <option value="popup">Popup</option>
                        <option value="floating">Floating</option>
                        <option value="marquee">Running Text</option>
                    </select>
                    <select 
                        v-model="status"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    <select 
                        v-model="audience"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Target</option>
                        <option value="all">Semua Pengunjung</option>
                        <option value="guest">Guest Only</option>
                        <option value="authenticated">User Login</option>
                        <option value="first_visit">Pengunjung Baru</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button 
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                    >
                        <RotateCcw class="w-4 h-4" />
                        <span>Reset</span>
                    </button>
                    <button 
                        v-if="selectedIds.length > 0"
                        @click="bulkDelete"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all"
                    >
                        <Trash2 class="w-4 h-4" />
                        <span>Hapus ({{ selectedIds.length }})</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table with Premium Styling -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left">
                                <input 
                                    type="checkbox" 
                                    v-model="selectAll"
                                    class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer"
                                />
                            </th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Pengumuman</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tipe</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Target</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Views</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="item in localAnnouncements" 
                            :key="item.id" 
                            class="table-row group hover:bg-gradient-to-r hover:from-amber-50/50 hover:to-orange-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-3">
                                <input 
                                    type="checkbox" 
                                    :value="item.id"
                                    v-model="selectedIds"
                                    class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <!-- Color Preview -->
                                    <div 
                                        :style="{ backgroundColor: item.bg_color || '#fbbf24' }" 
                                        class="w-10 h-10 rounded-xl flex items-center justify-center shadow-md flex-shrink-0"
                                    >
                                        <Bell class="w-5 h-5" :style="{ color: item.text_color || '#ffffff' }" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 text-xs line-clamp-1">{{ item.title }}</p>
                                        <p class="text-[10px] text-gray-500 line-clamp-1">{{ item.message }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r text-white shadow-sm',
                                    typeColors[item.type] || 'from-gray-500 to-gray-600'
                                ]">
                                    {{ typeLabels[item.type] || item.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <component 
                                        :is="audienceIcons[item.target_audience] || Globe" 
                                        class="w-3.5 h-3.5 text-gray-500" 
                                    />
                                    <span class="text-[10px] text-gray-600 font-medium">
                                        {{ audienceLabels[item.target_audience] || 'Semua' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button 
                                    @click="toggleStatus(item)"
                                    :disabled="isToggling === item.id"
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold transition-all duration-200',
                                        item.is_active 
                                            ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' 
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >
                                    <Loader2 v-if="isToggling === item.id" class="w-3 h-3 animate-spin" />
                                    <Eye v-else-if="item.is_active" class="w-3 h-3" />
                                    <EyeOff v-else class="w-3 h-3" />
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs font-semibold text-gray-700">{{ formatNumber(item.view_count) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button 
                                        @click="duplicate(item)" 
                                        class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200" 
                                        title="Duplikat"
                                    >
                                        <Copy class="w-4 h-4" />
                                    </button>
                                    <Link 
                                        :href="`/admin/announcements/${item.id}/edit`" 
                                        class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-all duration-200"
                                        title="Edit"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button 
                                        @click="confirmDelete(item)" 
                                        class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all duration-200"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="localAnnouncements.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-100 to-orange-200 flex items-center justify-center mb-4">
                                        <Bell class="w-8 h-8 text-amber-500" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada pengumuman</p>
                                    <p class="text-xs text-gray-400 mb-4">Buat pengumuman pertama Anda</p>
                                    <Link 
                                        href="/admin/announcements/create"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-bold hover:shadow-lg transition-all"
                                    >
                                        <Plus class="w-4 h-4" />
                                        Buat Pengumuman
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="announcements.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ announcements.from }}-{{ announcements.to }}</strong> dari <strong>{{ announcements.total }}</strong> pengumuman
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in announcements.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active 
                                ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md' 
                                : link.url 
                                    ? 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' 
                                    : 'bg-gray-50 text-gray-300 cursor-not-allowed border border-gray-100'
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <!-- Header -->
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Pengumuman</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus pengumuman <strong class="text-gray-900">"{{ deleteTarget?.title }}"</strong>?
                        </p>
                        
                        <div class="flex gap-3 justify-end">
                            <button 
                                @click="closeModal"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                @click="deleteItem"
                                :disabled="isDeleting"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
                                <Trash2 v-else class="w-4 h-4" />
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    transform: translateY(-16px);
}

.modal-enter-active, .modal-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
.modal-enter-from .relative, .modal-leave-to .relative {
    transform: scale(0.95) translateY(10px);
}
</style>
