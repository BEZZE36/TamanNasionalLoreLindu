<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Image, Plus, Edit, Trash2, GripVertical, ExternalLink } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ banners: { type: Array, default: () => [] } });

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const openDelete = (b) => { deleteTarget.value = b; showDeleteModal.value = true; };
const closeDelete = () => { deleteTarget.value = null; showDeleteModal.value = false; };
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/banners/${deleteTarget.value.id}`, { onSuccess: () => closeDelete() });
    }
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : null;
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-60 h-60 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <Image class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Manajemen Banner</h1>
                        <p class="mt-1 text-orange-100/90 text-lg">Kelola banner promosi di homepage</p>
                    </div>
                </div>
                <Link href="/admin/banners/create"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-xl text-white rounded-xl font-bold hover:bg-white/30 transition-all shadow-lg hover:-translate-y-0.5">
                    <Plus class="w-4 h-4" /><span>Tambah Banner</span>
                </Link>
            </div>
        </div>

        <!-- Banner List -->
        <div class="space-y-4">
            <div v-for="banner in banners" :key="banner.id"
                class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 flex items-center gap-5 group hover:shadow-xl transition-all">
                <!-- Drag Handle -->
                <div class="cursor-move text-gray-300 hover:text-gray-400"><GripVertical class="w-5 h-5" /></div>
                
                <!-- Image -->
                <div class="w-48 h-24 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 shadow-inner">
                    <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                </div>
                
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                        {{ banner.title }}
                        <a v-if="banner.link_url" :href="banner.link_url" target="_blank" class="text-gray-400 hover:text-orange-500 transition-colors"><ExternalLink class="w-4 h-4" /></a>
                    </h3>
                    <p v-if="banner.subtitle" class="text-sm text-gray-500 truncate">{{ banner.subtitle }}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <span v-if="banner.is_active" class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Aktif</span>
                        <span v-else class="px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">Nonaktif</span>
                        <span v-if="banner.start_at || banner.end_at" class="text-xs text-gray-500">
                            {{ formatDate(banner.start_at) }} - {{ formatDate(banner.end_at) }}
                        </span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <Link :href="`/admin/banners/${banner.id}/edit`" class="p-2.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                        <Edit class="w-4 h-4" />
                    </Link>
                    <button @click="openDelete(banner)" class="p-2.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="!banners.length" class="bg-white rounded-2xl shadow-lg border border-gray-100 py-16 text-center">
                <Image class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <p class="text-gray-500 font-medium mb-4">Belum ada banner</p>
                <Link href="/admin/banners/create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-pink-500 text-white font-bold rounded-xl shadow-lg">
                    <Plus class="w-4 h-4" />Tambah Banner Pertama
                </Link>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeDelete">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Banner</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus banner "<strong class="text-red-600">{{ deleteTarget?.title }}</strong>"?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="closeDelete" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="confirmDelete" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
