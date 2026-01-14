<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Activity, Search, Filter, Download, Trash2, Calendar, Loader2, 
    Plus, Edit, Trash, LogIn, LogOut, Eye, Upload, FileUp, Send, 
    QrCode, CheckCircle, ToggleRight, Database, Settings, Clock,
    ChevronLeft, ChevronRight, RotateCcw, X, AlertTriangle,
    TrendingUp, FileText, Sparkles, Info
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    logs: Object,
    actions: Object,
    filters: Object,
    stats: Object,
    modelTypes: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Filter state
const search = ref(props.filters?.search || '');
const action = ref(props.filters?.action || '');
const fromDate = ref(props.filters?.from_date || '');
const toDate = ref(props.filters?.to_date || '');
const modelType = ref(props.filters?.model_type || '');

// UI state
const clearingLogs = ref(false);
const selectedLog = ref(null);
const showDetailModal = ref(false);
const showClearModal = ref(false);
const clearDays = ref(30);
let ctx;

// Export URL computed
const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (action.value) params.append('action', action.value);
    if (fromDate.value) params.append('from_date', fromDate.value);
    if (toDate.value) params.append('to_date', toDate.value);
    if (modelType.value) params.append('model_type', modelType.value);
    const queryString = params.toString();
    return `/admin/activity-logs/export${queryString ? '?' + queryString : ''}`;
});

