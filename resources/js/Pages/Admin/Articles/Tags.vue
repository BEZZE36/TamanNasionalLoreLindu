<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { Tag, Plus, Search, Edit2, Trash2, X, Sparkles, FileText, Loader2, AlertTriangle } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    tags: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const showModal = ref(false);
const editingTag = ref(null);
const selectedTags = ref([]);
const isSubmitting = ref(false);
let ctx;

const form = useForm({ name: '' });

// GSAP Animations
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content',
            { opacity: 0, y: -20 },
            { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' }
        );
        gsap.fromTo('.stat-card',
            { opacity: 0, y: 20, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.tag-item',
            { opacity: 0, scale: 0.9 },
            { opacity: 1, scale: 1, duration: 0.3, stagger: 0.03, delay: 0.4, ease: 'back.out(1.5)' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const filteredTags = computed(() => props.tags?.data || []);

const searchTags = () => {
    router.get('/admin/articles/tags', { search: search.value }, { preserveState: true });
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
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    
    const url = editingTag.value 
        ? `/admin/articles/tags/${editingTag.value.id}` 
        : '/admin/articles/tags';
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
    } finally {
        isSubmitting.value = false;
    }
};

const deleteTag = async (tag) => {
    if (!confirm(`Hapus tag "${tag.name}"?`)) return;
    
    try {
        await fetch(`/admin/articles/tags/${tag.id}`, {
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
        await fetch('/admin/articles/tags/bulk-delete', {
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
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-teal-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-teal-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="header-content relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Tag class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Tag Artikel</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ tags?.total || 0 }} Total
                            </span>
                        </div>
                        <p class="text-emerald-100/80 text-xs">Kelola tag untuk artikel dan berita</p>
                    </div>
                </div>
                
                <button @click="openCreateModal" 
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-600 text-xs font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg">
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                    <span>Tambah Tag</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                </button>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 max-w-xs">
            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <Tag class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-xl font-black text-gray-900">{{ tags?.total || 0 }}</p>
                    <p class="text-[10px] text-gray-500 font-medium">Total Tag</p>
                </div>
            </div>
        </div>

        <!-- Search & Actions -->
        <div class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="relative flex-1 max-w-md group">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-emerald-500 transition-colors" />
                <input v-model="search" @keyup.enter="searchTags" type="text" placeholder="Cari tag..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all duration-300">
            </div>
            <div v-if="selectedTags.length" class="flex items-center gap-2">
                <span class="text-xs text-gray-600">{{ selectedTags.length }} dipilih</span>
                <button @click="bulkDelete" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all">
                    <Trash2 class="w-4 h-4" /> Hapus
                </button>
            </div>
        </div>

        <!-- Tags Grid -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center gap-3 bg-gradient-to-r from-gray-50 to-gray-100/50">
                <input type="checkbox" @change="toggleSelectAll" :checked="selectedTags.length === filteredTags.length && filteredTags.length > 0"
                    class="w-4 h-4 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500 cursor-pointer">
                <span class="text-xs text-gray-600 font-medium">Pilih Semua</span>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-3">
                    <div v-for="tag in filteredTags" :key="tag.id" 
                        class="tag-item group flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-gray-50 to-gray-100/50 rounded-xl border border-gray-200 hover:border-emerald-300 hover:from-emerald-50 hover:to-teal-50 transition-all duration-300">
                        <input type="checkbox" :checked="selectedTags.includes(tag.id)" @change="toggleSelect(tag.id)"
                            class="w-4 h-4 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500 cursor-pointer">
                        <span class="text-xs font-medium text-gray-700">{{ tag.name }}</span>
                        <span class="text-[10px] text-gray-400 bg-gray-200 px-2 py-0.5 rounded-full font-medium">{{ tag.articles_count }}</span>
                        <button @click="openEditModal(tag)" class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-emerald-600 transition-all">
                            <Edit2 class="w-3.5 h-3.5" />
                        </button>
                        <button @click="deleteTag(tag)" class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-600 transition-all">
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
                    <p v-if="!filteredTags.length" class="text-gray-400 text-xs py-8 text-center w-full">Belum ada tag</p>
                </div>
            </div>
            <!-- Pagination -->
            <div v-if="tags?.last_page > 1" class="p-4 border-t border-gray-100 flex justify-center gap-1 bg-gray-50/50">
                <Link v-for="link in tags.links" :key="link.label" :href="link.url || '#'" 
                    :class="[
                        'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                        link.active ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'
                    ]"
                    v-html="link.label" />
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-gray-100/50">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <Tag class="w-4 h-4 text-emerald-500" />
                                {{ editingTag ? 'Edit Tag' : 'Tambah Tag Baru' }}
                            </h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <form @submit.prevent="submitForm" class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Nama Tag *</label>
                                <input v-model="form.name" type="text" required placeholder="Contoh: Konservasi"
                                    class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" :disabled="isSubmitting"
                                    class="flex-1 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-bold rounded-xl hover:shadow-lg transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                                    <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                                    {{ editingTag ? 'Simpan' : 'Tambah' }}
                                </button>
                                <button type="button" @click="closeModal" class="px-4 py-3 bg-gray-100 text-gray-700 text-xs font-medium rounded-xl hover:bg-gray-200 transition-all">
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
