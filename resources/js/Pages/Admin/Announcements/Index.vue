<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Bell, Plus, Search, Edit, Trash2, ChevronLeft, ChevronRight, Eye, EyeOff, Copy, Power, BarChart2, RotateCcw, AlertTriangle } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    announcements: Object,
    stats: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || '');
const status = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/announcements', {
        search: search.value || undefined,
        type: type.value || undefined,
        status: status.value || undefined
    }, { preserveState: true });
};

const resetFilters = () => { search.value = ''; type.value = ''; status.value = ''; applyFilters(); };

const confirmDelete = (item) => { deleteTarget.value = item; showDeleteModal.value = true; };
const deleteItem = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/announcements/${deleteTarget.value.id}`, {
            onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; }
        });
    }
};

const toggleStatus = async (item) => {
    await fetch(`/admin/announcements/${item.id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    router.reload();
};

const duplicate = async (item) => {
    await fetch(`/admin/announcements/${item.id}/duplicate`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    router.reload();
};

const typeLabels = { banner: 'Banner', popup: 'Popup', floating: 'Floating', marquee: 'Running Text' };
const typeColors = { banner: 'bg-blue-100 text-blue-700', popup: 'bg-purple-100 text-purple-700', floating: 'bg-pink-100 text-pink-700', marquee: 'bg-teal-100 text-teal-700' };
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <Bell class="w-7 h-7 text-amber-600" />Manajemen Pengumuman
                </h1>
                <p class="text-gray-500 text-sm mt-1">Kelola banner, popup, dan notifikasi</p>
            </div>
            <div class="flex gap-2">
                <Link href="/admin/announcements/statistics" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2">
                    <BarChart2 class="w-5 h-5" />Statistik
                </Link>
                <Link href="/admin/announcements/create"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-bold rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 transition-all">
                    <Plus class="w-4 h-4" />Buat Pengumuman
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <p class="text-[11px] text-gray-500">Total</p>
                <p class="text-lg font-bold text-gray-900">{{ stats.total || 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <p class="text-[11px] text-gray-500">Aktif</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.active || 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <p class="text-[11px] text-gray-500">Views</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.total_views || 0 }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <p class="text-[11px] text-gray-500">Clicks</p>
                <p class="text-2xl font-bold text-amber-600">{{ stats.total_clicks || 0 }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari pengumuman..."
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                </div>
                <select v-model="type" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                    <option value="">Semua Tipe</option>
                    <option value="banner">Banner</option>
                    <option value="popup">Popup</option>
                    <option value="floating">Floating</option>
                    <option value="marquee">Running Text</option>
                </select>
                <select v-model="status" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
                <button v-if="search || type || status" @click="resetFilters"
                    class="px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2">
                    <RotateCcw class="w-4 h-4" />Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pengumuman</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Views</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in announcements.data" :key="item.id" class="hover:bg-amber-50/30 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div :style="{ backgroundColor: item.bg_color || '#fbbf24' }" class="w-10 h-10 rounded-xl flex items-center justify-center">
                                        <Bell class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 line-clamp-1">{{ item.title }}</p>
                                        <p class="text-xs text-gray-500 line-clamp-1">{{ item.message }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-bold', typeColors[item.type] || 'bg-gray-100 text-gray-600']">
                                    {{ typeLabels[item.type] || item.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="toggleStatus(item)" :class="['px-3 py-1.5 rounded-full text-xs font-bold transition-colors', item.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-medium text-gray-600">{{ item.view_count || 0 }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="duplicate(item)" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Duplikat">
                                        <Copy class="w-4 h-4" />
                                    </button>
                                    <Link :href="`/admin/announcements/${item.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(item)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!announcements.data?.length">
                            <td colspan="5" class="px-4 py-16 text-center">
                                <Bell class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                <p class="font-medium text-gray-900">Tidak ada pengumuman ditemukan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div v-if="announcements.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                <p class="text-[11px] text-gray-500">Halaman {{ announcements.current_page }} dari {{ announcements.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="announcements.prev_page_url" :href="announcements.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100"><ChevronLeft class="w-5 h-5" /></Link>
                    <Link v-if="announcements.next_page_url" :href="announcements.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100"><ChevronRight class="w-5 h-5" /></Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Hapus Pengumuman</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus "<strong class="text-amber-600">{{ deleteTarget?.title }}</strong>"?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="deleteItem" class="px-4 py-2 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
