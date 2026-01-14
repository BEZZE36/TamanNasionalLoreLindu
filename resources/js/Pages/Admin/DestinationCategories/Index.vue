<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MapPin, Plus, Edit, Trash2, Save, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ categories: { type: Object, required: true } });

const showAddForm = ref(false);
const editingId = ref(null);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const addForm = useForm({ name: '', description: '', icon: '' });
const editForm = useForm({ name: '', description: '', icon: '' });

const openAdd = () => { showAddForm.value = true; };
const closeAdd = () => { showAddForm.value = false; addForm.reset(); };
const submitAdd = () => { addForm.post('/admin/destination-categories', { onSuccess: () => closeAdd() }); };

const startEdit = (cat) => {
    editingId.value = cat.id;
    editForm.name = cat.name;
    editForm.description = cat.description || '';
    editForm.icon = cat.icon || '';
};
const cancelEdit = () => { editingId.value = null; editForm.reset(); };
const submitEdit = () => { editForm.put(`/admin/destination-categories/${editingId.value}`, { onSuccess: () => cancelEdit() }); };

const openDelete = (c) => { deleteTarget.value = c; showDeleteModal.value = true; };
const closeDelete = () => { deleteTarget.value = null; showDeleteModal.value = false; };
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/destination-categories/${deleteTarget.value.id}`, { onSuccess: () => closeDelete() });
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <MapPin class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Kategori Destinasi</h1>
                        <p class="mt-1 text-cyan-100/90 text-lg">Kelola kategori tempat wisata</p>
                    </div>
                </div>
                <button @click="openAdd" v-if="!showAddForm"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-xl text-white rounded-xl font-bold hover:bg-white/30 transition-all shadow-lg hover:-translate-y-0.5">
                    <Plus class="w-4 h-4" /><span>Tambah Kategori</span>
                </button>
            </div>
        </div>

        <!-- Add Form -->
        <div v-if="showAddForm" class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
                    <input v-model="addForm.name" type="text" required placeholder="Wisata Alam"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <input v-model="addForm.description" type="text" placeholder="Deskripsi singkat"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon (Lucide)</label>
                    <input v-model="addForm.icon" type="text" placeholder="mountain, tree, etc"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div class="flex gap-2">
                    <button @click="submitAdd" :disabled="addForm.processing" class="flex-1 px-4 py-3 bg-cyan-500 text-white font-bold rounded-xl hover:bg-cyan-600 transition-colors flex items-center justify-center gap-2">
                        <Save class="w-5 h-5" />Simpan
                    </button>
                    <button @click="closeAdd" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Deskripsi</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Destinasi</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="cat in categories.data" :key="cat.id" class="hover:bg-cyan-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <template v-if="editingId === cat.id">
                                    <input v-model="editForm.name" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                </template>
                                <template v-else>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white font-bold shadow">
                                            {{ cat.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="font-bold text-gray-900">{{ cat.name }}</span>
                                    </div>
                                </template>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-sm">
                                <template v-if="editingId === cat.id">
                                    <input v-model="editForm.description" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                </template>
                                <template v-else>{{ cat.description || '-' }}</template>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-sm font-bold">{{ cat.destinations_count || 0 }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <template v-if="editingId === cat.id">
                                        <button @click="submitEdit" class="p-2 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors"><Save class="w-4 h-4" /></button>
                                        <button @click="cancelEdit" class="p-2 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors"><X class="w-4 h-4" /></button>
                                    </template>
                                    <template v-else>
                                        <button @click="startEdit(cat)" class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"><Edit class="w-4 h-4" /></button>
                                        <button @click="openDelete(cat)" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"><Trash2 class="w-4 h-4" /></button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!categories.data?.length"><td colspan="4" class="px-6 py-12 text-center text-gray-500"><MapPin class="w-12 h-12 mx-auto mb-4 text-gray-300" /><p class="font-medium">Belum ada kategori</p></td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="categories.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">Halaman {{ categories.current_page }} dari {{ categories.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="categories.prev_page_url" :href="categories.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronLeft class="w-5 h-5" /></Link>
                    <Link v-if="categories.next_page_url" :href="categories.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronRight class="w-5 h-5" /></Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeDelete">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Kategori</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus kategori "<strong class="text-cyan-600">{{ deleteTarget?.name }}</strong>"?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="closeDelete" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="confirmDelete" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
