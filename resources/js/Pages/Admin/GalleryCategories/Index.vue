<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FolderOpen, Plus, Edit, Trash2, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ categories: { type: Array, default: () => [] } });

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const openDelete = (c) => { deleteTarget.value = c; showDeleteModal.value = true; };
const closeDelete = () => { deleteTarget.value = null; showDeleteModal.value = false; };
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/gallery-categories/${deleteTarget.value.id}`, { onSuccess: () => closeDelete() });
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <FolderOpen class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Kategori Galeri</h1>
                        <p class="mt-1 text-violet-100/90 text-lg">Kelola kategori album galeri</p>
                    </div>
                </div>
                <Link href="/admin/gallery-categories/create"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-xl text-white rounded-xl font-bold hover:bg-white/30 transition-all shadow-lg hover:-translate-y-0.5">
                    <Plus class="w-4 h-4" /><span>Tambah Kategori</span>
                </Link>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Nama Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Galeri</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="cat in categories" :key="cat.id" class="hover:bg-violet-50/30 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-white font-bold shadow">
                                        {{ cat.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-bold text-gray-900">{{ cat.name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-sm max-w-xs truncate">{{ cat.description || '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-sm font-bold">{{ cat.galleries_count || 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="['px-3 py-1 rounded-full text-xs font-bold', cat.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600']">
                                    {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="`/admin/gallery-categories/${cat.id}/edit`" class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"><Edit class="w-4 h-4" /></Link>
                                    <button @click="openDelete(cat)" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"><Trash2 class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!categories.length"><td colspan="5" class="px-6 py-12 text-center text-gray-500"><FolderOpen class="w-12 h-12 mx-auto mb-4 text-gray-300" /><p class="font-medium">Belum ada kategori</p></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeDelete">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Kategori</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus kategori "<strong class="text-violet-600">{{ deleteTarget?.name }}</strong>"?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="closeDelete" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="confirmDelete" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
