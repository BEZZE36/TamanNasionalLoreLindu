<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Database, Download, Trash2, Upload, HardDrive, Calendar, FileText, 
    RefreshCw, Table, BarChart, Server, CheckCircle, AlertTriangle,
    Loader2, Sparkles, CloudUpload, Shield, Zap, Clock, Settings, Save, Power, ChevronDown
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    backups: Array,
    stats: Object
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Local reactive copies
const localBackups = ref(JSON.parse(JSON.stringify(props.backups || [])));
const localStats = ref(JSON.parse(JSON.stringify(props.stats || {})));

// Sync with props changes
watch(() => props.backups, (newVal) => {
    localBackups.value = JSON.parse(JSON.stringify(newVal || []));
}, { deep: true });

watch(() => props.stats, (newVal) => {
    localStats.value = JSON.parse(JSON.stringify(newVal || {}));
}, { deep: true });

// State
const creating = ref(false);
const isDeleting = ref(null);
const showDeleteModal = ref(false);
const selectedBackup = ref(null);
const showImportModal = ref(false);
const importForm = useForm({ file: null, import_mode: 'full', clear_existing: false });
const importFileName = ref('');
let ctx;
let refreshInterval = null;
const isRefreshing = ref(false);
const lastRefresh = ref(new Date());

// Auto Backup State
const autoBackupLoading = ref(true);
const autoBackupSaving = ref(false);
const autoBackupSettings = ref({
    enabled: false,
    interval_months: 0,
    interval_weeks: 0,
    interval_days: 1,
    interval_hours: 0,
    interval_minutes: 0,
    last_run: null,
    next_run: null,
    max_files: 10
});
const autoBackupMessage = ref({ type: '', text: '' });
const autoBackupCollapsed = ref(localStorage.getItem('autoBackupCollapsed') === 'true');
const backupsCollapsed = ref(localStorage.getItem('backupsCollapsed') === 'true');

// Watch collapse states and persist to localStorage
watch(autoBackupCollapsed, (val) => localStorage.setItem('autoBackupCollapsed', val));
watch(backupsCollapsed, (val) => localStorage.setItem('backupsCollapsed', val));

// GSAP Animations
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        
        gsap.fromTo('.backup-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { 
    if (ctx) ctx.revert(); 
    if (refreshInterval) clearInterval(refreshInterval);
});

// Auto-refresh backups list
const refreshBackups = async (silent = false) => {
    if (!silent) isRefreshing.value = true;
    try {
        const response = await fetch('/admin/database/api/list');
        if (response.ok) {
            const data = await response.json();
            localBackups.value = data.backups || [];
            localStats.value = data.stats || {};
            lastRefresh.value = new Date();
        }
    } catch (e) {
        console.error('Failed to refresh backups', e);
    } finally {
        isRefreshing.value = false;
    }
};

// Start polling on mount
onMounted(() => {
    // Refresh every 30 seconds
    refreshInterval = setInterval(() => refreshBackups(true), 30000);
});

// Create Backup
const createBackup = () => {
    creating.value = true;
    router.post('/admin/database/backup', {}, { 
        preserveScroll: true,
        onFinish: () => creating.value = false 
    });
};

// Delete Modal Handlers
const openDeleteModal = (backup) => {
    selectedBackup.value = backup;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedBackup.value = null;
};

