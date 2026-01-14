<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Tag, Plus, Search, Edit2, Trash2, X, Sparkles, FileText } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    tags: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const showModal = ref(false);
const editingTag = ref(null);
const selectedTags = ref([]);

const form = useForm({ name: '' });

const filteredTags = computed(() => props.tags?.data || []);

const searchTags = () => {
    router.get('/admin/news/tags', { search: search.value }, { preserveState: true });
};

const openCreateModal = () => {
    editingTag.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (tag) => {
    editingTag.value = tag;
    form.name = tag.name;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingTag.value = null;
    form.reset();
};

const submitForm = async () => {
    const url = editingTag.value 
        ? `/admin/news/tags/${editingTag.value.id}` 
        : '/admin/news/tags';
    const method = editingTag.value ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: form.name })
        });
        const data = await response.json();
        if (data.success) {
            closeModal();
            router.reload();
        } else {
            alert(data.message || 'Gagal menyimpan tag');
        }
    } catch (e) {
        alert('Gagal menyimpan tag');
    }
};

const deleteTag = async (tag) => {
    if (!confirm(`Hapus tag "${tag.name}"?`)) return;
    
    try {
        await fetch(`/admin/news/tags/${tag.id}`, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        router.reload();
    } catch (e) {
        alert('Gagal menghapus tag');
    }
};

const toggleSelect = (id) => {
    const idx = selectedTags.value.indexOf(id);
    if (idx > -1) {
        selectedTags.value.splice(idx, 1);
    } else {
        selectedTags.value.push(id);
    }
};

const toggleSelectAll = () => {
    if (selectedTags.value.length === filteredTags.value.length) {
        selectedTags.value = [];
    } else {
        selectedTags.value = filteredTags.value.map(t => t.id);
    }
};

const bulkDelete = async () => {
    if (!selectedTags.value.length) return;
    if (!confirm(`Hapus ${selectedTags.value.length} tag?`)) return;

    try {
        await fetch('/admin/news/tags/bulk-delete', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: selectedTags.value })
        });
        selectedTags.value = [];
        router.reload();
    } catch (e) {
        alert('Gagal menghapus tag');
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Tag class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tag Berita</h1>
                    <p class="text-gray-500">Kelola tag untuk berita</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openCreateModal" class="px-4 py-2 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-all flex items-center gap-2">
                    <Plus class="w-5 h-5" /> Tambah Tag
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <Tag class="w-5 h-5 text-red-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ tags?.total || 0 }}</p>
                        <p class="text-xs text-gray-500">Total Tag</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Actions -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative flex-1 max-w-md">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input v-model="search" @keyup.enter="searchTags" type="text" placeholder="Cari tag..."
                    class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
            </div>
            <div v-if="selectedTags.length" class="flex items-center gap-2">
                <span class="text-sm text-gray-600">{{ selectedTags.length }} dipilih</span>
                <button @click="bulkDelete" class="px-4 py-2 bg-red-500 text-white rounded-xl text-sm font-medium hover:bg-red-600 transition-all flex items-center gap-2">
                    <Trash2 class="w-4 h-4" /> Hapus
                </button>
            </div>
        </div>

        <!-- Tags Grid -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center gap-3">
                <input type="checkbox" @change="toggleSelectAll" :checked="selectedTags.length === filteredTags.length && filteredTags.length > 0"
                    class="w-5 h-5 rounded text-red-500 focus:ring-red-500">
                <span class="text-sm text-gray-600">Pilih Semua</span>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-3">
                    <div v-for="tag in filteredTags" :key="tag.id" 
                        class="group flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-xl border border-gray-200 hover:border-red-300 hover:bg-red-50 transition-all">
                        <input type="checkbox" :checked="selectedTags.includes(tag.id)" @change="toggleSelect(tag.id)"
                            class="w-4 h-4 rounded text-red-500 focus:ring-red-500">
                        <span class="font-medium text-gray-700">{{ tag.name }}</span>
                        <span class="text-xs text-gray-400 bg-gray-200 px-2 py-0.5 rounded-full">{{ tag.articles_count }}</span>
                        <button @click="openEditModal(tag)" class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-600 transition-all">
                            <Edit2 class="w-4 h-4" />
                        </button>
                        <button @click="deleteTag(tag)" class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-600 transition-all">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                    <p v-if="!filteredTags.length" class="text-gray-500 py-8 text-center w-full">Belum ada tag</p>
                </div>
            </div>
            <!-- Pagination -->
            <div v-if="tags?.last_page > 1" class="p-4 border-t border-gray-100 flex justify-center gap-2">
                <Link v-for="link in tags.links" :key="link.label" :href="link.url || '#'" 
                    :class="['px-3 py-1 rounded-lg text-sm', link.active ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                    v-html="link.label" />
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <Tag class="w-5 h-5 text-red-500" />
                                {{ editingTag ? 'Edit Tag' : 'Tambah Tag Baru' }}
                            </h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <form @submit.prevent="submitForm" class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tag *</label>
                                <input v-model="form.name" type="text" required placeholder="Contoh: Breaking News"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" :disabled="form.processing"
                                    class="flex-1 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 transition-all disabled:opacity-50">
                                    {{ editingTag ? 'Simpan' : 'Tambah' }}
                                </button>
                                <button type="button" @click="closeModal" class="px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-all">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
