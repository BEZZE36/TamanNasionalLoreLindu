<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FileText, Plus, Search, Grid3X3, List, Eye, Edit, Trash2, ChevronLeft, ChevronRight, Star, Clock, LayoutGrid, RotateCcw } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    articles: Object,
    categories: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const category = ref(props.filters?.category || '');
const status = ref(props.filters?.status || '');
const viewMode = ref(localStorage.getItem('articleView') || 'grid');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/articles', {
        search: search.value || undefined,
        category: category.value || undefined,
        status: status.value || undefined
    }, { preserveState: true });
};

const resetFilters = () => { search.value = ''; category.value = ''; status.value = ''; applyFilters(); };
const setView = (m) => { viewMode.value = m; localStorage.setItem('articleView', m); };

const confirmDelete = (item) => { deleteTarget.value = item; showDeleteModal.value = true; };
const deleteArticle = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/articles/${deleteTarget.value.id}`, {
            onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; }
        });
    }
};

const stats = computed(() => ({
    total: props.articles?.total || 0,
    published: props.articles?.data?.filter(a => a.is_published).length || 0,
    featured: props.articles?.data?.filter(a => a.is_featured).length || 0
}));

const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <FileText class="w-7 h-7 text-rose-600" />Manajemen Artikel
                </h1>
                <p class="text-gray-500 text-sm mt-1">Kelola semua artikel dan konten blog</p>
            </div>
            <Link href="/admin/articles/create"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-500 to-pink-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 transition-all">
                <Plus class="w-4 h-4" />Tulis Artikel Baru
            </Link>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-rose-100"><FileText class="w-6 h-6 text-rose-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Total Artikel</p><p class="text-lg font-bold text-gray-900">{{ stats.total }}</p></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-green-100"><Eye class="w-6 h-6 text-green-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Terpublikasi</p><p class="text-2xl font-bold text-green-600">{{ stats.published }}</p></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-yellow-100"><Star class="w-6 h-6 text-yellow-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Unggulan</p><p class="text-2xl font-bold text-yellow-600">{{ stats.featured }}</p></div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari artikel..."
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                </div>
                <select v-model="category" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                    <option value="">Semua Kategori</option>
                    <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="status" @change="applyFilters"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                    <option value="">Semua Status</option>
                    <option value="published">Terpublikasi</option>
                    <option value="draft">Draft</option>
                </select>
                <button v-if="search || category || status" @click="resetFilters"
                    class="px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2">
                    <RotateCcw class="w-4 h-4" />Reset
                </button>
                <div class="flex gap-1 border border-gray-200 rounded-xl p-1">
                    <button @click="setView('grid')" :class="['p-2 rounded-lg transition-colors', viewMode === 'grid' ? 'bg-rose-100 text-rose-600' : 'text-gray-400 hover:text-gray-600']">
                        <Grid3X3 class="w-5 h-5" />
                    </button>
                    <button @click="setView('list')" :class="['p-2 rounded-lg transition-colors', viewMode === 'list' ? 'bg-rose-100 text-rose-600' : 'text-gray-400 hover:text-gray-600']">
                        <List class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            <div v-for="article in articles.data" :key="article.id"
                class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden group hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="aspect-video bg-gray-100 relative overflow-hidden">
                    <img :src="article.image_url" :alt="article.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3 flex gap-2">
                        <span v-if="article.is_featured" class="px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded-lg">⭐ Unggulan</span>
                        <span :class="['px-2 py-1 text-xs font-bold rounded-lg', article.is_published ? 'bg-green-500 text-white' : 'bg-gray-500 text-white']">
                            {{ article.is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-rose-600 font-bold uppercase mb-1">{{ categories[article.category] || article.category }}</p>
                    <h3 class="font-bold text-gray-900 line-clamp-2 mb-2">{{ article.title }}</h3>
                    <p class="text-xs text-gray-500 flex items-center gap-1"><Clock class="w-3 h-3" />{{ formatDate(article.published_at) }}</p>
                    <div class="flex items-center justify-end gap-1 mt-3 pt-3 border-t border-gray-100 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Link :href="`/admin/articles/${article.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                            <Edit class="w-4 h-4" />
                        </Link>
                        <button @click="confirmDelete(article)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="!articles.data?.length" class="col-span-full py-16 text-center">
                <FileText class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                <p class="font-medium text-gray-900">Tidak ada artikel ditemukan</p>
            </div>
        </div>

        <!-- List View -->
        <div v-else class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Artikel</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="article in articles.data" :key="article.id" class="hover:bg-rose-50/30 transition-colors group">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img :src="article.image_url" class="w-12 h-12 rounded-lg object-cover">
                                <div>
                                    <p class="font-semibold text-gray-900 line-clamp-1">{{ article.title }}</p>
                                    <p class="text-xs text-gray-500 line-clamp-1">{{ article.excerpt }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3"><span class="text-xs font-bold text-rose-600 uppercase">{{ categories[article.category] || article.category }}</span></td>
                        <td class="px-4 py-3 text-center">
                            <span :class="['px-2.5 py-1 rounded-full text-xs font-bold', article.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600']">
                                {{ article.is_published ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(article.published_at) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="`/admin/articles/${article.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                    <Edit class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(article)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="articles.last_page > 1" class="flex items-center justify-between">
            <p class="text-[11px] text-gray-500">Halaman {{ articles.current_page }} dari {{ articles.last_page }}</p>
            <div class="flex gap-2">
                <Link v-if="articles.prev_page_url" :href="articles.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100"><ChevronLeft class="w-5 h-5" /></Link>
                <Link v-if="articles.next_page_url" :href="articles.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-100"><ChevronRight class="w-5 h-5" /></Link>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Hapus Artikel</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus artikel "<strong class="text-rose-600">{{ deleteTarget?.title }}</strong>"?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="deleteArticle" class="px-4 py-2 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