const confirmDelete = () => {
    if (!selectedBackup.value) return;
    isDeleting.value = selectedBackup.value.filename;
    
    // Optimistic update
    const index = localBackups.value.findIndex(b => b.filename === selectedBackup.value.filename);
    let removedBackup = null;
    if (index !== -1) {
        removedBackup = localBackups.value.splice(index, 1)[0];
    }
    
    router.delete(`/admin/database/backup/${selectedBackup.value.filename}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = null;
            closeDeleteModal();
        },
        onError: () => {
            // Revert on error
            if (removedBackup && index !== -1) {
                localBackups.value.splice(index, 0, removedBackup);
            }
        }
    });
};

// Import Handlers
const openImportModal = () => {
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
    importForm.file = null;
    importForm.import_mode = 'full';
    importForm.clear_existing = false;
    importFileName.value = '';
};

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) {
        importForm.file = file;
        importFileName.value = file.name;
    }
};

const submitImport = () => {
    if (!importForm.file) return;
    
    importForm.post('/admin/database/import', { 
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeImportModal();
        }
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Auto Backup Functions
const fetchAutoBackupSettings = async () => {
    autoBackupLoading.value = true;
    try {
        const response = await fetch('/admin/database/auto-backup-settings');
        if (response.ok) {
            const data = await response.json();
            autoBackupSettings.value = data;
        }
    } catch (e) {
        console.error('Failed to fetch auto backup settings', e);
    } finally {
        autoBackupLoading.value = false;
    }
};

const saveAutoBackupSettings = async () => {
    autoBackupSaving.value = true;
    autoBackupMessage.value = { type: '', text: '' };
    
    try {
        const response = await fetch('/admin/database/auto-backup-settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify(autoBackupSettings.value)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            autoBackupSettings.value.next_run = data.next_run;
            autoBackupMessage.value = { type: 'success', text: data.message };
            setTimeout(() => autoBackupMessage.value = { type: '', text: '' }, 3000);
        } else {
            autoBackupMessage.value = { type: 'error', text: 'Gagal menyimpan pengaturan.' };
        }
    } catch (e) {
        autoBackupMessage.value = { type: 'error', text: 'Terjadi kesalahan saat menyimpan.' };
    } finally {
        autoBackupSaving.value = false;
    }
};

// Fetch auto backup settings on mount
onMounted(() => {
    fetchAutoBackupSettings();
});
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-violet-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-violet-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-indigo-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-violet-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Icon with Glow -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Database class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Manajemen Database</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ localBackups.length }} Backup
                            </span>
                        </div>
                        <p class="text-indigo-100/80 text-xs">Backup dan restore database sistem Anda</p>
                    </div>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center gap-2">
                    <button 
                        @click="openImportModal"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/20 backdrop-blur-sm text-white text-xs font-bold hover:bg-white/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 ring-1 ring-white/30"
                    >
                        <Upload class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        <span>Import Database</span>
                    </button>
                    <button 
                        @click="createBackup"
                        :disabled="creating"
                        class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-indigo-600 text-xs font-bold hover:bg-indigo-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg disabled:opacity-50"
                    >
                        <RefreshCw :class="['w-4 h-4', creating ? 'animate-spin' : 'group-hover:rotate-180 transition-transform duration-500']" />
                        <span>{{ creating ? 'Membuat...' : 'Buat Backup' }}</span>
                        <Sparkles class="w-3.5 h-3.5 text-amber-500" />
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

        <Transition name="slide">
            <div v-if="flash.error" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 border border-red-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-400 to-rose-500 shadow-lg">
                    <AlertTriangle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-red-800">{{ flash.error }}</p>
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Tables Count -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                        <Table class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.tables_count || 0 }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Tabel</p>
                    </div>
                </div>
            </div>
            
            <!-- Database Size -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <BarChart class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.size_mb || 0 }} MB</p>
                        <p class="text-[10px] text-gray-500 font-medium">Ukuran Database</p>
                    </div>
                </div>
            </div>
            
            <!-- Total Backups -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <HardDrive class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localBackups.length }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Backup</p>
                    </div>
                </div>
            </div>
            
            <!-- Connection -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <Server class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900 uppercase">{{ localStats.connection || 'MySQL' }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Koneksi DB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="rounded-xl bg-gradient-to-r from-indigo-50 via-purple-50 to-violet-50 border border-indigo-200/60 p-4 flex items-start gap-3 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 shadow-lg flex-shrink-0">
                <Shield class="w-4 h-4 text-white" />
            </div>
            <div>
                <h4 class="text-xs font-bold text-indigo-900 mb-1">Informasi Backup & Import</h4>
                <p class="text-[11px] text-indigo-700 leading-relaxed">
                    Backup database secara berkala untuk melindungi data. Saat import, data yang sudah ada akan <strong>diganti</strong> dengan data baru dari file backup. 
                    File backup disimpan di <code class="bg-indigo-100 px-1.5 py-0.5 rounded text-[10px] font-mono">storage/app/backups/</code>
                </p>
            </div>
        </div>

        <!-- Auto Backup Settings Section -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div 
                @click="autoBackupCollapsed = !autoBackupCollapsed"
                class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 flex items-center justify-between cursor-pointer hover:bg-gradient-to-r hover:from-amber-100 hover:via-orange-100 hover:to-amber-100 transition-all duration-300"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30">
                        <Clock class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Auto Backup</h3>
                        <p class="text-[10px] text-gray-500">Jadwalkan backup otomatis</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Toggle (stop propagation to prevent collapse when clicking toggle) -->
                    <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                        <input type="checkbox" v-model="autoBackupSettings.enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-amber-500 peer-checked:to-orange-500"></div>
                        <span class="ml-2 text-xs font-semibold" :class="autoBackupSettings.enabled ? 'text-amber-600' : 'text-gray-400'">
                            {{ autoBackupSettings.enabled ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </label>
                    <!-- Collapse Icon -->
                    <ChevronDown 
                        :class="[
                            'w-5 h-5 text-gray-400 transition-transform duration-300',
                            autoBackupCollapsed ? '-rotate-90' : 'rotate-0'
                        ]" 
                    />
                </div>
            </div>
            
            <!-- Collapsible Content -->
            <Transition name="collapse">
            <div v-show="!autoBackupCollapsed">
                <!-- Loading State -->
                <div v-if="autoBackupLoading" class="p-6 flex items-center justify-center">
                    <Loader2 class="w-6 h-6 text-amber-500 animate-spin" />
                    <span class="ml-2 text-sm text-gray-500">Memuat pengaturan...</span>
                </div>
                
                <!-- Settings Content -->
                <div v-else class="p-5 space-y-5">
                <!-- Interval Settings -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-3">Interval Backup</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <!-- Months -->
                        <div class="relative">
                            <input 
                                type="number" 
                                v-model.number="autoBackupSettings.interval_months"
                                min="0" max="12"
                                class="w-full px-3 py-2.5 pr-12 rounded-xl border border-gray-200 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-gray-400">Bulan</span>
                        </div>
                        <!-- Weeks -->
                        <div class="relative">
                            <input 
                                type="number" 
                                v-model.number="autoBackupSettings.interval_weeks"
                                min="0" max="52"
                                class="w-full px-3 py-2.5 pr-14 rounded-xl border border-gray-200 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-gray-400">Minggu</span>
                        </div>
                        <!-- Days -->
                        <div class="relative">
                            <input 
                                type="number" 
                                v-model.number="autoBackupSettings.interval_days"
                                min="0" max="365"
                                class="w-full px-3 py-2.5 pr-10 rounded-xl border border-gray-200 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-gray-400">Hari</span>
                        </div>
                        <!-- Hours -->
                        <div class="relative">
                            <input 
                                type="number" 
                                v-model.number="autoBackupSettings.interval_hours"
                                min="0" max="23"
                                class="w-full px-3 py-2.5 pr-10 rounded-xl border border-gray-200 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-gray-400">Jam</span>
                        </div>
                        <!-- Minutes -->
                        <div class="relative">
                            <input 
                                type="number" 
                                v-model.number="autoBackupSettings.interval_minutes"
                                min="0" max="59"
                                class="w-full px-3 py-2.5 pr-12 rounded-xl border border-gray-200 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-gray-400">Menit</span>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-gray-400">Backup akan dijalankan setiap interval yang Anda tentukan di atas. Minimal 1 nilai harus diisi.</p>
                </div>
                
                <!-- Max Files -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Maksimal File Backup</label>
                    <div class="relative w-40">
                        <input 
                            type="number" 
                            v-model.number="autoBackupSettings.max_files"
                            min="1" max="100"
                            class="w-full px-3 py-2.5 pr-10 rounded-xl border border-gray-200 text-sm font-semibold text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-medium text-gray-400">file</span>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-400">File backup lama akan dihapus otomatis jika melebihi batas ini.</p>
                </div>
                
                <!-- Status Info -->
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 border border-gray-100">
                        <Calendar class="w-4 h-4 text-gray-400" />
                        <div>
                            <p class="text-[10px] text-gray-400 font-medium">Backup Terakhir</p>
                            <p class="text-xs font-semibold text-gray-700">{{ autoBackupSettings.last_run ? formatDate(autoBackupSettings.last_run) : 'Belum pernah' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg" :class="autoBackupSettings.enabled ? 'bg-amber-50 border border-amber-100' : 'bg-gray-50 border border-gray-100'">
                        <Clock class="w-4 h-4" :class="autoBackupSettings.enabled ? 'text-amber-500' : 'text-gray-400'" />
                        <div>
                            <p class="text-[10px] font-medium" :class="autoBackupSettings.enabled ? 'text-amber-500' : 'text-gray-400'">Backup Berikutnya</p>
                            <p class="text-xs font-semibold" :class="autoBackupSettings.enabled ? 'text-amber-700' : 'text-gray-700'">
                                {{ autoBackupSettings.enabled && autoBackupSettings.next_run ? formatDate(autoBackupSettings.next_run) : 'Tidak dijadwalkan' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Message -->
                <Transition name="slide">
                    <div v-if="autoBackupMessage.text" 
                        :class="[
                            'flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold',
                            autoBackupMessage.type === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'
                        ]"
                    >
                        <CheckCircle v-if="autoBackupMessage.type === 'success'" class="w-4 h-4" />
                        <AlertTriangle v-else class="w-4 h-4" />
                        {{ autoBackupMessage.text }}
                    </div>
                </Transition>
                
                <!-- Save Button -->
                <div class="flex justify-end pt-2">
                    <button 
                        @click="saveAutoBackupSettings"
                        :disabled="autoBackupSaving"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-bold hover:shadow-lg hover:shadow-amber-500/30 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50"
                    >
                        <Loader2 v-if="autoBackupSaving" class="w-4 h-4 animate-spin" />
                        <Save v-else class="w-4 h-4" />
                        {{ autoBackupSaving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
                </div>
            </div>
            </Transition>
        </div>

        <!-- Backups Table -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div 
                @click="backupsCollapsed = !backupsCollapsed"
                class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 flex items-center justify-between cursor-pointer hover:from-gray-100 hover:via-gray-100 hover:to-gray-150 transition-all duration-300"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg">
                        <HardDrive class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Daftar Backup</h3>
                        <p class="text-[10px] text-gray-500">Kelola file backup database Anda</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Refresh Button -->
                    <button 
                        @click.stop="refreshBackups(false)"
                        :disabled="isRefreshing"
                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600 text-[10px] font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all disabled:opacity-50"
                        title="Refresh daftar backup"
                    >
                        <RefreshCw :class="['w-3 h-3', isRefreshing ? 'animate-spin' : '']" />
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                    <!-- Auto-refresh indicator -->
                    <div class="hidden md:flex items-center gap-1.5 text-[9px] text-gray-400">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        Auto 30s
                    </div>
                    <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold">
                        {{ localBackups.length }} file
                    </span>
                    <!-- Collapse Icon -->
                    <ChevronDown 
                        :class="[
                            'w-5 h-5 text-gray-400 transition-transform duration-300',
                            backupsCollapsed ? '-rotate-90' : 'rotate-0'
                        ]" 
                    />
                </div>
            </div>
            
            <!-- Collapsible Content -->
            <Transition name="collapse">
            <div v-show="!backupsCollapsed">
                <div v-if="localBackups.length" class="divide-y divide-gray-100">
                    <div 
                        v-for="(backup, index) in localBackups" 
                        :key="backup.filename" 
                        class="backup-row px-5 py-3.5 flex items-center justify-between hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/30 transition-all duration-200 group"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 group-hover:from-indigo-100 group-hover:to-purple-100 transition-colors">
                                <FileText class="w-5 h-5 text-gray-500 group-hover:text-indigo-600 transition-colors" />
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-900 font-mono">{{ backup.filename }}</div>
                                <div class="flex items-center gap-3 mt-0.5">
                                    <span class="flex items-center gap-1 text-[10px] text-gray-500">
                                        <HardDrive class="w-3 h-3" />{{ backup.size }}
                                    </span>
                                    <span class="flex items-center gap-1 text-[10px] text-gray-500">
                                        <Calendar class="w-3 h-3" />{{ backup.date }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <a 
                                :href="`/admin/database/backup/${backup.filename}/download`" 
                                class="p-2 rounded-lg text-blue-500 hover:bg-blue-50 hover:shadow-sm transition-all duration-200"
                                title="Download"
                            >
                                <Download class="w-4 h-4" />
                            </a>
                            <button 
                                @click="openDeleteModal(backup)"
                                class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:shadow-sm transition-all duration-200"
                                title="Hapus"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                            <Database class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada backup</p>
                        <p class="text-xs text-gray-400 mb-4">Klik tombol "Buat Backup" untuk membuat backup pertama</p>
                        <button 
                            @click="createBackup"
                            :disabled="creating"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-xs font-bold hover:shadow-lg transition-all disabled:opacity-50"
                        >
                            <RefreshCw :class="['w-4 h-4', creating ? 'animate-spin' : '']" />
                            {{ creating ? 'Membuat...' : 'Buat Backup Sekarang' }}
                        </button>
                    </div>
                </div>
            </div>
            </Transition>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDeleteModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Backup</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus backup <strong class="text-gray-900 font-mono">"{{ selectedBackup?.filename }}"</strong>?
                        </p>
                        
                        <div class="flex gap-3 justify-end">
                            <button 
                                @click="closeDeleteModal"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                @click="confirmDelete"
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

        <!-- Import Database Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeImportModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 transform transition-all">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100">
                                <CloudUpload class="w-6 h-6 text-indigo-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Import Database</h3>
                                <p class="text-xs text-gray-500">Upload file backup SQL untuk restore database</p>
                            </div>
                        </div>

                        <!-- Import Mode Selection -->
                        <div class="mb-4 space-y-3">
                            <label class="block text-xs font-semibold text-gray-700">Mode Import</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label 
                                    :class="[
                                        'relative flex flex-col items-center p-4 rounded-xl border-2 cursor-pointer transition-all',
                                        importForm.import_mode === 'full' 
                                            ? 'border-indigo-500 bg-indigo-50' 
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <input type="radio" v-model="importForm.import_mode" value="full" class="sr-only">
                                    <Database class="w-6 h-6 mb-2" :class="importForm.import_mode === 'full' ? 'text-indigo-600' : 'text-gray-400'" />
                                    <span class="text-xs font-bold" :class="importForm.import_mode === 'full' ? 'text-indigo-900' : 'text-gray-700'">Full Import</span>
                                    <span class="text-[10px] text-center mt-1" :class="importForm.import_mode === 'full' ? 'text-indigo-600' : 'text-gray-500'">Ganti schema + data</span>
                                </label>
                                <label 
                                    :class="[
                                        'relative flex flex-col items-center p-4 rounded-xl border-2 cursor-pointer transition-all',
                                        importForm.import_mode === 'data_only' 
                                            ? 'border-emerald-500 bg-emerald-50' 
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <input type="radio" v-model="importForm.import_mode" value="data_only" class="sr-only">
                                    <Table class="w-6 h-6 mb-2" :class="importForm.import_mode === 'data_only' ? 'text-emerald-600' : 'text-gray-400'" />
                                    <span class="text-xs font-bold" :class="importForm.import_mode === 'data_only' ? 'text-emerald-900' : 'text-gray-700'">Data Only</span>
                                    <span class="text-[10px] text-center mt-1" :class="importForm.import_mode === 'data_only' ? 'text-emerald-600' : 'text-gray-500'">Hanya import data</span>
                                </label>
                            </div>
                        </div>

                        <!-- Clear Existing (only for data_only mode) -->
                        <Transition name="slide">
                            <div v-if="importForm.import_mode === 'data_only'" class="mb-4">
                                <label class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                                    <input type="checkbox" v-model="importForm.clear_existing" class="w-4 h-4 text-indigo-600 rounded">
                                    <div>
                                        <span class="text-xs font-semibold text-gray-700">Hapus data lama sebelum import</span>
                                        <p class="text-[10px] text-gray-500">Data di tabel akan dihapus terlebih dahulu</p>
                                    </div>
                                </label>
                            </div>
                        </Transition>

                        <!-- Warning based on mode -->
                        <div :class="[
                            'mb-5 p-3 rounded-xl border flex items-start gap-2',
                            importForm.import_mode === 'full' ? 'bg-amber-50 border-amber-200' : 'bg-blue-50 border-blue-200'
                        ]">
                            <AlertTriangle class="w-4 h-4 flex-shrink-0 mt-0.5" :class="importForm.import_mode === 'full' ? 'text-amber-600' : 'text-blue-600'" />
                            <p class="text-[11px]" :class="importForm.import_mode === 'full' ? 'text-amber-800' : 'text-blue-800'">
                                <template v-if="importForm.import_mode === 'full'">
                                    <strong>Full Import:</strong> Semua data dan struktur tabel akan diganti dengan data dari backup. Pastikan backup terlebih dahulu!
                                </template>
                                <template v-else>
                                    <strong>Data Only:</strong> Hanya data yang akan diimport. Struktur tabel (schema) akan tetap seperti sekarang. Cocok untuk import backup lama.
                                </template>
                            </p>
                        </div>
                        
                        <form @submit.prevent="submitImport" class="space-y-4">
                            <!-- File Upload -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2">File Backup (.sql)</label>
                                <div 
                                    class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition-colors cursor-pointer"
                                    @click="$refs.fileInput.click()"
                                >
                                    <input 
                                        ref="fileInput"
                                        type="file" 
                                        @change="handleFileSelect"
                                        accept=".sql"
                                        class="hidden"
                                    >
                                    <div v-if="!importFileName" class="space-y-2">
                                        <Upload class="w-8 h-8 mx-auto text-gray-400" />
                                        <p class="text-xs text-gray-500">Klik untuk pilih file atau drag & drop</p>
                                        <p class="text-[10px] text-gray-400">Hanya file .sql yang didukung</p>
                                    </div>
                                    <div v-else class="space-y-2">
                                        <FileText class="w-8 h-8 mx-auto text-indigo-500" />
                                        <p class="text-xs font-semibold text-indigo-600">{{ importFileName }}</p>
                                        <p class="text-[10px] text-gray-400">Klik untuk ganti file</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3 justify-end pt-2">
                                <button 
                                    type="button"
                                    @click="closeImportModal"
                                    class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="!importForm.file || importForm.processing"
                                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                                >
                                    <Loader2 v-if="importForm.processing" class="w-4 h-4 animate-spin" />
                                    <Zap v-else class="w-4 h-4" />
                                    {{ importForm.processing ? 'Mengimport...' : 'Import Sekarang' }}
                                </button>
                            </div>
                        </form>
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

.collapse-enter-active, .collapse-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}
.collapse-enter-from, .collapse-leave-to {
    opacity: 0;
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
}
.collapse-enter-to, .collapse-leave-from {
    opacity: 1;
    max-height: 500px;
}
</style>

