<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Search, Edit, Trash2, Flower2, Star, ChevronLeft, ChevronRight, Copy, Filter, CheckCircle, Eye } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    flora: Object,
    totalFlora: Number,
    activeFlora: Number,
    featuredFlora: Number,
    inactiveFlora: Number,
    filters: { type: Object, default: () => ({}) },
    categories: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const category = ref(props.filters?.category || '');
const status = ref(props.filters?.status || '');
const selectedIds = ref([]);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/flora', { search: search.value || undefined, category: category.value || undefined, status: status.value || undefined }, { preserveState: true });
};

const confirmDelete = (item) => { deleteTarget.value = item; showDeleteModal.value = true; };
const deleteFlora = () => { if (deleteTarget.value) { router.delete(`/admin/flora/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; } }); }};
const toggleActive = async (item) => { await fetch(`/admin/flora/${item.id}/toggle-active`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); router.reload(); };
const toggleFeatured = async (item) => { await fetch(`/admin/flora/${item.id}/toggle-featured`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); router.reload(); };
const duplicate = async (item) => { const res = await fetch(`/admin/flora/${item.id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); const data = await res.json(); if (data.redirect) window.location.href = data.redirect; };

const getCategoryLabel = (cat) => ({ umum: 'Umum', langka: 'Langka', endemik: 'Endemik' }[cat] || cat || 'Umum');
const getCategoryClass = (cat) => ({ umum: 'bg-green-100 text-green-700', langka: 'bg-amber-100 text-amber-700', endemik: 'bg-purple-100 text-purple-700' }[cat] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <div class="space-y-4">
        <!-- Premium Header -->
        <div class="relative overflow-hidden bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 rounded-3xl p-8 shadow-2xl shadow-green-500/25">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-xl rounded-xl flex items-center justify-center ring-4 ring-white/20">
                        <Flower2 class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight">Flora</h1>
                        <p class="text-green-100">Kelola data flora taman nasional</p>
                    </div>
                </div>
                <Link href="/admin/flora/create" class="group inline-flex items-center gap-3 px-4 py-2.5 bg-white text-green-600 font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <Plus class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" />
                    <span>Tambah Flora</span>
                </Link>
            </div>
            
            <!-- Quick Stats -->
            <div class="relative grid grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><Flower2 class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ totalFlora }}</p><p class="text-xs text-green-100">Total Flora</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><CheckCircle class="w-5 h-5 text-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ activeFlora }}</p><p class="text-xs text-green-100">Aktif</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><Star class="w-5 h-5 text-white fill-white" /></div>
                        <div><p class="text-lg font-bold text-white">{{ featuredFlora }}</p><p class="text-xs text-green-100">Unggulan</p></div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
                        <div><p class="text-lg font-bold text-white">{{ inactiveFlora }}</p><p class="text-xs text-green-100">Nonaktif</p></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-5">
            <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
                <div class="relative flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari flora berdasarkan nama atau nama ilmiah..." class="w-full pl-12 pr-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="relative">
                        <select v-model="category" @change="applyFilters" class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 appearance-none min-w-[140px] pr-10">
                            <option value="">Semua Kategori</option>
                            <option value="umum">Umum</option>
                            <option value="langka">Langka</option>
                            <option value="endemik">Endemik</option>
                        </select>
                    </div>
                    <div class="relative">
                        <select v-model="status" @change="applyFilters" class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 appearance-none min-w-[130px] pr-10">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                            <th class="px-5 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Flora</th>
                            <th class="px-5 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Kategori</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Unggulan</th>
                            <th class="px-5 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in flora.data" :key="item.id" class="group hover:bg-gradient-to-r hover:from-green-50/50 hover:to-transparent transition-all duration-300">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <img :src="item.image_url || '/images/placeholder-no-image.svg'" class="w-14 h-14 rounded-xl object-cover shadow-lg ring-2 ring-gray-100 group-hover:ring-green-200 transition-all" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 group-hover:text-green-600 transition-colors">{{ item.name }}</p>
                                        <p class="text-sm text-gray-500 italic">{{ item.scientific_name || '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span :class="['px-3 py-1.5 rounded-full text-xs font-bold', getCategoryClass(item.category)]">{{ getCategoryLabel(item.category) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button @click="toggleActive(item)" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all', item.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200']">
                                    <span :class="['w-1.5 h-1.5 rounded-full', item.is_active ? 'bg-green-500' : 'bg-gray-400']"></span>
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button @click="toggleFeatured(item)" :class="['p-2.5 rounded-xl transition-all', item.is_featured ? 'bg-amber-100 text-amber-600 hover:bg-amber-200 shadow-lg shadow-amber-100' : 'text-gray-300 hover:bg-gray-100 hover:text-gray-400']">
                                    <Star :class="['w-5 h-5', item.is_featured ? 'fill-amber-500' : '']" />
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="`/flora/${item.slug || item.id}`" target="_blank" class="p-2 rounded-lg text-gray-400 hover:text-violet-600 hover:bg-violet-50 transition-all" title="Lihat">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="`/admin/flora/${item.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="duplicate(item)" class="p-2 rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 transition-all" title="Duplikat">
                                        <Copy class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(item)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!flora.data?.length">
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                                        <Flower2 class="w-10 h-10 text-gray-300" />
                                    </div>
                                    <p class="font-bold text-gray-900 mb-1">Belum Ada Flora</p>
                                    <p class="text-gray-500 text-sm mb-4">Mulai tambahkan data flora pertama</p>
                                    <Link href="/admin/flora/create" class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-xl text-sm font-semibold hover:bg-green-600 transition-colors">
                                        <Plus class="w-4 h-4" />Tambah Flora
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="flora.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700">{{ flora.from }}</span> - <span class="font-semibold text-gray-700">{{ flora.to }}</span> dari <span class="font-semibold text-gray-700">{{ flora.total }}</span> flora
                </p>
                <div class="flex items-center gap-2">
                    <Link v-if="flora.prev_page_url" :href="flora.prev_page_url" preserve-scroll class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <ChevronLeft class="w-4 h-4" />
                        <span class="hidden sm:inline">Prev</span>
                    </Link>
                    <span class="px-4 py-2 rounded-lg bg-green-500 text-white text-sm font-bold">{{ flora.current_page }}</span>
                    <Link v-if="flora.next_page_url" :href="flora.next_page_url" preserve-scroll class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors">
                        <span class="hidden sm:inline">Next</span>
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showDeleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 scale-95 translate-y-4" enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <div v-if="showDeleteModal" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center"><Trash2 class="w-6 h-6 text-red-600" /></div>
                                <div><h3 class="text-xl font-bold text-gray-900">Hapus Flora</h3><p class="text-gray-500 text-sm">Tindakan ini tidak dapat dibatalkan</p></div>
                            </div>
                            <p class="text-gray-600 mb-6">Yakin ingin menghapus <strong class="text-gray-900">{{ deleteTarget?.name }}</strong>?</p>
                            <div class="flex gap-3 justify-end">
                                <button @click="showDeleteModal = false" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition-colors">Batal</button>
                                <button @click="deleteFlora" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors shadow-lg shadow-red-500/25">Hapus</button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
