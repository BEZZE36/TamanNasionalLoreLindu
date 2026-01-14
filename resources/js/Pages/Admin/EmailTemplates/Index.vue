<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Mail, Plus, Edit, Trash2, Eye, Power, ChevronLeft, ChevronRight, Code } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ templates: { type: Object, required: true } });

const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const showPreviewModal = ref(false);
const previewData = ref({ subject: '', body: '' });
const previewLoading = ref(false);

const openDelete = (t) => { deleteTarget.value = t; showDeleteModal.value = true; };
const closeDelete = () => { deleteTarget.value = null; showDeleteModal.value = false; };
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/email-templates/${deleteTarget.value.id}`, { onSuccess: () => closeDelete() });
    }
};

const previewTemplate = async (template) => {
    previewLoading.value = true;
    try {
        const response = await fetch(`/admin/email-templates/${template.id}/preview`);
        previewData.value = await response.json();
        showPreviewModal.value = true;
    } catch (e) { console.error(e); }
    previewLoading.value = false;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-pink-500 via-rose-500 to-red-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <Mail class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Template Email</h1>
                        <p class="mt-1 text-pink-100/90 text-lg">Kelola template notifikasi email</p>
                    </div>
                </div>
                <Link href="/admin/email-templates/create"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-xl text-white rounded-xl font-bold hover:bg-white/30 transition-all shadow-lg hover:-translate-y-0.5">
                    <Plus class="w-4 h-4" /><span>Tambah Template</span>
                </Link>
            </div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div v-for="template in templates.data" :key="template.id"
                class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all hover:-translate-y-1">
                <div class="p-6 bg-gradient-to-r from-pink-50 to-rose-50">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center shadow-lg">
                                <Mail class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ template.name }}</h3>
                                <p class="text-xs text-gray-500 font-mono">{{ template.slug }}</p>
                            </div>
                        </div>
                        <span :class="['px-2.5 py-1 rounded-full text-xs font-bold', template.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                            {{ template.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ template.description || 'Tidak ada deskripsi' }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <Code class="w-4 h-4" />
                        <span>{{ template.variables?.length || 0 }} variabel</span>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 flex gap-2">
                    <button @click="previewTemplate(template)" class="flex-1 py-2 text-center rounded-lg bg-blue-50 text-blue-600 font-medium hover:bg-blue-100 transition-colors">
                        <Eye class="w-4 h-4 inline mr-1" />Preview
                    </button>
                    <Link :href="`/admin/email-templates/${template.id}/edit`" class="flex-1 py-2 text-center rounded-lg bg-pink-50 text-pink-600 font-medium hover:bg-pink-100 transition-colors">
                        <Edit class="w-4 h-4 inline mr-1" />Edit
                    </Link>
                    <button @click="openDelete(template)" class="px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
            <div v-if="!templates.data?.length" class="col-span-full bg-white rounded-2xl shadow-lg border border-gray-100 py-16 text-center">
                <Mail class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <p class="text-gray-500 font-medium mb-4">Belum ada template</p>
                <Link href="/admin/email-templates/create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-pink-500 text-white font-bold rounded-xl">
                    <Plus class="w-4 h-4" />Buat Template Pertama
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="templates.last_page > 1" class="flex items-center justify-between">
            <p class="text-[11px] text-gray-500">Halaman {{ templates.current_page }} dari {{ templates.last_page }}</p>
            <div class="flex gap-2">
                <Link v-if="templates.prev_page_url" :href="templates.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronLeft class="w-5 h-5" /></Link>
                <Link v-if="templates.next_page_url" :href="templates.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronRight class="w-5 h-5" /></Link>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeDelete">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Template</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus template "<strong class="text-pink-600">{{ deleteTarget?.name }}</strong>"?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="closeDelete" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="confirmDelete" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showPreviewModal = false">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-pink-50 to-rose-50 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Preview Email</h3>
                    <button @click="showPreviewModal = false" class="p-2 hover:bg-pink-100 rounded-lg transition-colors">&times;</button>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-500 mb-1">Subject:</p>
                    <p class="font-bold text-gray-900 mb-4">{{ previewData.subject }}</p>
                    <p class="text-sm text-gray-500 mb-1">Body:</p>
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 prose prose-sm max-w-none" v-html="previewData.body"></div>
                </div>
            </div>
        </div>
    </div>
</template>