// Animation on mount
onMounted(() => {
    ctx = gsap.context(() => {
        // Animate header
        gsap.fromTo('.header-content', 
            { opacity: 0, y: -20 }, 
            { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' }
        );
        
        // Animate stats cards
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08, delay: 0.2, ease: 'back.out(1.7)' }
        );
        
        // Animate table rows
        gsap.fromTo('.table-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.03, delay: 0.4, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Search with debounce
let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 500);
});

// Apply filters
const applyFilters = () => {
    router.get('/admin/activity-logs', {
        search: search.value || undefined,
        action: action.value || undefined,
        from_date: fromDate.value || undefined,
        to_date: toDate.value || undefined,
        model_type: modelType.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    action.value = '';
    fromDate.value = '';
    toDate.value = '';
    modelType.value = '';
    router.get('/admin/activity-logs');
};

const openClearModal = () => {
    showClearModal.value = true;
};

const closeClearModal = () => {
    showClearModal.value = false;
    clearDays.value = 30;
};

const clearOldLogs = () => {
    clearingLogs.value = true;
    router.post('/admin/activity-logs/clear', { days: clearDays.value }, {
        onFinish: () => {
            clearingLogs.value = false;
            closeClearModal();
        }
    });
};

const openDetailModal = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

const closeDetailModal = () => {
    showDetailModal.value = false;
    selectedLog.value = null;
};

// Action icons mapping
const getActionIcon = (actionType) => {
    const icons = {
        create: Plus,
        update: Edit,
        delete: Trash,
        login: LogIn,
        logout: LogOut,
        view: Eye,
        export: Upload,
        import: FileUp,
        publish: CheckCircle,
        unpublish: X,
        send: Send,
        scan: QrCode,
        checkin: CheckCircle,
        toggle: ToggleRight,
        backup: Database,
        settings: Settings,
        restore: RotateCcw,
        archive: FileText,
    };
    return icons[actionType] || FileText;
};

// Action colors mapping
const getActionColor = (actionType) => {
    const colors = {
        create: 'from-emerald-500 to-teal-600',
        update: 'from-blue-500 to-indigo-600',
        delete: 'from-red-500 to-rose-600',
        login: 'from-violet-500 to-purple-600',
        logout: 'from-gray-500 to-slate-600',
        view: 'from-cyan-500 to-blue-600',
        export: 'from-orange-500 to-amber-600',
        import: 'from-yellow-500 to-amber-600',
        publish: 'from-green-500 to-emerald-600',
        unpublish: 'from-gray-500 to-slate-600',
        send: 'from-pink-500 to-rose-600',
        scan: 'from-purple-500 to-indigo-600',
        checkin: 'from-teal-500 to-cyan-600',
        toggle: 'from-amber-500 to-orange-600',
        backup: 'from-blue-500 to-cyan-600',
        settings: 'from-slate-500 to-gray-600',
        restore: 'from-green-500 to-teal-600',
        archive: 'from-gray-500 to-slate-600',
    };
    return colors[actionType] || 'from-gray-500 to-slate-600';
};

const getActionBgColor = (actionType) => {
    const colors = {
        create: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        update: 'bg-blue-50 text-blue-700 border-blue-200',
        delete: 'bg-red-50 text-red-700 border-red-200',
        login: 'bg-violet-50 text-violet-700 border-violet-200',
        logout: 'bg-gray-50 text-gray-700 border-gray-200',
        view: 'bg-cyan-50 text-cyan-700 border-cyan-200',
        export: 'bg-orange-50 text-orange-700 border-orange-200',
        import: 'bg-yellow-50 text-yellow-700 border-yellow-200',
        publish: 'bg-green-50 text-green-700 border-green-200',
        unpublish: 'bg-gray-50 text-gray-700 border-gray-200',
        send: 'bg-pink-50 text-pink-700 border-pink-200',
        scan: 'bg-purple-50 text-purple-700 border-purple-200',
        checkin: 'bg-teal-50 text-teal-700 border-teal-200',
        toggle: 'bg-amber-50 text-amber-700 border-amber-200',
        backup: 'bg-blue-50 text-blue-700 border-blue-200',
        settings: 'bg-slate-50 text-slate-700 border-slate-200',
        restore: 'bg-green-50 text-green-700 border-green-200',
        archive: 'bg-gray-50 text-gray-700 border-gray-200',
    };
    return colors[actionType] || 'bg-gray-50 text-gray-700 border-gray-200';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getModelBasename = (modelType) => {
    if (!modelType) return '-';
    const parts = modelType.split('\\');
    return parts[parts.length - 1];
};

const truncate = (str, len = 50) => {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
};

const hasChanges = (log) => {
    return (log.old_values && Object.keys(log.old_values).length > 0) || 
           (log.new_values && Object.keys(log.new_values).length > 0);
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="header-content relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-700 via-gray-700 to-zinc-700 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-slate-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-gray-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-slate-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Icon with Glow -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Activity class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Activity Log</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ stats.total }} Total
                            </span>
                        </div>
                        <p class="text-slate-200/80 text-xs">Pantau riwayat aktivitas sistem dan pengguna</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <a 
                        :href="exportUrl"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/20 text-white text-xs font-bold hover:bg-white/30 hover:shadow-xl transition-all duration-300 backdrop-blur-sm"
                    >
                        <Download class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        <span>Export CSV</span>
                    </a>
                    <button 
                        @click="openClearModal"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-500/20 text-red-100 text-xs font-bold hover:bg-red-500/30 hover:shadow-xl transition-all duration-300 backdrop-blur-sm"
                    >
                        <Trash2 class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        <span>Bersihkan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Total -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-slate-100 to-gray-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-500 to-gray-600 shadow-lg shadow-slate-500/30 group-hover:scale-110 transition-transform">
                        <Activity class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Log</p>
                    </div>
                </div>
            </div>
            
            <!-- Today -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Clock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.today }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Hari Ini</p>
                    </div>
                </div>
            </div>
            
            <!-- This Week -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.this_week }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Minggu Ini</p>
                    </div>
                </div>
            </div>
            
            <!-- Create -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <Plus class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.create }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Membuat</p>
                    </div>
                </div>
            </div>
            
            <!-- Update -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Edit class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.update }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Memperbarui</p>
                    </div>
                </div>
            </div>
            
            <!-- Delete -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                        <Trash class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.delete }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Menghapus</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex items-center gap-2 mb-4">
                <Filter class="w-4 h-4 text-slate-600" />
                <h3 class="text-xs font-bold text-gray-900">Filter & Pencarian</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Search -->
                <div class="relative group lg:col-span-2">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-slate-500 transition-colors" />
                    <input 
                        type="text" 
                        v-model="search"
                        placeholder="Cari deskripsi..."
                        class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 focus:bg-white transition-all duration-300"
                    />
                </div>
                
                <!-- Action Filter -->
                <select 
                    v-model="action"
                    @change="applyFilters"
                    class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 focus:bg-white transition-all cursor-pointer"
                >
                    <option value="">Semua Aksi</option>
                    <option v-for="(label, key) in actions" :key="key" :value="key">{{ label }}</option>
                </select>
                
                <!-- From Date -->
                <input 
                    type="date" 
                    v-model="fromDate"
                    @change="applyFilters"
                    class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 focus:bg-white transition-all"
                    placeholder="Dari Tanggal"
                />
                
                <!-- To Date -->
                <input 
                    type="date" 
                    v-model="toDate"
                    @change="applyFilters"
                    class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 focus:bg-white transition-all"
                    placeholder="Sampai Tanggal"
                />
                
                <!-- Reset -->
                <button 
                    @click="resetFilters"
                    class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200 transition-all flex items-center justify-center gap-2"
                >
                    <RotateCcw class="w-4 h-4" />
                    Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Waktu</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">User/Admin</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">IP</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="(log, index) in logs.data" 
                            :key="log.id" 
                            class="table-row group hover:bg-gradient-to-r hover:from-slate-50/50 hover:to-gray-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <Calendar class="w-3 h-3 text-gray-400" />
                                    <div>
                                        <div class="text-xs font-medium text-gray-900">{{ formatDate(log.created_at) }}</div>
                                        <div class="text-[10px] text-gray-500">{{ formatTime(log.created_at) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border', getActionBgColor(log.action)]">
                                    <component :is="getActionIcon(log.action)" class="w-3 h-3" />
                                    {{ log.action_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-900">{{ truncate(log.description, 40) }}</p>
                                <p v-if="log.model_type" class="text-[10px] text-gray-500 mt-0.5">
                                    {{ getModelBasename(log.model_type) }} #{{ log.model_id }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="log.admin" class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-bold">
                                        {{ log.admin.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-xs font-medium text-violet-600">{{ log.admin.name }}</span>
                                </div>
                                <div v-else-if="log.user" class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white text-[10px] font-bold">
                                        {{ log.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-xs font-medium text-blue-600">{{ log.user.name }}</span>
                                </div>
                                <span v-else class="text-xs text-gray-400 italic">System</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[10px] text-gray-500 font-mono bg-gray-100 px-2 py-0.5 rounded">{{ log.ip_address || '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button 
                                    @click="openDetailModal(log)"
                                    class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all"
                                    title="Lihat Detail"
                                >
                                    <Info class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="!logs.data?.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                                        <Activity class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada activity log</p>
                                    <p class="text-xs text-gray-400">Aktivitas pengguna akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ logs.from }}-{{ logs.to }}</strong> dari <strong>{{ logs.total }}</strong> log
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in logs.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active 
                                ? 'bg-gradient-to-r from-slate-600 to-gray-700 text-white shadow-md' 
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

        <!-- Detail Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDetailModal && selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDetailModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden transform transition-all">
                        <!-- Modal Header -->
                        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div :class="['flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br shadow-lg', getActionColor(selectedLog.action)]">
                                        <component :is="getActionIcon(selectedLog.action)" class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">Detail Activity Log</h3>
                                        <p class="text-[10px] text-gray-500">{{ formatDateTime(selectedLog.created_at) }}</p>
                                    </div>
                                </div>
                                <button @click="closeDetailModal" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <X class="w-5 h-5 text-gray-400" />
                                </button>
                            </div>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="p-5 space-y-4 overflow-y-auto max-h-[60vh]">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Aksi</label>
                                    <span :class="['mt-1 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border', getActionBgColor(selectedLog.action)]">
                                        <component :is="getActionIcon(selectedLog.action)" class="w-3 h-3" />
                                        {{ selectedLog.action_label }}
                                    </span>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">User/Admin</label>
                                    <p class="mt-1 text-xs font-medium text-gray-900">
                                        {{ selectedLog.admin?.name || selectedLog.user?.name || 'System' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Model</label>
                                    <p class="mt-1 text-xs text-gray-900">
                                        {{ getModelBasename(selectedLog.model_type) || '-' }}
                                        <span v-if="selectedLog.model_id" class="text-gray-500">#{{ selectedLog.model_id }}</span>
                                    </p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">IP Address</label>
                                    <p class="mt-1 text-xs text-gray-900 font-mono">{{ selectedLog.ip_address || '-' }}</p>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Deskripsi</label>
                                <p class="mt-1 text-xs text-gray-900">{{ selectedLog.description }}</p>
                            </div>
                            
                            <!-- User Agent -->
                            <div v-if="selectedLog.user_agent">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">User Agent</label>
                                <p class="mt-1 text-[10px] text-gray-600 bg-gray-50 p-2 rounded-lg font-mono break-all">{{ selectedLog.user_agent }}</p>
                            </div>
                            
                            <!-- Changes (Old vs New Values) -->
                            <div v-if="hasChanges(selectedLog)" class="border-t pt-4">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3 block">Perubahan Data</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Old Values -->
                                    <div v-if="selectedLog.old_values && Object.keys(selectedLog.old_values).length > 0">
                                        <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                                            <h4 class="text-[10px] font-bold text-red-700 uppercase mb-2 flex items-center gap-1">
                                                <X class="w-3 h-3" /> Nilai Lama
                                            </h4>
                                            <div class="space-y-1.5">
                                                <div v-for="(value, key) in selectedLog.old_values" :key="key" class="text-[10px]">
                                                    <span class="font-semibold text-red-800">{{ key }}:</span>
                                                    <span class="text-red-700 ml-1">{{ typeof value === 'object' ? JSON.stringify(value) : value }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- New Values -->
                                    <div v-if="selectedLog.new_values && Object.keys(selectedLog.new_values).length > 0">
                                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                            <h4 class="text-[10px] font-bold text-emerald-700 uppercase mb-2 flex items-center gap-1">
                                                <CheckCircle class="w-3 h-3" /> Nilai Baru
                                            </h4>
                                            <div class="space-y-1.5">
                                                <div v-for="(value, key) in selectedLog.new_values" :key="key" class="text-[10px]">
                                                    <span class="font-semibold text-emerald-800">{{ key }}:</span>
                                                    <span class="text-emerald-700 ml-1">{{ typeof value === 'object' ? JSON.stringify(value) : value }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                            <button 
                                @click="closeDetailModal"
                                class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Clear Logs Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showClearModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeClearModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <!-- Header -->
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Bersihkan Log Lama</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-4">
                            Pilih jumlah hari untuk menghapus log yang lebih lama:
                        </p>
                        
                        <div class="flex items-center gap-3 mb-6">
                            <select 
                                v-model="clearDays"
                                class="flex-1 px-4 py-2.5 text-sm rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all"
                            >
                                <option :value="7">7 hari</option>
                                <option :value="14">14 hari</option>
                                <option :value="30">30 hari</option>
                                <option :value="60">60 hari</option>
                                <option :value="90">90 hari</option>
                            </select>
                            <span class="text-sm text-gray-500">yang lalu</span>
                        </div>
                        
                        <div class="flex gap-3 justify-end">
                            <button 
                                @click="closeClearModal"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                @click="clearOldLogs"
                                :disabled="clearingLogs"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <Loader2 v-if="clearingLogs" class="w-4 h-4 animate-spin" />
                                <Trash2 v-else class="w-4 h-4" />
                                Hapus Log Lama
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
