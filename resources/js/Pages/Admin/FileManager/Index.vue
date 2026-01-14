<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Folder, Trash2, Image, FileText, Video, File, HardDrive, Calendar, Search } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    files: Object
});

const getFileIcon = (mime) => {
    if (mime?.startsWith('image')) return Image;
    if (mime?.startsWith('video')) return Video;
    if (mime?.includes('pdf') || mime?.includes('document')) return FileText;
    return File;
};

const formatBytes = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';

const deleteFile = (id) => {
    if (confirm('Hapus file ini?')) router.delete(`/admin/file-manager/${id}`);
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-xl shadow-cyan-500/30">
                    <Folder class="w-7 h-7 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">File <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-500 to-blue-600">Manager</span></h1>
                    <p class="text-gray-500 text-sm">Kelola file yang diupload ke sistem</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-3 text-cyan-500 mb-3"><File class="w-6 h-6" /></div>
                <div class="text-2xl font-black text-gray-900">{{ files?.total || 0 }}</div>
                <div class="text-[11px] text-gray-500">Total File</div>
            </div>
        </div>

        <!-- Files Grid -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-cyan-50 to-blue-50">
                <h3 class="text-lg font-bold text-gray-900">Daftar File</h3>
            </div>
            <div v-if="files?.data?.length" class="divide-y divide-gray-100">
                <div v-for="file in files.data" :key="file.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <component :is="getFileIcon(file.mime_type)" class="w-6 h-6 text-gray-500" />
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 truncate max-w-md">{{ file.filename || file.original_name || 'File' }}</div>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span>{{ file.mime_type }}</span>
                                <span class="flex items-center gap-1"><HardDrive class="w-3.5 h-3.5" />{{ formatBytes(file.size) }}</span>
                                <span class="flex items-center gap-1"><Calendar class="w-3.5 h-3.5" />{{ formatDate(file.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <button @click="deleteFile(file.id)" class="p-2.5 bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-colors" title="Hapus">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
            <div v-else class="px-6 py-16 text-center">
                <Folder class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <h3 class="text-lg font-bold text-gray-900 mb-2">Tidak ada file</h3>
                <p class="text-gray-500">File yang diupload akan muncul di sini.</p>
            </div>
            
            <!-- Pagination -->
            <div v-if="files?.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">Halaman {{ files.current_page }} dari {{ files.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="files.prev_page_url" :href="files.prev_page_url" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">Sebelumnya</Link>
                    <Link v-if="files.next_page_url" :href="files.next_page_url" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">Selanjutnya</Link>
                </div>
            </div>
        </div>
    </div>
</template>
