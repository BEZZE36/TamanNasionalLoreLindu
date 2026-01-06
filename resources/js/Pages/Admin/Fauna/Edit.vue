<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import ImageEditorModal from '@/Components/Admin/ImageEditorModal.vue';
import { Bird, Save, ArrowLeft, Upload, X, Plus, Info, Tag, FileText, Image, Sparkles, Pencil, Home } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    fauna: { type: Object, required: true },
    categories: { type: Object, default: () => ({}) },
    conservationStatuses: { type: Object, default: () => ({}) }
});

const form = useForm({
    name: props.fauna.name || '', local_name: props.fauna.local_name || '', scientific_name: props.fauna.scientific_name || '',
    description: props.fauna.description || '', habitat: props.fauna.habitat || '', category: props.fauna.category || 'umum',
    conservation_status: props.fauna.conservation_status || '', meta_title: props.fauna.meta_title || '', meta_description: props.fauna.meta_description || '',
    is_active: props.fauna.is_active ?? true, is_featured: props.fauna.is_featured ?? false, image: null, gallery: [], _method: 'PUT'
});

const imagePreview = ref(null);
const galleryPreviews = ref([]);
const existingImages = ref(props.fauna.images || []);
const isDraggingCover = ref(false);
const isDraggingGallery = ref(false);

// Image Editor state
const showImageEditor = ref(false);
const editingImageUrl = ref('');
const editingImageType = ref('');
const editingGalleryIndex = ref(-1);

// Image handlers
const handleCoverUpload = (e) => {
    const file = e.target.files?.[0] || e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) { if (file.size > 2 * 1024 * 1024) { alert('Max 2MB'); return; } form.image = file; imagePreview.value = URL.createObjectURL(file); }
};
const handleCoverDrop = (e) => { isDraggingCover.value = false; handleCoverUpload(e); };
const removeCover = () => { form.image = null; imagePreview.value = null; };

const handleGalleryUpload = (e) => { Array.from(e.target.files || e.dataTransfer?.files).forEach(file => { if (!file.type.startsWith('image/') || file.size > 5 * 1024 * 1024) return; form.gallery.push(file); galleryPreviews.value.push(URL.createObjectURL(file)); }); };
const handleGalleryDrop = (e) => { isDraggingGallery.value = false; handleGalleryUpload(e); };
const removeGalleryImage = (i) => { form.gallery.splice(i, 1); galleryPreviews.value.splice(i, 1); };

