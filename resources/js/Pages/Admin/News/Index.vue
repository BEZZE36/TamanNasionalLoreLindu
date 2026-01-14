<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Plus, Search, Edit, Trash2, Eye, Newspaper, Star,
    Copy, CheckCircle, Clock, Sparkles, AlertTriangle
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    news: Object,
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const selectedIds = ref([]);
const selectAll = ref(false);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.stat-card', { opacity: 0, y: 20, scale: 0.95 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'back.out(1.7)' });
        gsap.fromTo('.table-row', { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.4, ease: 'power2.out' });
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

const applyFilters = () => {
    router.get('/admin/news', {
        search: search.value || undefined,
        status: status.value || undefined
    }, { preserveState: true });
};

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = props.news.data.map(n => n.id);
    } else {
        selectedIds.value = [];
    }
};

const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) selectedIds.value.splice(index, 1);
    else selectedIds.value.push(id);
};

const confirmDelete = (item) => {
    deleteTarget.value = item;
    showDeleteModal.value = true;
};

const deleteNews = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/news/${deleteTarget.value.id}`, {
            onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; }
        });
    }
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    if (confirm(`Hapus ${selectedIds.value.length} berita?`)) {
        fetch('/admin/news/bulk-delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify({ ids: selectedIds.value })
        }).then(() => { selectedIds.value = []; router.reload(); });
    }
};

const togglePublished = async (item) => {
    await fetch(`/admin/news/${item.id}/toggle-published`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }});
    router.reload();
};

const toggleFeatured = async (item) => {
    await fetch(`/admin/news/${item.id}/toggle-featured`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }});
    router.reload();
};

const duplicate = async (item) => {
    const res = await fetch(`/admin/news/${item.id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }});
    const data = await res.json();
    if (data.redirect) window.location.href = data.redirect;
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

