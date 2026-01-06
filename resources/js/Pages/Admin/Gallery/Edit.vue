<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Image, Save, ArrowLeft, Upload, X, Plus, Video, Calendar, MapPin, Tag, Info, Search, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    gallery: { type: Object, required: true },
    destinations: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] }
});

const form = useForm({
    title: props.gallery.title || '', gallery_category_id: props.gallery.gallery_category_id || '',
    location: props.gallery.location || '', description: props.gallery.description || '',
    capture_date: props.gallery.capture_date ? props.gallery.capture_date.split('T')[0] : '',
    destination_id: props.gallery.destination_id || '', tags: props.gallery.tags || [],
    is_active: props.gallery.is_active ?? true, is_featured: props.gallery.is_featured ?? false,
    cover_image: null, images: [], video_urls: props.gallery.video_urls?.length ? props.gallery.video_urls : [''],
    meta_title: props.gallery.meta_title || '', meta_description: props.gallery.meta_description || '',
    meta_keywords: props.gallery.meta_keywords || ''
});

const coverPreview = ref(null);
const galleryPreviews = ref([]);
const existingMedia = ref(props.gallery.media || []);
const newTag = ref('');
const isDragging = ref(false);

// Cover image handling
const handleCoverUpload = (e) => {
    const file = e.target.files[0];
    if (file) { form.cover_image = file; coverPreview.value = URL.createObjectURL(file); }
};

// Gallery images handling
const handleGalleryUpload = (e) => {
    Array.from(e.target.files).forEach(file => {
        form.images.push(file);
        galleryPreviews.value.push(URL.createObjectURL(file));
    });
};

const handleDrop = (e) => {
    isDragging.value = false;
    const files = e.dataTransfer.files;
    Array.from(files).filter(f => f.type.startsWith('image/')).forEach(file => {
        form.images.push(file);
        galleryPreviews.value.push(URL.createObjectURL(file));
    });
};

const removeGalleryImage = (i) => { form.images.splice(i, 1); galleryPreviews.value.splice(i, 1); };

