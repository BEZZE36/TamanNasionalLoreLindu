<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import ImageEditorModal from '@/Components/Admin/ImageEditorModal.vue';
import { Flower2, Save, ArrowLeft, Upload, X, Plus, Info, Tag, FileText, Image, Sparkles, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    flora: { type: Object, required: true },
    categories: { type: Object, default: () => ({}) },
    conservationStatuses: { type: Object, default: () => ({}) }
});

const form = useForm({
    name: props.flora.name || '',
    local_name: props.flora.local_name || '',
    scientific_name: props.flora.scientific_name || '',
    description: props.flora.description || '',
    category: props.flora.category || 'umum',
    conservation_status: props.flora.conservation_status || '',
    meta_title: props.flora.meta_title || '',
    meta_description: props.flora.meta_description || '',
    is_active: props.flora.is_active ?? true,
    is_featured: props.flora.is_featured ?? false,
    image: null,
    gallery: [],
    _method: 'PUT'
});

const imagePreview = ref(null);
const galleryPreviews = ref([]);
const existingImages = ref(props.flora.images || []);
const isDraggingCover = ref(false);
const isDraggingGallery = ref(false);

// Image Editor state
const showImageEditor = ref(false);
const editingImageUrl = ref('');
const editingImageType = ref(''); // 'cover' or 'gallery'
const editingGalleryIndex = ref(-1);

// Handle cover image upload
const handleCoverUpload = (e) => {
    const file = e.target.files?.[0] || e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File terlalu besar (max 2MB)');
            return;
        }
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const handleCoverDrop = (e) => {
    isDraggingCover.value = false;
    handleCoverUpload(e);
};

const removeCover = () => {
    form.image = null;
    imagePreview.value = null;
};

// Handle gallery upload
const handleGalleryUpload = (e) => {
    const files = e.target.files || e.dataTransfer?.files;
    Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('File terlalu besar (max 5MB)');
            return;
        }
        form.gallery.push(file);
        galleryPreviews.value.push(URL.createObjectURL(file));
    });
};

const handleGalleryDrop = (e) => {
    isDraggingGallery.value = false;
    handleGalleryUpload(e);
};

const removeGalleryImage = (index) => {
    form.gallery.splice(index, 1);
    galleryPreviews.value.splice(index, 1);
};