const totalNews = computed(() => props.news?.total || props.news?.data?.length || 0);
const publishedCount = computed(() => props.news?.data?.filter(n => n.is_published).length || 0);
const featuredCount = computed(() => props.news?.data?.filter(n => n.is_featured).length || 0);
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-rose-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="header-content relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Newspaper class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Manajemen Berita</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ totalNews }} Total
                            </span>
                        </div>
                        <p class="text-red-100/80 text-xs">Kelola semua berita dan update terbaru</p>
                    </div>
                </div>
                
                <Link href="/admin/news/create" class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-red-600 text-xs font-bold hover:bg-red-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg">
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                    <span>Tulis Berita</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                </Link>
            </div>
        </div>
        
        <!-- Stats Cards - VISIBLE with solid backgrounds -->
        <!-- Stats Cards - Using inline styles for guaranteed visibility -->
        <div class="grid grid-cols-3 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #ef4444 0%, #f43f5e 100%);">
                <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div>
                <div class="relative flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                        <Newspaper class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-white">{{ totalNews }}</p>
                        <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Total Berita</p>
                    </div>
                </div>
            </div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);">
                <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div>
                <div class="relative flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                        <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-white">{{ publishedCount }}</p>
                        <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Terpublikasi</p>
                    </div>
                </div>
            </div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
                <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div>
                <div class="relative flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                        <Star class="w-5 h-5 text-white fill-white" />
                    </div>
                    <div>
                        <p class="text-2xl font-black text-white">{{ featuredCount }}</p>
                        <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Headline</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Actions Card -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3 justify-between">
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-red-500 transition-colors" />
                        <input type="text" v-model="search" placeholder="Cari berita berdasarkan judul..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-red-500 focus:ring-4 focus:ring-red-500/10 focus:bg-white transition-all duration-300" />
                    </div>
                    <select v-model="status" @change="applyFilters" class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-red-500 focus:ring-4 focus:ring-red-500/10 focus:bg-white transition-all cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                <button v-if="selectedIds.length > 0" @click="bulkDelete" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <Trash2 class="w-4 h-4" />
                    <span>Hapus ({{ selectedIds.length }})</span>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left w-12">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer" />
                            </th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Berita</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Headline</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in news.data" :key="item.id" class="table-row group hover:bg-gradient-to-r hover:from-red-50/50 hover:to-rose-50/30 transition-all duration-200">
                            <td class="px-4 py-3">
                                <input type="checkbox" :checked="selectedIds.includes(item.id)" @change="toggleSelect(item.id)" class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-4">
                                    <!-- BIGGER image - 80x80px -->
                                    <div class="relative flex-shrink-0">
                                        <img 
                                            :src="item.image_url || '/images/placeholder-no-image.svg'" 
                                            :alt="item.title"
                                            class="w-20 h-20 rounded-xl object-cover shadow-lg ring-2 ring-gray-100 group-hover:ring-red-200 transition-all" 
                                            @error="(e) => e.target.src='/images/placeholder-no-image.svg'"
                                        />
                                        <div v-if="item.is_featured" class="absolute -top-1 -right-1 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center shadow-md">
                                            <Star class="w-3 h-3 text-white fill-white" />
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 group-hover:text-red-600 transition-colors line-clamp-2">{{ item.title }}</p>
                                        <p class="text-[11px] text-gray-400 mt-1 line-clamp-2">{{ item.excerpt || 'Tidak ada excerpt' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                                    <Clock class="w-3.5 h-3.5" />
                                    {{ formatDate(item.published_at) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="togglePublished(item)" 
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold shadow-sm transition-all',
                                        item.is_published 
                                            ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white' 
                                            : 'bg-gray-200 text-gray-600'
                                    ]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', item.is_published ? 'bg-white' : 'bg-gray-500']"></span>
                                    {{ item.is_published ? 'Published' : 'Draft' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="toggleFeatured(item)" 
                                    :class="[
                                        'p-2.5 rounded-xl transition-all duration-200',
                                        item.is_featured ? 'bg-amber-100 text-amber-600 hover:bg-amber-200 shadow-sm' : 'text-gray-300 hover:bg-gray-100 hover:text-gray-400'
                                    ]">
                                    <Star :class="['w-5 h-5', item.is_featured ? 'fill-amber-500' : '']" />
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a v-if="item.is_published && item.slug" :href="`/news/${item.slug}`" target="_blank" class="p-2 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-all" title="Lihat">
                                        <Eye class="w-4 h-4" />
                                    </a>
                                    <span v-else class="p-2 rounded-lg text-gray-300 cursor-not-allowed" title="Tidak tersedia">
                                        <Eye class="w-4 h-4" />
                                    </span>
                                    <Link :href="`/admin/news/${item.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="duplicate(item)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Duplikat">
                                        <Copy class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(item)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="!news.data?.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center mb-4">
                                        <Newspaper class="w-8 h-8 text-red-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada berita</p>
                                    <p class="text-xs text-gray-400 mb-4">Mulai tulis berita pertama Anda</p>
                                    <Link href="/admin/news/create" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-xs font-bold shadow-lg hover:shadow-xl transition-all">
                                        <Plus class="w-4 h-4" />Tulis Berita
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="news.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ news.from }}-{{ news.to }}</strong> dari <strong>{{ news.total }}</strong> berita
                </p>
                <div class="flex gap-1">
                    <Link v-for="link in news.links" :key="link.label" :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-md' : link.url ? 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' : 'bg-gray-50 text-gray-300 cursor-not-allowed border border-gray-100'
                        ]"
                        v-html="link.label" preserve-scroll />
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Berita</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus berita <strong class="text-gray-900">"{{ deleteTarget?.title }}"</strong>?
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button @click="showDeleteModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all">Batal</button>
                            <button @click="deleteNews" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all flex items-center gap-2">
                                <Trash2 class="w-4 h-4" /> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .relative, .modal-leave-to .relative { transform: scale(0.95) translateY(10px); }
</style>