// Delete existing media
const deleteMedia = async (media) => {
    if (!confirm('Hapus media ini?')) return;
    await fetch(`/admin/gallery/media/${media.id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
    });
    existingMedia.value = existingMedia.value.filter(m => m.id !== media.id);
};

// Tags handling
const addTag = () => {
    if (newTag.value.trim() && !form.tags.includes(newTag.value.trim())) {
        form.tags.push(newTag.value.trim());
        newTag.value = '';
    }
};
const removeTag = (i) => form.tags.splice(i, 1);
const handleTagKeydown = (e) => { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addTag(); }};

// Video URLs handling
const addVideoUrl = () => form.video_urls.push('');
const removeVideoUrl = (i) => form.video_urls.splice(i, 1);

const submit = () => {
    form.transform(data => {
        const fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('title', data.title);
        fd.append('gallery_category_id', data.gallery_category_id || '');
        fd.append('location', data.location || '');
        fd.append('description', data.description || '');
        fd.append('capture_date', data.capture_date || '');
        fd.append('destination_id', data.destination_id || '');
        fd.append('tags', JSON.stringify(data.tags));
        fd.append('is_active', data.is_active ? '1' : '0');
        fd.append('is_featured', data.is_featured ? '1' : '0');
        fd.append('meta_title', data.meta_title || '');
        fd.append('meta_description', data.meta_description || '');
        fd.append('meta_keywords', data.meta_keywords || '');
        if (data.cover_image) fd.append('cover_image', data.cover_image);
        data.images.forEach((img, i) => fd.append(`images[${i}]`, img));
        data.video_urls.filter(u => u.trim()).forEach((url, i) => fd.append(`video_urls[${i}]`, url));
        return fd;
    }).post(`/admin/gallery/${props.gallery.id}`, { forceFormData: true, onSuccess: () => router.visit('/admin/gallery') });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link href="/admin/gallery" class="p-2 rounded-xl bg-violet-100 hover:bg-violet-200 transition-colors">
                <ArrowLeft class="w-5 h-5 text-violet-600" />
            </Link>
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <Image class="w-7 h-7 text-violet-600" />Edit Album Galeri
                </h1>
                <p class="text-gray-500 text-sm">{{ gallery.title }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Album Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <Info class="w-5 h-5 text-violet-600" />Informasi Album
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Album *</label>
                            <input v-model="form.title" type="text" required placeholder="Contoh: Keindahan Danau Tambing"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <select v-model="form.gallery_category_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><MapPin class="w-4 h-4 inline" /> Lokasi</label>
                                <input v-model="form.location" type="text" placeholder="Contoh: Lembah Bada, Sigi"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><Tag class="w-4 h-4 inline" /> Tags</label>
                                <div class="w-full px-4 py-2 rounded-xl border border-gray-200 focus-within:border-violet-500 focus-within:ring-2 focus-within:ring-violet-500/20">
                                    <div v-if="form.tags.length" class="flex flex-wrap gap-2 mb-2">
                                        <span v-for="(tag, i) in form.tags" :key="i" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-violet-100 text-violet-700">
                                            {{ tag }}
                                            <button type="button" @click="removeTag(i)" class="ml-1 text-violet-500 hover:text-violet-700">&times;</button>
                                        </span>
                                    </div>
                                    <input v-model="newTag" @keydown="handleTagKeydown" @blur="addTag" type="text"
                                        class="w-full bg-transparent border-none focus:ring-0 p-0 text-sm" placeholder="Ketik tag lalu tekan Enter...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><Calendar class="w-4 h-4 inline" /> Tanggal Pengambilan</label>
                                <input v-model="form.capture_date" type="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Album</label>
                            <textarea v-model="form.description" rows="4" placeholder="Deskripsikan album ini..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Current Cover -->
                <div v-if="gallery.cover_url && !coverPreview" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Cover Saat Ini</h2>
                    <img :src="gallery.cover_url" class="w-full h-48 object-cover rounded-xl" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                    <p class="text-xs text-gray-500 mt-2">Upload gambar baru di bawah untuk mengganti</p>
                </div>

                <!-- Cover Image Upload -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Image class="w-5 h-5 text-emerald-600" />{{ gallery.cover_url ? 'Ganti Cover' : 'Cover Album *' }}
                    </h2>
                    <label class="block cursor-pointer">
                        <div v-if="coverPreview" class="relative rounded-xl overflow-hidden group">
                            <img :src="coverPreview" class="w-full h-64 object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="bg-white/90 text-gray-900 font-bold px-4 py-2 rounded-lg">Ganti Cover</span>
                            </div>
                        </div>
                        <div v-else class="border-2 border-dashed border-gray-300 hover:border-violet-400 rounded-xl p-8 transition-all text-center">
                            <Upload class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                            <p class="text-sm text-gray-600 font-medium">Klik untuk upload Cover baru</p>
                            <p class="text-xs text-gray-400">JPG, PNG, WEBP (Max 5MB)</p>
                        </div>
                        <input type="file" accept="image/*" @change="handleCoverUpload" class="hidden">
                    </label>
                </div>

                <!-- Existing Media -->
                <div v-if="existingMedia.length" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Media Tersimpan</h2>
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-violet-100 text-violet-700">{{ existingMedia.length }} media</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <div v-for="media in existingMedia" :key="media.id" class="relative aspect-square group rounded-xl overflow-hidden">
                            <img :src="media.thumbnail_url || media.file_url" class="w-full h-full object-cover" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" @click="deleteMedia(media)" class="p-2 rounded-full bg-red-500 text-white hover:bg-red-600">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add New Gallery Images -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <Plus class="w-5 h-5 text-blue-600" />Tambah Foto Baru
                        </h2>
                        <span v-if="galleryPreviews.length" class="px-3 py-1 rounded-full text-sm font-bold bg-violet-100 text-violet-700">{{ galleryPreviews.length }} foto</span>
                    </div>
                    <div @click="$refs.galleryInput.click()" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop"
                        :class="['border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer', isDragging ? 'border-violet-500 bg-violet-50' : 'border-gray-300 hover:border-violet-400']">
                        <Upload :class="['w-8 h-8 mx-auto mb-2', isDragging ? 'text-violet-500' : 'text-gray-400']" />
                        <p class="text-gray-600 text-sm font-medium">{{ isDragging ? 'Lepas file di sini!' : 'Drag & drop atau klik untuk upload' }}</p>
                    </div>
                    <input ref="galleryInput" type="file" multiple accept="image/*" @change="handleGalleryUpload" class="hidden">
                    <div v-if="galleryPreviews.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4">
                        <div v-for="(img, i) in galleryPreviews" :key="i" class="relative aspect-square group rounded-xl overflow-hidden">
                            <img :src="img" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" @click.stop="removeGalleryImage(i)" class="p-2 rounded-full bg-red-500 text-white hover:bg-red-600">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Video URLs -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Video class="w-5 h-5 text-red-600" />Video YouTube <span class="text-gray-400 text-sm font-normal">(Opsional)</span>
                    </h2>
                    <div class="space-y-3">
                        <div v-for="(url, i) in form.video_urls" :key="i" class="flex gap-2">
                            <input v-model="form.video_urls[i]" type="url" placeholder="https://youtube.com/watch?v=..."
                                class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                            <button v-if="form.video_urls.length > 1" type="button" @click="removeVideoUrl(i)"
                                class="p-2.5 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition-colors">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <button type="button" @click="addVideoUrl"
                            class="w-full py-2.5 rounded-xl border-2 border-dashed border-gray-300 text-gray-500 hover:border-red-400 hover:text-red-500 transition-colors font-medium">
                            + Tambah Video Lain
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Publication -->
                <div class="bg-white rounded-2xl shadow-lg border-l-4 border-violet-500 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Publikasi</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div><span class="block text-sm font-bold text-gray-900">Status Aktif</span><span class="text-xs text-gray-500">Tampilkan di galeri</span></div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div><span class="block text-sm font-bold text-gray-900">Unggulan</span><span class="text-xs text-gray-500">Tampilkan di homepage</span></div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input v-model="form.is_featured" type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Destinasi (Opsional)</label>
                            <select v-model="form.destination_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                                <option value="">-- Pilih Destinasi --</option>
                                <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><Search class="w-5 h-5 text-indigo-600" />SEO Metadata</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title <span class="text-gray-400 text-xs">(maks 70)</span></label>
                            <input v-model="form.meta_title" type="text" maxlength="70" placeholder="Judul untuk hasil pencarian"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description <span class="text-gray-400 text-xs">(maks 170)</span></label>
                            <textarea v-model="form.meta_description" rows="2" maxlength="170" placeholder="Deskripsi singkat..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keywords <span class="text-gray-400 text-xs">(pisahkan koma)</span></label>
                            <input v-model="form.meta_keywords" type="text" maxlength="255" placeholder="galeri, foto, tnll"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3 bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold rounded-xl shadow-lg shadow-violet-500/30 hover:shadow-violet-500/50 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Save class="w-5 h-5" />{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                    <Link href="/admin/gallery" class="block w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl text-center transition-colors">
                        Batal
                    </Link>
                </div>
            </div>
        </form>
    </div>
</template>
