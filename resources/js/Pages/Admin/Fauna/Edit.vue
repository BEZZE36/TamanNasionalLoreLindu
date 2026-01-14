<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import ImageEditorModal from '@/Components/Admin/ImageEditorModal.vue';
import { gsap } from 'gsap';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Bird, Save, ArrowLeft, Upload, X, Plus, Info, Tag, FileText, Image, Sparkles, Pencil, Home, Wand2, Loader2, CheckCircle, Clock, Eye } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({ fauna: { type: Object, required: true }, categories: { type: Object, default: () => ({}) }, conservationStatuses: { type: Object, default: () => ({}) } });

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
const showImageEditor = ref(false);
const editingImageUrl = ref('');
const editingImageType = ref('');
const editingGalleryIndex = ref(-1);
const isGenerating = ref({ name: false, seo: false });
const isSubmitting = ref(false);
const autoSaveStatus = ref('');
let ctx, autoSaveTimer;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.form-section', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'power2.out' });
    });
});
onBeforeUnmount(() => { if (ctx) ctx.revert(); if (autoSaveTimer) clearTimeout(autoSaveTimer); });

const nameLength = computed(() => form.name.length);
const descLength = computed(() => (form.description || '').replace(/<[^>]*>/g, '').length);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

watch(() => [form.name, form.description, form.habitat, form.meta_title, form.meta_description], () => {
    autoSaveStatus.value = 'pending';
    if (autoSaveTimer) clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => { autoSaveStatus.value = 'saved'; }, 2000);
}, { deep: true });

