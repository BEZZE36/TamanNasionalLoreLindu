<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Plus, Search, Edit, Trash2, Eye, MapPin, Star, StarOff, 
    Power, PowerOff, Copy, Filter, ChevronLeft, ChevronRight,
    CheckCircle, XCircle, MoreVertical, Sparkles, Image, TrendingUp
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    destinations: Object,
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const selectedIds = ref([]);
const selectAll = ref(false);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const actionMenuId = ref(null);

// Watch search for debounced filtering
let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

const applyFilters = () => {
    router.get('/admin/destinations', {
        search: search.value || undefined,
        status: status.value || undefined
    }, { preserveState: true });
};

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = props.destinations.data.map(d => d.id);
    } else {
        selectedIds.value = [];
    }
};

const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) selectedIds.value.splice(index, 1);
    else selectedIds.value.push(id);
};

const confirmDelete = (dest) => {
    deleteTarget.value = dest;
    showDeleteModal.value = true;
};

const deleteDestination = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/destinations/${deleteTarget.value.id}`, {
            onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; }
        });
    }
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    if (confirm(`Hapus ${selectedIds.value.length} destinasi?`)) {
        fetch('/admin/destinations/bulk-delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify({ ids: selectedIds.value })
        }).then(() => { selectedIds.value = []; router.reload(); });
    }
};

const toggleActive = async (dest) => {
    await fetch(`/admin/destinations/${dest.id}/toggle-active`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }});
    router.reload();
};

const toggleFeatured = async (dest) => {
    await fetch(`/admin/destinations/${dest.id}/toggle-featured`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }});
    router.reload();
};

const duplicate = async (dest) => {
    const res = await fetch(`/admin/destinations/${dest.id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }});
    const data = await res.json();
    if (data.redirect) window.location.href = data.redirect;
};

const formatPrice = (price) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price || 0);

// Stats computed
const totalDestinations = computed(() => props.destinations?.total || props.destinations?.data?.length || 0);
const activeCount = computed(() => props.destinations?.data?.filter(d => d.is_active).length || 0);
const featuredCount = computed(() => props.destinations?.data?.filter(d => d.is_featured).length || 0);
</script>

