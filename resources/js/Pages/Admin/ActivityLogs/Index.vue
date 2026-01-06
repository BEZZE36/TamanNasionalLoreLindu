<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Activity, Trash2, RefreshCw, Filter, Calendar, Search, 
    Plus, Edit, TrashIcon, LogIn, LogOut, Eye, Upload, Download,
    FileText, ChevronLeft, ChevronRight, RotateCcw
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    logs: Object,
    actions: Object,
    filters: Object
});

// Filters state
const action = ref(props.filters?.action || '');
const fromDate = ref(props.filters?.from_date || '');
const toDate = ref(props.filters?.to_date || '');
const modelType = ref(props.filters?.model_type || '');
const clearingLogs = ref(false);

// Apply filters
const applyFilters = () => {
    router.get('/admin/activity-logs', {
        action: action.value || undefined,
        from_date: fromDate.value || undefined,
        to_date: toDate.value || undefined,
        model_type: modelType.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    action.value = '';
    fromDate.value = '';
    toDate.value = '';
    modelType.value = '';
    router.get('/admin/activity-logs');
};

const clearOldLogs = () => {
    if (!confirm('Hapus log lebih dari 30 hari? Aksi ini tidak dapat dibatalkan.')) return;
    clearingLogs.value = true;
    router.post('/admin/activity-logs/clear', { days: 30 }, {
        onFinish: () => clearingLogs.value = false
    });
};

// Action icons and colors
const getActionIcon = (actionType) => {
    const icons = {
        create: Plus,
        update: Edit,
        delete: TrashIcon,
        login: LogIn,
        logout: LogOut,
        view: Eye,
        export: Upload,
        import: Download,
    };
    return icons[actionType] || FileText;
};

const getActionColor = (actionType) => {
    const colors = {
        create: 'bg-green-100 text-green-700 border-green-200',
        update: 'bg-blue-100 text-blue-700 border-blue-200',
        delete: 'bg-red-100 text-red-700 border-red-200',
        login: 'bg-purple-100 text-purple-700 border-purple-200',
        logout: 'bg-gray-100 text-gray-700 border-gray-200',
        view: 'bg-cyan-100 text-cyan-700 border-cyan-200',
        export: 'bg-orange-100 text-orange-700 border-orange-200',
        import: 'bg-yellow-100 text-yellow-700 border-yellow-200',
    };
    return colors[actionType] || 'bg-gray-100 text-gray-700 border-gray-200';
};

const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

const getModelBasename = (modelType) => {
    if (!modelType) return '';
    const parts = modelType.split('\\');
    return parts[parts.length - 1];
};

const truncate = (str, len = 60) => {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                        <Activity class="w-4 h-4 text-white" />
                    </div>
                    Activity Log
                </h1>
                <p class="text-gray-500 mt-1">Riwayat aktivitas sistem dan pengguna</p>
            </div>
            <button 
                @click="clearOldLogs"
                :disabled="clearingLogs"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-100 transition-all disabled:opacity-50">
                <Trash2 class="w-4 h-4" />
                <span>Bersihkan Log Lama</span>
            </button>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4">
                <Filter class="w-5 h-5 text-indigo-600" />
                <h3 class="font-semibold text-gray-900">Filter</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1.5">Aksi</label>
                    <select v-model="action"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">Semua Aksi</option>
                        <option v-for="(label, key) in actions" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1.5">Dari Tanggal</label>
                    <input type="date" v-model="fromDate"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1.5">Sampai Tanggal</label>
                    <input type="date" v-model="toDate"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1.5">Model</label>
                    <input type="text" v-model="modelType" placeholder="Destination, Booking..."
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                <div class="flex items-end gap-2">
                    <button @click="applyFilters"
                        class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg hover:shadow-indigo-500/30 transition-all flex items-center justify-center gap-2">
                        <Search class="w-4 h-4" />
                        Filter
                    </button>
                    <button @click="resetFilters"
                        class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all">
                        <RotateCcw class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User/Admin</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="log in logs.data" :key="log.id" 
                            class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ formatDate(log.created_at) }}</div>
                                <div class="text-xs text-gray-500">{{ formatTime(log.created_at) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border', getActionColor(log.action)]">
                                    <component :is="getActionIcon(log.action)" class="w-3.5 h-3.5" />
                                    {{ log.action_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-900">{{ truncate(log.description) }}</p>
                                <p v-if="log.model_type" class="text-xs text-gray-500 mt-0.5">
                                    {{ getModelBasename(log.model_type) }} #{{ log.model_id }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="log.admin" class="text-sm font-medium text-purple-600">{{ log.admin.name }}</span>
                                <span v-else-if="log.user" class="text-sm font-medium text-gray-700">{{ log.user.name }}</span>
                                <span v-else class="text-sm text-gray-400">System</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-500 font-mono">{{ log.ip_address || '-' }}</span>
                            </td>
                        </tr>
                        <tr v-if="!logs.data?.length">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-4">
                                        <FileText class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-gray-500 font-medium">Belum ada activity log</p>
                                    <p class="text-gray-400 text-sm mt-1">Aktivitas pengguna akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.meta?.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">
                    Menampilkan {{ logs.meta.from }} - {{ logs.meta.to }} dari {{ logs.meta.total }} log
                </p>
                <div class="flex items-center gap-2">
                    <Link 
                        v-if="logs.links?.prev"
                        :href="logs.links.prev"
                        class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                        <ChevronLeft class="w-5 h-5" />
                    </Link>
                    <span class="px-4 py-2 text-sm font-medium text-gray-700">
                        {{ logs.meta?.current_page }} / {{ logs.meta?.last_page }}
                    </span>
                    <Link 
                        v-if="logs.links?.next"
                        :href="logs.links.next"
                        class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                        <ChevronRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