const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
const handleCoverUpload = (e) => { const file = e.target.files?.[0] || e.dataTransfer?.files?.[0]; if (file) { if (!allowedTypes.includes(file.type)) { alert('Format tidak didukung! Hanya JPG, PNG, dan WebP yang diizinkan.'); return; } if (file.size > 2 * 1024 * 1024) { alert('Max 2MB'); return; } form.image = file; imagePreview.value = URL.createObjectURL(file); } };
const handleCoverDrop = (e) => { isDraggingCover.value = false; handleCoverUpload(e); };
const removeCover = () => { form.image = null; imagePreview.value = null; };
const handleGalleryUpload = (e) => { Array.from(e.target.files || e.dataTransfer?.files).forEach(file => { if (!allowedTypes.includes(file.type)) { alert('Format tidak didukung! Hanya JPG, PNG, dan WebP yang diizinkan.'); return; } if (file.size > 5 * 1024 * 1024) { alert('Max 5MB per file'); return; } form.gallery.push(file); galleryPreviews.value.push(URL.createObjectURL(file)); }); };
const handleGalleryDrop = (e) => { isDraggingGallery.value = false; handleGalleryUpload(e); };
const removeGalleryImage = (i) => { form.gallery.splice(i, 1); galleryPreviews.value.splice(i, 1); };
const deleteExistingImage = async (id) => { if (!confirm('Hapus gambar?')) return; try { await fetch(`/admin/fauna/images/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } }); existingImages.value = existingImages.value.filter(img => img.id !== id); } catch (e) {} };
const openCoverEditor = () => { if (imagePreview.value) { editingImageUrl.value = imagePreview.value; editingImageType.value = 'cover'; showImageEditor.value = true; }};
const openGalleryEditor = (i) => { if (galleryPreviews.value[i]) { editingImageUrl.value = galleryPreviews.value[i]; editingImageType.value = 'gallery'; editingGalleryIndex.value = i; showImageEditor.value = true; }};
const handleEditorSave = async (dataUrl) => { const blob = await (await fetch(dataUrl)).blob(); const file = new File([blob], 'edited.jpg', { type: 'image/jpeg' }); if (editingImageType.value === 'cover') { form.image = file; imagePreview.value = dataUrl; } else { form.gallery[editingGalleryIndex.value] = file; galleryPreviews.value[editingGalleryIndex.value] = dataUrl; } showImageEditor.value = false; };

const generateAI = async (type) => {
    isGenerating.value[type] = true;
    try {
        if (type === 'seo') { form.meta_title = form.name.substring(0, 60); form.meta_description = `Informasi lengkap tentang ${form.name}, habitat, dan status konservasi di Taman Nasional Lore Lindu.`.substring(0, 160); }
    } finally { setTimeout(() => { isGenerating.value[type] = false; }, 500); }
};

const submit = () => {
    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('name', form.name);
    fd.append('local_name', form.local_name || '');
    fd.append('scientific_name', form.scientific_name || '');
    fd.append('description', form.description || '');
    fd.append('habitat', form.habitat || '');
    fd.append('category', form.category);
    fd.append('conservation_status', form.conservation_status || '');
    fd.append('meta_title', form.meta_title || '');
    fd.append('meta_description', form.meta_description || '');
    fd.append('is_active', form.is_active ? '1' : '0');
    fd.append('is_featured', form.is_featured ? '1' : '0');
    
    if (form.image) fd.append('image', form.image);
    form.gallery.forEach((file, i) => fd.append(`gallery[${i}]`, file));
    
    router.post(`/admin/fauna/${props.fauna.id}`, fd, {
        onSuccess: () => router.visit('/admin/fauna'),
        onError: (errors) => { form.errors = errors; }
    });
};
</script>

<template>
    <div class="min-h-screen space-y-4">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden"><div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div></div>
            <div class="header-content relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/admin/fauna" class="p-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/20 transition-all"><ArrowLeft class="w-5 h-5 text-white" /></Link>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center"><Pencil class="w-5 h-5 text-white" /></div>
                        <div><h1 class="text-lg font-bold text-white flex items-center gap-2"><Bird class="w-5 h-5" />Edit Fauna</h1><p class="text-amber-100/80 text-xs truncate max-w-xs">{{ fauna.name }}</p></div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div v-if="autoSaveStatus === 'saved'" class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 rounded-lg"><CheckCircle class="w-3.5 h-3.5 text-green-300" /><span class="text-[10px] text-white/80">Tersimpan</span></div>
                    <a v-if="fauna.is_active" :href="`/fauna/${fauna.slug || fauna.id}`" target="_blank" class="p-2 rounded-xl bg-white/10 border border-white/20 hover:bg-white/20 transition-all"><Eye class="w-4 h-4 text-white" /></a>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="form-section bg-white rounded-xl p-4 shadow-lg border border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', fauna.is_active ? 'bg-emerald-100' : 'bg-amber-100']"><CheckCircle :class="['w-5 h-5', fauna.is_active ? 'text-emerald-600' : 'text-amber-600']" /></div>
                <div><p class="text-xs font-bold text-gray-900">{{ fauna.is_active ? 'Dipublikasi' : 'Draft' }}</p><p class="text-[10px] text-gray-500">{{ formatDate(fauna.updated_at) }}</p></div>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-gray-500"><Clock class="w-3.5 h-3.5" /><span>Dibuat {{ formatDate(fauna.created_at) }}</span></div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-2 space-y-4">
                <!-- Basic Info -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><Info class="w-4 h-4 text-amber-600" />Informasi Utama</h2>
                    <div class="space-y-3">
                        <div><div class="flex items-center justify-between mb-1"><label class="text-[11px] font-medium text-gray-700">Nama Fauna *</label><span class="text-[10px] text-gray-400">{{ nameLength }}/100</span></div><input v-model="form.name" type="text" required maxlength="100" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Nama Ilmiah</label><input v-model="form.scientific_name" type="text" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 italic"></div>
                            <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Nama Lokal</label><input v-model="form.local_name" type="text" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500"></div>
                        </div>
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1"><Home class="w-3 h-3 inline" /> Habitat</label><input v-model="form.habitat" type="text" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500"></div>
                    </div>
                </div>

                <!-- Classification -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><Tag class="w-4 h-4 text-purple-600" />Klasifikasi</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Kategori</label><select v-model="form.category" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500"><option value="umum">Umum</option><option value="langka">Langka</option><option value="endemik">Endemik</option></select></div>
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Status Konservasi</label><select v-model="form.conservation_status" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500"><option value="">Pilih Status</option><option v-for="(l, k) in conservationStatuses" :key="k" :value="k">{{ l }}</option></select></div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4"><h2 class="text-sm font-bold text-gray-900 flex items-center gap-2"><FileText class="w-4 h-4 text-blue-600" />Deskripsi</h2><span class="text-[10px] text-gray-400">{{ descLength }} karakter</span></div>
                    <TinyMceEditor v-model="form.description" name="description" id="fauna-edit-desc" :height="350" placeholder="Deskripsi fauna..." />
                </div>

                <!-- SEO -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4"><h2 class="text-sm font-bold text-gray-900 flex items-center gap-2"><Sparkles class="w-4 h-4 text-teal-600" />SEO</h2><button type="button" @click="generateAI('seo')" class="px-2 py-1 rounded-lg bg-teal-100 text-teal-600 text-[9px] font-bold hover:bg-teal-200 flex items-center gap-1"><Wand2 class="w-3 h-3" /> Generate</button></div>
                    <div class="space-y-3">
                        <div><div class="flex justify-between mb-1"><label class="text-[10px] font-medium text-gray-700">Meta Title</label><span class="text-[9px] text-gray-400">{{ form.meta_title?.length || 0 }}/60</span></div><input v-model="form.meta_title" type="text" maxlength="60" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-teal-500"></div>
                        <div><div class="flex justify-between mb-1"><label class="text-[10px] font-medium text-gray-700">Meta Description</label><span class="text-[9px] text-gray-400">{{ form.meta_description?.length || 0 }}/160</span></div><textarea v-model="form.meta_description" rows="2" maxlength="160" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-teal-500 resize-none"></textarea></div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Status -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-amber-500 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><Bird class="w-4 h-4 text-amber-600" />Pengaturan</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl"><div><span class="block text-[11px] font-bold text-gray-900">Status Aktif</span><span class="text-[10px] text-gray-500">Tampilkan</span></div><label class="relative inline-flex items-center cursor-pointer"><input v-model="form.is_active" type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div></label></div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl"><div><span class="block text-[11px] font-bold text-gray-900">Unggulan</span><span class="text-[10px] text-gray-500">Homepage</span></div><label class="relative inline-flex items-center cursor-pointer"><input v-model="form.is_featured" type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div></label></div>
                    </div>
                </div>

                <!-- Foto Cover Section -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-orange-500 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><Image class="w-4 h-4 text-orange-600" />Foto Cover <span class="text-[9px] font-normal text-gray-400">(1 foto)</span></h2>
                    <div v-if="fauna.image_url && !imagePreview" class="mb-3">
                        <div class="relative aspect-video rounded-xl overflow-hidden shadow-lg group">
                            <img :src="fauna.image_url" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-white text-[9px] rounded-lg font-bold">Cover Aktif</span>
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <label class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 cursor-pointer"><Upload class="w-4 h-4" /><input type="file" accept=".jpg,.jpeg,.png,.webp" @change="handleCoverUpload" class="hidden"></label>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="imagePreview" class="relative rounded-xl overflow-hidden shadow-lg group">
                        <img :src="imagePreview" class="w-full h-32 object-cover">
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-orange-500 text-white text-[9px] rounded-lg font-bold">Cover Baru</span>
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" @click.prevent="openCoverEditor" class="p-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600"><Pencil class="w-4 h-4" /></button>
                            <button type="button" @click.prevent="removeCover" class="p-2 bg-red-500 text-white rounded-xl hover:bg-red-600"><X class="w-4 h-4" /></button>
                        </div>
                    </div>
                    <label v-else class="block cursor-pointer" @dragover.prevent="isDraggingCover = true" @dragleave.prevent="isDraggingCover = false" @drop.prevent="handleCoverDrop">
                        <div :class="[isDraggingCover ? 'border-orange-500 bg-orange-50 scale-[1.02]' : 'border-gray-300 hover:border-orange-400']" class="border-2 border-dashed rounded-xl p-5 text-center transition-all"><Upload class="w-7 h-7 mx-auto text-gray-400 mb-1.5" /><p class="text-xs text-gray-600 font-medium">Upload Cover</p></div>
                        <input type="file" accept=".jpg,.jpeg,.png,.webp" @change="handleCoverUpload" class="hidden">
                    </label>
                </div>

                <!-- Galeri Foto Section -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-purple-500 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2"><Image class="w-4 h-4 text-purple-600" />Galeri Foto</h2>
                        <span class="text-[10px] font-medium px-2 py-1 rounded-lg" :class="(existingImages.length + galleryPreviews.length) >= 20 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'">{{ existingImages.length + galleryPreviews.length }}/20</span>
                    </div>
                    <!-- Existing Gallery Images -->
                    <div v-if="existingImages.length" class="mb-3">
                        <p class="text-[10px] text-gray-500 mb-2">Tersimpan</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="img in existingImages" :key="img.id" class="relative aspect-square group rounded-lg overflow-hidden">
                                <img :src="img.image_url" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1">
                                    <button type="button" @click="deleteExistingImage(img.id)" class="p-1 rounded-lg bg-red-500 text-white hover:bg-red-600"><X class="w-3 h-3" /></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- New Gallery Images -->
                    <div v-if="galleryPreviews.length" class="mb-3">
                        <p class="text-[10px] text-gray-500 mb-2">Foto Baru</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="(img, i) in galleryPreviews" :key="i" class="relative aspect-square group rounded-lg overflow-hidden">
                                <img :src="img" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click="openGalleryEditor(i)" class="p-1 bg-blue-500 text-white rounded-lg"><Pencil class="w-3 h-3" /></button>
                                    <button type="button" @click="removeGalleryImage(i)" class="p-1 bg-red-500 text-white rounded-lg"><X class="w-3 h-3" /></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Upload Button -->
                    <label v-if="(existingImages.length + galleryPreviews.length) < 20" class="block cursor-pointer" @dragover.prevent="isDraggingGallery = true" @dragleave.prevent="isDraggingGallery = false" @drop.prevent="handleGalleryDrop">
                        <div :class="[isDraggingGallery ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-400']" class="border-2 border-dashed rounded-xl p-3 text-center transition-all"><Plus class="w-5 h-5 mx-auto text-gray-400" /><p class="text-[10px] text-gray-500 mt-0.5">Tambah Foto (max 20)</p></div>
                        <input type="file" accept=".jpg,.jpeg,.png,.webp" multiple @change="handleGalleryUpload" class="hidden">
                    </label>
                    <p v-else class="text-center py-3 text-amber-600 text-[10px] font-medium bg-amber-50 rounded-xl">Batas maksimal 20 foto tercapai</p>
                </div>
            </div>
        </form>

        <!-- Floating Buttons -->
        <div class="fixed bottom-6 right-6 flex gap-3 z-40">
            <Link href="/admin/fauna" class="px-4 py-3 bg-white text-gray-700 rounded-xl shadow-lg border border-gray-200 text-xs font-semibold hover:bg-gray-50 transition-all">Batal</Link>
            <button @click="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl shadow-lg shadow-amber-500/30 text-xs font-bold hover:shadow-amber-500/50 transition-all flex items-center gap-2 disabled:opacity-50"><Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" /><Save v-else class="w-4 h-4" />{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
        </div>

        <ImageEditorModal :show="showImageEditor" :image-url="editingImageUrl" @close="showImageEditor = false" @save="handleEditorSave" />
    </div>
</template>