<template>
    <div class="space-y-4">
        <!-- Premium Header -->
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 via-green-500 to-teal-600 rounded-3xl p-8 shadow-2xl shadow-emerald-500/25">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-xl rounded-xl flex items-center justify-center ring-4 ring-white/20">
                        <MapPin class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight">Destinasi Wisata</h1>
                        <p class="text-emerald-100">Kelola semua destinasi wisata</p>
                    </div>
                </div>
                <Link href="/admin/destinations/create" class="group inline-flex items-center gap-3 px-4 py-2.5 bg-white text-emerald-600 font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <Plus class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" />
                    <span>Tambah Destinasi</span>
                </Link>
            </div>
            
            <!-- Quick Stats -->
            <div class="relative grid grid-cols-3 gap-4 mt-8">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><MapPin class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ totalDestinations }}</p><p class="text-[10px] text-emerald-100">Total Destinasi</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><CheckCircle class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ activeCount }}</p><p class="text-[10px] text-emerald-100">Aktif</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><Star class="w-5 h-5 text-white fill-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ featuredCount }}</p><p class="text-[10px] text-emerald-100">Unggulan</p></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-5">
            <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                <div class="relative flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari destinasi berdasarkan nama atau kota..." class="w-full pl-12 pr-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <select v-model="status" @change="applyFilters" class="pl-10 pr-8 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 appearance-none min-w-[140px]">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <button v-if="selectedIds.length > 0" @click="bulkDelete" class="px-5 py-3 bg-red-500 text-white rounded-xl font-semibold flex items-center gap-2 hover:bg-red-600 transition-all hover:-translate-y-0.5 shadow-lg shadow-red-500/25">
                        <Trash2 class="w-4 h-4" />
                        <span>Hapus ({{ selectedIds.length }})</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                            <th class="px-5 py-4 text-left w-12">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            </th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Destinasi</th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Lokasi</th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Harga</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Unggulan</th>
                            <th class="px-5 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="dest in destinations.data" :key="dest.id" class="group hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-transparent transition-all duration-300">
                            <td class="px-5 py-4">
                                <input type="checkbox" :checked="selectedIds.includes(dest.id)" @change="toggleSelect(dest.id)" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <img :src="dest.images?.[0]?.image_url || '/images/placeholder-no-image.svg'" class="w-14 h-14 rounded-xl object-cover shadow-lg ring-2 ring-gray-100 group-hover:ring-emerald-200 transition-all" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 rounded-lg flex items-center justify-center shadow" v-if="dest.images?.length > 1">
                                            <span class="text-[10px] text-white font-bold">{{ dest.images.length }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ dest.name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ dest.category?.name || 'Umum' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <MapPin class="w-4 h-4 text-gray-400" />
                                    <span class="text-sm">{{ dest.city }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-gray-900">{{ formatPrice(dest.prices?.[0]?.price) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button @click="toggleActive(dest)" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all', dest.is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200']">
                                    <span :class="['w-1.5 h-1.5 rounded-full', dest.is_active ? 'bg-emerald-500' : 'bg-gray-400']"></span>
                                    {{ dest.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button @click="toggleFeatured(dest)" :class="['p-2.5 rounded-xl transition-all', dest.is_featured ? 'bg-amber-100 text-amber-600 hover:bg-amber-200 shadow-lg shadow-amber-100' : 'text-gray-300 hover:bg-gray-100 hover:text-gray-400']">
                                    <Star :class="['w-5 h-5', dest.is_featured ? 'fill-amber-500' : '']" />
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="`/destinations/${dest.slug || dest.id}`" target="_blank" class="p-2 rounded-lg text-gray-400 hover:text-violet-600 hover:bg-violet-50 transition-all" title="Lihat">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="`/admin/destinations/${dest.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="duplicate(dest)" class="p-2 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all" title="Duplikat">
                                        <Copy class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(dest)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!destinations.data?.length">
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                                        <MapPin class="w-10 h-10 text-gray-300" />
                                    </div>
                                    <p class="font-bold text-gray-900 mb-1">Belum Ada Destinasi</p>
                                    <p class="text-gray-500 text-sm mb-4">Mulai tambahkan destinasi wisata pertama</p>
                                    <Link href="/admin/destinations/create" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-xl text-sm font-semibold hover:bg-emerald-600 transition-colors">
                                        <Plus class="w-4 h-4" />Tambah Destinasi
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="destinations.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700">{{ destinations.from }}</span> - <span class="font-semibold text-gray-700">{{ destinations.to }}</span> dari <span class="font-semibold text-gray-700">{{ destinations.total }}</span> destinasi
                </p>
                <div class="flex items-center gap-2">
                    <Link v-if="destinations.prev_page_url" :href="destinations.prev_page_url" preserve-scroll class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <ChevronLeft class="w-4 h-4" />
                        <span class="hidden sm:inline">Prev</span>
                    </Link>
                    <span class="px-4 py-2 rounded-lg bg-emerald-500 text-white text-sm font-bold">{{ destinations.current_page }}</span>
                    <Link v-if="destinations.next_page_url" :href="destinations.next_page_url" preserve-scroll class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <span class="hidden sm:inline">Next</span>
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showDeleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div v-if="showDeleteModal" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                    <Trash2 class="w-6 h-6 text-red-600" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Hapus Destinasi</h3>
                                    <p class="text-gray-500 text-sm">Tindakan ini tidak dapat dibatalkan</p>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6">Yakin ingin menghapus <strong class="text-gray-900">{{ deleteTarget?.name }}</strong>?</p>
                            <div class="flex gap-3 justify-end">
                                <button @click="showDeleteModal = false" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition-colors">Batal</button>
                                <button @click="deleteDestination" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors shadow-lg shadow-red-500/25">Hapus</button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
