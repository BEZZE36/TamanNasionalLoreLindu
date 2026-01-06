<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Search, Edit, Trash2, Image, Star, ChevronLeft, ChevronRight, Copy, Eye, Video, Play } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    galleries: Object, destinations: Array,
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const destination = ref(props.filters?.destination || '');
const status = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => { router.get('/admin/gallery', { search: search.value || undefined, destination: destination.value || undefined, status: status.value || undefined }, { preserveState: true }); };
const confirmDelete = (item) => { deleteTarget.value = item; showDeleteModal.value = true; };
const deleteGallery = () => { if (deleteTarget.value) { router.delete(`/admin/gallery/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; } }); }};
const toggleActive = async (item) => { await fetch(`/admin/gallery/${item.id}/toggle-active`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); router.reload(); };
const toggleFeatured = async (item) => { await fetch(`/admin/gallery/${item.id}/toggle-featured`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); router.reload(); };
const duplicate = async (item) => { const res = await fetch(`/admin/gallery/${item.id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); const data = await res.json(); if (data.redirect) window.location.href = data.redirect; };
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div><h1 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Image class="w-7 h-7 text-violet-600" />Gallery</h1><p class="text-gray-500 text-sm mt-1">Kelola album foto dan video</p></div>
            <Link href="/admin/gallery/create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold rounded-xl shadow-lg shadow-violet-500/30 hover:shadow-violet-500/50 transition-all"><Plus class="w-4 h-4" />Tambah Album</Link>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <div class="relative flex-1 w-full"><Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" /><input v-model="search" type="text" placeholder="Cari album..." class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"></div>
                <select v-model="destination" @change="applyFilters" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"><option value="">Semua Destinasi</option><option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option></select>
                <select v-model="status" @change="applyFilters" class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"><option value="">Semua Status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100"><tr><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Album</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Destinasi</th><th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Media</th><th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th><th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Unggulan</th><th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in galleries.data" :key="item.id" class="hover:bg-violet-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative"><img :src="item.cover_url || '/images/placeholder-no-image.svg'" class="w-14 h-14 rounded-xl object-cover shadow" @error="(e) => e.target.src='/images/placeholder-no-image.svg'"><div v-if="item.has_video" class="absolute -bottom-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center"><Play class="w-2.5 h-2.5 text-white fill-white" /></div></div>
                                    <div><p class="font-semibold text-gray-900">{{ item.title }}</p><p class="text-xs text-gray-500">{{ item.location || '-' }}</p></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-sm">{{ item.destination?.name || '-' }}</td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-1 bg-violet-100 text-violet-700 rounded-full text-xs font-bold">{{ item.media_count || 0 }}</span></td>
                            <td class="px-4 py-3 text-center"><button @click="toggleActive(item)" :class="['px-3 py-1 rounded-full text-xs font-bold', item.is_active ? 'bg-violet-100 text-violet-700' : 'bg-gray-100 text-gray-600']">{{ item.is_active ? 'Aktif' : 'Nonaktif' }}</button></td>
                            <td class="px-4 py-3 text-center"><button @click="toggleFeatured(item)" :class="['p-2 rounded-lg transition-colors', item.is_featured ? 'text-amber-500 hover:bg-amber-50' : 'text-gray-300 hover:bg-gray-100']"><Star :class="['w-5 h-5', item.is_featured ? 'fill-amber-500' : '']" /></button></td>
                            <td class="px-4 py-3"><div class="flex items-center justify-end gap-2"><Link :href="`/admin/gallery/${item.id}/edit`" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors"><Edit class="w-4 h-4" /></Link><button @click="duplicate(item)" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors"><Copy class="w-4 h-4" /></button><button @click="confirmDelete(item)" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors"><Trash2 class="w-4 h-4" /></button></div></td>
                        </tr>
                        <tr v-if="!galleries.data?.length"><td colspan="6" class="px-4 py-12 text-center text-gray-500"><Image class="w-12 h-12 mx-auto mb-4 text-gray-300" /><p class="font-medium">Tidak ada album</p></td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="galleries.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between"><p class="text-[11px] text-gray-500">Halaman {{ galleries.current_page }} dari {{ galleries.last_page }}</p><div class="flex gap-2"><Link v-if="galleries.prev_page_url" :href="galleries.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronLeft class="w-5 h-5" /></Link><Link v-if="galleries.next_page_url" :href="galleries.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronRight class="w-5 h-5" /></Link></div></div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"><div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4"><h3 class="text-xl font-bold text-gray-900 mb-4">Hapus Album</h3><p class="text-gray-600 mb-6">Yakin ingin menghapus <strong>{{ deleteTarget?.title }}</strong>?</p><div class="flex gap-3 justify-end"><button @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button><button @click="deleteGallery" class="px-4 py-2 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button></div></div></div>
    </div>
</template>