const deleteExistingImage = async (id) => { if (!confirm('Hapus gambar?')) return; try { await fetch(`/admin/fauna/images/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } }); existingImages.value = existingImages.value.filter(img => img.id !== id); } catch (e) {} };

// Image Editor
const openCoverEditor = () => { if (imagePreview.value) { editingImageUrl.value = imagePreview.value; editingImageType.value = 'cover'; showImageEditor.value = true; }};
const openGalleryEditor = (i) => { if (galleryPreviews.value[i]) { editingImageUrl.value = galleryPreviews.value[i]; editingImageType.value = 'gallery'; editingGalleryIndex.value = i; showImageEditor.value = true; }};
const handleEditorSave = async (dataUrl) => { const blob = await (await fetch(dataUrl)).blob(); const file = new File([blob], 'edited.jpg', { type: 'image/jpeg' }); if (editingImageType.value === 'cover') { form.image = file; imagePreview.value = dataUrl; } else { form.gallery[editingGalleryIndex.value] = file; galleryPreviews.value[editingGalleryIndex.value] = dataUrl; } showImageEditor.value = false; };

const submit = () => form.post(`/admin/fauna/${props.fauna.id}`, { forceFormData: true, onSuccess: () => router.visit('/admin/fauna') });
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <Link href="/admin/fauna" class="group p-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all"><ArrowLeft class="w-5 h-5 text-gray-600 group-hover:-translate-x-0.5 transition-transform" /></Link>
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30"><Pencil class="w-7 h-7 text-white" /></div>
                <div><h1 class="text-2xl font-black text-gray-900">Edit Fauna</h1><p class="text-gray-500 text-sm">{{ fauna.name }}</p></div>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 xl:grid-cols-3 gap-3">
            <div class="xl:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-amber-400/20 to-orange-500/20 rounded-xl flex items-center justify-center"><Info class="w-5 h-5 text-amber-600" /></div><h2 class="text-lg font-bold text-gray-900">Informasi Utama</h2></div>
                    <div class="space-y-5">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Nama Fauna <span class="text-red-500">*</span></label><input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div><label class="block text-sm font-semibold text-gray-700 mb-2">Nama Ilmiah</label><input v-model="form.scientific_name" type="text" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all italic"></div>
                            <div><label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lokal</label><input v-model="form.local_name" type="text" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"></div>
                        </div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Habitat</label><input v-model="form.habitat" type="text" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"></div>
                    </div>
                </div>

                <!-- Classification -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-purple-400/20 to-violet-500/20 rounded-xl flex items-center justify-center"><Tag class="w-5 h-5 text-purple-600" /></div><h2 class="text-lg font-bold text-gray-900">Klasifikasi</h2></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label><select v-model="form.category" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"><option value="umum">Umum</option><option value="langka">Langka</option><option value="endemik">Endemik</option></select></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Status Konservasi</label><select v-model="form.conservation_status" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"><option value="">Pilih Status</option><option v-for="(l, k) in conservationStatuses" :key="k" :value="k">{{ l }}</option></select></div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-blue-400/20 to-indigo-500/20 rounded-xl flex items-center justify-center"><FileText class="w-5 h-5 text-blue-600" /></div><div><h2 class="text-lg font-bold text-gray-900">Deskripsi Lengkap</h2><p class="text-xs text-gray-500">Gunakan AI untuk generate konten</p></div></div>
                    <TinyMceEditor v-model="form.description" name="description" id="fauna-edit-desc" :height="400" placeholder="Deskripsi fauna..." />
                </div>

                <!-- SEO -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-teal-400/20 to-cyan-500/20 rounded-xl flex items-center justify-center"><Sparkles class="w-5 h-5 text-teal-600" /></div><h2 class="text-lg font-bold text-gray-900">SEO & Meta</h2></div>
                    <div class="space-y-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Meta Title</label><input v-model="form.meta_title" type="text" maxlength="60" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"><p class="text-xs text-gray-400 mt-1">{{ form.meta_title?.length || 0 }}/60</p></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Meta Description</label><textarea v-model="form.meta_description" rows="3" maxlength="160" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 resize-none"></textarea><p class="text-xs text-gray-400 mt-1">{{ form.meta_description?.length || 0 }}/160</p></div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Status -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-indigo-400/20 to-purple-500/20 rounded-xl flex items-center justify-center"><Bird class="w-5 h-5 text-indigo-600" /></div><h2 class="text-lg font-bold text-gray-900">Pengaturan</h2></div>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="font-medium text-gray-700">Status Aktif</span></div><input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></label>
                        <label class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></div><span class="font-medium text-gray-700">Unggulan</span></div><input v-model="form.is_featured" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-amber-500 focus:ring-amber-500"></label>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full mt-6 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50"><Save class="w-5 h-5" />{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</button>
                </div>

                <!-- Current Cover Image -->
                <div v-if="fauna.image_url && !imagePreview" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Gambar Saat Ini</h2>
                    <img :src="fauna.image_url" class="w-full aspect-video object-cover rounded-xl mb-2">
                    <p class="text-xs text-gray-500">Upload gambar baru untuk mengganti</p>
                </div>

                <!-- Cover Image Upload -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-teal-400/20 to-green-500/20 rounded-xl flex items-center justify-center"><Image class="w-5 h-5 text-teal-600" /></div><h2 class="text-lg font-bold text-gray-900">{{ fauna.image_url ? 'Ganti Gambar' : 'Gambar Utama' }}</h2></div>
                    <label class="block cursor-pointer" @dragover.prevent="isDraggingCover = true" @dragleave.prevent="isDraggingCover = false" @drop.prevent="handleCoverDrop">
                        <div v-if="imagePreview" class="relative rounded-xl overflow-hidden shadow-lg border border-gray-100 group"><img :src="imagePreview" class="w-full h-48 object-cover"><div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button type="button" @click.prevent="openCoverEditor" class="bg-blue-500 text-white p-2.5 rounded-xl hover:bg-blue-600"><Pencil class="w-5 h-5" /></button><button type="button" @click.prevent="removeCover" class="bg-red-500 text-white p-2.5 rounded-xl hover:bg-red-600"><X class="w-5 h-5" /></button></div></div>
                        <div v-else :class="[isDraggingCover ? 'border-amber-500 bg-amber-50 scale-[1.02]' : 'border-gray-300 hover:border-amber-400']" class="border-2 border-dashed rounded-xl p-8 text-center transition-all"><Upload class="w-12 h-12 mx-auto text-gray-400 mb-3" /><p class="text-sm text-gray-600 font-medium">Drag & drop gambar</p><p class="text-xs text-gray-400 mt-1">Max 2MB • JPG, PNG</p></div>
                        <input type="file" accept="image/*" class="hidden" @change="handleCoverUpload">
                    </label>
                </div>

                <!-- Existing Gallery -->
                <div v-if="existingImages.length" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Galeri Tersimpan</h2>
                    <div class="grid grid-cols-3 gap-2"><div v-for="img in existingImages" :key="img.id" class="relative aspect-square group"><img :src="img.image_url" class="w-full h-full object-cover rounded-lg"><button type="button" @click="deleteExistingImage(img.id)" class="absolute top-1 right-1 p-1.5 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"><X class="w-3 h-3" /></button></div></div>
                </div>

                <!-- Gallery Upload -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-pink-400/20 to-rose-500/20 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div><h2 class="text-lg font-bold text-gray-900">Tambah Galeri</h2></div>
                    <div v-if="galleryPreviews.length" class="grid grid-cols-3 gap-2 mb-4"><div v-for="(img, i) in galleryPreviews" :key="i" class="relative aspect-square group"><img :src="img" class="w-full h-full object-cover rounded-lg"><div class="absolute inset-0 bg-black/40 rounded-lg flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"><button type="button" @click="openGalleryEditor(i)" class="p-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600"><Pencil class="w-3 h-3" /></button><button type="button" @click="removeGalleryImage(i)" class="p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600"><X class="w-3 h-3" /></button></div></div></div>
                    <label class="block cursor-pointer" @dragover.prevent="isDraggingGallery = true" @dragleave.prevent="isDraggingGallery = false" @drop.prevent="handleGalleryDrop"><div :class="[isDraggingGallery ? 'border-pink-500 bg-pink-50' : 'border-gray-200 hover:border-pink-400']" class="border-2 border-dashed rounded-xl p-4 text-center transition-all"><Plus class="w-6 h-6 mx-auto text-gray-400" /><p class="text-xs text-gray-500 mt-1">Tambah Gambar</p></div><input type="file" accept="image/*" multiple class="hidden" @change="handleGalleryUpload"></label>
                </div>
            </div>
        </form>
        
        <ImageEditorModal :show="showImageEditor" :image-url="editingImageUrl" @close="showImageEditor = false" @save="handleEditorSave" />
    </div>
</template>