const deleteExistingImage = async (id) => {
    if (!confirm('Hapus gambar ini?')) return;
    try {
        await fetch(`/admin/flora/images/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        });
        existingImages.value = existingImages.value.filter(img => img.id !== id);
    } catch (e) {
        console.error('Failed to delete image', e);
    }
};

// Image Editor functions
const openCoverEditor = () => {
    if (imagePreview.value) {
        editingImageUrl.value = imagePreview.value;
        editingImageType.value = 'cover';
        showImageEditor.value = true;
    }
};

const openGalleryEditor = (index) => {
    if (galleryPreviews.value[index]) {
        editingImageUrl.value = galleryPreviews.value[index];
        editingImageType.value = 'gallery';
        editingGalleryIndex.value = index;
        showImageEditor.value = true;
    }
};

const handleEditorSave = async (dataUrl) => {
    const res = await fetch(dataUrl);
    const blob = await res.blob();
    const file = new File([blob], 'edited-image.jpg', { type: 'image/jpeg' });
    
    if (editingImageType.value === 'cover') {
        form.image = file;
        imagePreview.value = dataUrl;
    } else if (editingImageType.value === 'gallery') {
        form.gallery[editingGalleryIndex.value] = file;
        galleryPreviews.value[editingGalleryIndex.value] = dataUrl;
    }
    showImageEditor.value = false;
};

const submit = () => {
    form.post(`/admin/flora/${props.flora.id}`, {
        forceFormData: true,
        onSuccess: () => router.visit('/admin/flora')
    });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link href="/admin/flora" class="group p-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all duration-300">
                <ArrowLeft class="w-5 h-5 text-gray-600 group-hover:-translate-x-0.5 transition-transform" />
            </Link>
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <Pencil class="w-7 h-7 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Edit Flora</h1>
                    <p class="text-gray-500 text-sm">{{ flora.name }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 xl:grid-cols-3 gap-3">
            <!-- Left Column -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Card: Basic Info -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-400/20 to-emerald-500/20 rounded-xl flex items-center justify-center">
                            <Info class="w-5 h-5 text-green-600" />
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">Informasi Utama</h2>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Flora <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" required placeholder="Contoh: Anggrek Bulan"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all text-gray-900">
                            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Ilmiah</label>
                                <input v-model="form.scientific_name" type="text" placeholder="Contoh: Phalaenopsis amabilis"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all text-gray-900 italic">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lokal / Lain</label>
                                <input v-model="form.local_name" type="text" placeholder="Contoh: Puspa Pesona"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all text-gray-900">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Category & Conservation -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-400/20 to-orange-500/20 rounded-xl flex items-center justify-center">
                            <Tag class="w-5 h-5 text-amber-600" />
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">Klasifikasi</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                            <select v-model="form.category" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all text-gray-900">
                                <option value="umum">Umum</option>
                                <option value="langka">Langka</option>
                                <option value="endemik">Endemik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Konservasi</label>
                            <select v-model="form.conservation_status" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all text-gray-900">
                                <option value="">Pilih Status</option>
                                <option v-for="(label, key) in conservationStatuses" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card: Description with TinyMCE AI -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-400/20 to-violet-500/20 rounded-xl flex items-center justify-center">
                            <FileText class="w-5 h-5 text-purple-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Deskripsi Lengkap</h2>
                            <p class="text-xs text-gray-500">Gunakan AI untuk generate konten</p>
                        </div>
                    </div>
                    <TinyMceEditor 
                        v-model="form.description" 
                        name="description" 
                        id="description-editor" 
                        :height="450"
                        placeholder="Mulai menulis deskripsi flora..." 
                    />
                </div>

                <!-- Card: SEO -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-400/20 to-indigo-500/20 rounded-xl flex items-center justify-center">
                            <Sparkles class="w-5 h-5 text-blue-600" />
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">SEO & Meta</h2>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Title</label>
                            <input v-model="form.meta_title" type="text" maxlength="60" placeholder="Judul untuk mesin pencari"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all text-gray-900">
                            <p class="text-xs text-gray-400 mt-1">{{ form.meta_title?.length || 0 }}/60 karakter</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label>
                            <textarea v-model="form.meta_description" rows="3" maxlength="160" placeholder="Deskripsi singkat untuk hasil pencarian"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all text-gray-900 resize-none"></textarea>
                            <p class="text-xs text-gray-400 mt-1">{{ form.meta_description?.length || 0 }}/160 karakter</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (Sidebar) -->
            <div class="space-y-4">
                <!-- Card: Publish Options -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-400/20 to-purple-500/20 rounded-xl flex items-center justify-center">
                            <Flower2 class="w-5 h-5 text-indigo-600" />
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">Pengaturan</h2>
                    </div>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="font-medium text-gray-700">Status Aktif</span>
                            </div>
                            <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        </label>
                        <label class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </div>
                                <span class="font-medium text-gray-700">Tampilkan Unggulan</span>
                            </div>
                            <input v-model="form.is_featured" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                        </label>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full mt-6 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Save class="w-5 h-5" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>

                <!-- Card: Current Cover Image -->
                <div v-if="flora.image_url && !imagePreview" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Gambar Saat Ini</h2>
                    <img :src="flora.image_url" class="w-full aspect-video object-cover rounded-xl mb-2">
                    <p class="text-xs text-gray-500">Upload gambar baru untuk mengganti</p>
                </div>

                <!-- Card: Upload New Cover -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-teal-400/20 to-green-500/20 rounded-xl flex items-center justify-center">
                            <Image class="w-5 h-5 text-teal-600" />
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">{{ flora.image_url ? 'Ganti Gambar' : 'Gambar Utama' }}</h2>
                    </div>
                    <label class="block cursor-pointer"
                        @dragover.prevent="isDraggingCover = true"
                        @dragleave.prevent="isDraggingCover = false"
                        @drop.prevent="handleCoverDrop">
                        <div v-if="imagePreview" class="relative rounded-xl overflow-hidden shadow-lg border border-gray-100 group">
                            <img :src="imagePreview" class="w-full h-48 object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click.prevent="removeCover" class="bg-red-500 text-white p-2.5 rounded-xl hover:bg-red-600 transition-colors">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div v-else
                            :class="[isDraggingCover ? 'border-green-500 bg-green-50 scale-[1.02]' : 'border-gray-300 hover:border-green-400 hover:bg-gray-50']"
                            class="relative border-2 border-dashed rounded-xl p-8 transition-all text-center">
                            <div class="space-y-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400 rounded-xl flex items-center justify-center mx-auto">
                                    <Upload class="w-7 h-7" />
                                </div>
                                <div class="text-sm text-gray-600 font-medium">Drag & drop gambar</div>
                                <div class="text-xs text-gray-400">Max 2MB • JPG, PNG, WebP</div>
                            </div>
                        </div>
                        <input type="file" accept="image/*" class="hidden" @change="handleCoverUpload">
                    </label>
                </div>

                <!-- Card: Existing Gallery -->
                <div v-if="existingImages.length" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Galeri Tersimpan</h2>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="img in existingImages" :key="img.id" class="relative aspect-square group">
                            <img :src="img.image_url" class="w-full h-full object-cover rounded-lg">
                            <button type="button" @click="deleteExistingImage(img.id)"
                                class="absolute top-1 right-1 p-1.5 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <X class="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card: Add Gallery -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-400/20 to-rose-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">Tambah Galeri</h2>
                    </div>
                    <div v-if="galleryPreviews.length" class="grid grid-cols-3 gap-2 mb-4">
                        <div v-for="(img, i) in galleryPreviews" :key="i" class="relative aspect-square group">
                            <img :src="img" class="w-full h-full object-cover rounded-lg">
                            <div class="absolute inset-0 bg-black/40 rounded-lg flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click="openGalleryEditor(i)" class="p-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600" title="Edit">
                                    <Pencil class="w-3 h-3" />
                                </button>
                                <button type="button" @click="removeGalleryImage(i)" class="p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600" title="Hapus">
                                    <X class="w-3 h-3" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <label class="block cursor-pointer"
                        @dragover.prevent="isDraggingGallery = true"
                        @dragleave.prevent="isDraggingGallery = false"
                        @drop.prevent="handleGalleryDrop">
                        <div :class="[isDraggingGallery ? 'border-pink-500 bg-pink-50' : 'border-gray-200 hover:border-pink-400']"
                            class="border-2 border-dashed rounded-xl p-4 text-center transition-all">
                            <Plus class="w-6 h-6 mx-auto text-gray-400" />
                            <p class="text-xs text-gray-500 mt-1">Tambah Gambar</p>
                        </div>
                        <input type="file" accept="image/*" multiple class="hidden" @change="handleGalleryUpload">
                    </label>
                </div>
            </div>
        </form>
        
        <!-- Image Editor Modal -->
        <ImageEditorModal 
            :show="showImageEditor" 
            :image-url="editingImageUrl"
            @close="showImageEditor = false"
            @save="handleEditorSave"
        />
    </div>
</template>
