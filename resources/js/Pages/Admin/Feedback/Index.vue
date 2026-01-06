<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MessageSquare, Plus, Eye, Edit, Archive, Star, Search, Filter, ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    feedbacks: { type: Object, required: true },
    stats: { type: Object, default: () => ({ unread: 0, total: 0, published: 0 }) },
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const rating = ref(props.filters?.rating || '');
const showArchiveModal = ref(false);
const archiveTarget = ref(null);

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/feedback', {
        search: search.value || undefined,
        status: status.value || undefined,
        rating: rating.value || undefined
    }, { preserveState: true });
};

const openArchive = (f) => { archiveTarget.value = f; showArchiveModal.value = true; };
const closeArchive = () => { archiveTarget.value = null; showArchiveModal.value = false; };
const confirmArchive = () => {
    if (archiveTarget.value) {
        router.delete(`/admin/feedback/${archiveTarget.value.id}`, { onSuccess: () => closeArchive() });
    }
};

const togglePublish = (fb) => { router.patch(`/admin/feedback/${fb.id}/status`, { is_published: !fb.is_published }, { preserveScroll: true }); };
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

const statusBadge = (s) => {
    const badges = {
        unread: 'bg-red-100 text-red-700',
        read: 'bg-blue-100 text-blue-700',
        replied: 'bg-green-100 text-green-700',
        archived: 'bg-gray-100 text-gray-600'
    };
    return badges[s] || badges.unread;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <MessageSquare class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Feedback & Ulasan</h1>
                        <p class="mt-1 text-amber-100/90 text-lg">Kelola ulasan dan masukan pengunjung</p>
                    </div>
                </div>
                <Link href="/admin/feedback/create"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-xl text-white rounded-xl font-bold hover:bg-white/30 transition-all shadow-lg hover:-translate-y-0.5">
                    <Plus class="w-4 h-4" /><span>Tambah Manual</span>
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3"><div class="p-3 rounded-xl bg-red-100"><MessageSquare class="w-6 h-6 text-red-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Belum Dibaca</p><p class="text-2xl font-bold text-red-600">{{ stats.unread }}</p></div></div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3"><div class="p-3 rounded-xl bg-amber-100"><MessageSquare class="w-6 h-6 text-amber-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Total Aktif</p><p class="text-2xl font-bold text-amber-600">{{ stats.total }}</p></div></div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3"><div class="p-3 rounded-xl bg-green-100"><Star class="w-6 h-6 text-green-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Dipublikasikan</p><p class="text-2xl font-bold text-green-600">{{ stats.published }}</p></div></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari feedback..."
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                </div>
                <select v-model="status" @change="applyFilters" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                    <option value="">Semua Status</option>
                    <option value="unread">Belum Dibaca</option>
                    <option value="read">Sudah Dibaca</option>
                    <option value="replied">Sudah Dibalas</option>
                    <option value="archived">Diarsipkan</option>
                </select>
                <select v-model="rating" @change="applyFilters" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                    <option value="">Semua Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                    <option value="4">⭐⭐⭐⭐ (4)</option>
                    <option value="3">⭐⭐⭐ (3)</option>
                    <option value="2">⭐⭐ (2)</option>
                    <option value="1">⭐ (1)</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pengirim</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pesan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Rating</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Publik</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="fb in feedbacks.data" :key="fb.id" class="hover:bg-amber-50/30 transition-colors group">
                            <td class="px-4 py-4">
                                <div><p class="font-bold text-gray-900">{{ fb.display_name }}</p><p class="text-xs text-gray-500">{{ formatDate(fb.created_at) }}</p></div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm text-gray-700 line-clamp-2 max-w-xs">{{ fb.message }}</p>
                                <p v-if="fb.destination" class="text-xs text-amber-600 mt-1">📍 {{ fb.destination.name }}</p>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex justify-center">
                                    <span v-for="i in 5" :key="i" :class="i <= fb.rating ? 'text-amber-400' : 'text-gray-200'">⭐</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-bold capitalize', statusBadge(fb.status)]">{{ fb.status }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <button @click="togglePublish(fb)" :class="['px-2.5 py-1 rounded-full text-xs font-bold', fb.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                    {{ fb.is_published ? 'Ya' : 'Tidak' }}
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="`/admin/feedback/${fb.id}`" class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"><Eye class="w-4 h-4" /></Link>
                                    <Link :href="`/admin/feedback/${fb.id}/edit`" class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors"><Edit class="w-4 h-4" /></Link>
                                    <button @click="openArchive(fb)" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"><Archive class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!feedbacks.data?.length"><td colspan="6" class="px-4 py-12 text-center text-gray-500"><MessageSquare class="w-12 h-12 mx-auto mb-4 text-gray-300" /><p class="font-medium">Tidak ada feedback</p></td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="feedbacks.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">Halaman {{ feedbacks.current_page }} dari {{ feedbacks.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="feedbacks.prev_page_url" :href="feedbacks.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronLeft class="w-5 h-5" /></Link>
                    <Link v-if="feedbacks.next_page_url" :href="feedbacks.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronRight class="w-5 h-5" /></Link>
                </div>
            </div>
        </div>

        <!-- Archive Modal -->
        <div v-if="showArchiveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeArchive">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Arsipkan Feedback</h3>
                <p class="text-gray-600 mb-6">Yakin ingin mengarsipkan feedback dari <strong class="text-amber-600">{{ archiveTarget?.display_name }}</strong>?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="closeArchive" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="confirmArchive" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Arsipkan</button>
                </div>
            </div>
        </div>
    </div>
</template>
