<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Image, Save, ArrowLeft, Upload, X, Plus, Video, Calendar, MapPin, Tag, Search, Sparkles, Wand2, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({ destinations: { type: Array, default: () => [] }, categories: { type: Array, default: () => [] } });

const form = useForm({
    title: '', gallery_category_id: '', location: '', description: '',
    capture_date: '', destination_id: '', tags: [],
    is_active: true, is_featured: false,
    cover_image: null, images: [], video_urls: [''],
    meta_title: '', meta_description: '', meta_keywords: ''
});

const coverPreview = ref(null);
const galleryPreviews = ref([]);
const newTag = ref('');
const isDragging = ref(false);
const isGenerating = ref({ title: false, description: false, seo: false });
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.form-section', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'power2.out' });
    });
});
onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const titleLength = computed(() => form.title.length);
const descLength = computed(() => form.description.length);

const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
const handleCoverUpload = (e) => { const file = e.target.files[0]; if (file) { if (!allowedTypes.includes(file.type)) { alert('Format tidak didukung! Hanya JPG, PNG, dan WebP yang diizinkan.'); return; } form.cover_image = file; coverPreview.value = URL.createObjectURL(file); } };
const handleGalleryUpload = (e) => { Array.from(e.target.files).forEach(file => { if (!allowedTypes.includes(file.type)) { alert('Format tidak didukung! Hanya JPG, PNG, dan WebP yang diizinkan.'); return; } form.images.push(file); galleryPreviews.value.push(URL.createObjectURL(file)); }); };
const handleDrop = (e) => { isDragging.value = false; Array.from(e.dataTransfer.files).filter(f => allowedTypes.includes(f.type)).forEach(file => { form.images.push(file); galleryPreviews.value.push(URL.createObjectURL(file)); }); };
const removeGalleryImage = (i) => { form.images.splice(i, 1); galleryPreviews.value.splice(i, 1); };
const addTag = () => { if (newTag.value.trim() && !form.tags.includes(newTag.value.trim())) { form.tags.push(newTag.value.trim()); newTag.value = ''; } };
const removeTag = (i) => form.tags.splice(i, 1);
const handleTagKeydown = (e) => { if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addTag(); }};
const addVideoUrl = () => form.video_urls.push('');
const removeVideoUrl = (i) => form.video_urls.splice(i, 1);

const generateAI = async (type) => {
    isGenerating.value[type] = true;
    try {
        if (type === 'title') { form.title = `Album Galeri: ${form.location || 'TNLL'} - ${new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}`; }
        else if (type === 'description') { form.description = `Dokumentasi visual keindahan ${form.location || 'Taman Nasional Lore Lindu'}. Album ini berisi kumpulan foto-foto memukau yang menampilkan keanekaragaman alam dan keindahan lanskap yang ada di kawasan konservasi ini.`; }
        else if (type === 'seo') { form.meta_title = form.title.substring(0, 70); form.meta_description = form.description.substring(0, 170); form.meta_keywords = form.tags.length ? form.tags.join(', ') : 'galeri, foto, tnll, taman nasional'; }
    } finally { setTimeout(() => { isGenerating.value[type] = false; }, 500); }
};

const submit = () => {
    const fd = new FormData();
    fd.append('title', form.title);
    fd.append('gallery_category_id', form.gallery_category_id || '');
    fd.append('location', form.location || '');
    fd.append('description', form.description || '');
    fd.append('capture_date', form.capture_date || '');
    fd.append('destination_id', form.destination_id || '');
    fd.append('tags', JSON.stringify(form.tags));
    fd.append('is_active', form.is_active ? '1' : '0');
    fd.append('is_featured', form.is_featured ? '1' : '0');
    fd.append('meta_title', form.meta_title || '');
    fd.append('meta_description', form.meta_description || '');
    fd.append('meta_keywords', form.meta_keywords || '');
    if (form.cover_image) fd.append('cover_image', form.cover_image);
    form.images.forEach((img, i) => fd.append(`images[${i}]`, img));
    form.video_urls.filter(u => u.trim()).forEach((url, i) => fd.append(`video_urls[${i}]`, url));
    
    router.post('/admin/gallery', fd, {
        onSuccess: () => router.visit('/admin/gallery'),
        onError: (errors) => { form.errors = errors; }
    });
};
</script>

<template>
    <div class="min-h-screen space-y-4">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden"><div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div></div>
            <div class="header-content relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/admin/gallery" class="p-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/20 transition-all"><ArrowLeft class="w-5 h-5 text-white" /></Link>
                    <div><h1 class="text-lg font-bold text-white flex items-center gap-2"><Image class="w-5 h-5" />Tambah Album Galeri</h1><p class="text-purple-100/80 text-xs">Buat album baru untuk galeri TNLL</p></div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Album Info -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><Sparkles class="w-4 h-4 text-violet-600" />Informasi Album</h2>
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-medium text-gray-700">Judul Album *</label>
                                <span class="text-[10px] text-gray-400">{{ titleLength }}/100</span>
                            </div>
                            <div class="flex gap-2">
                                <input v-model="form.title" type="text" maxlength="100" required placeholder="Keindahan Danau Tambing" class="flex-1 px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all">
                                <button type="button" @click="generateAI('title')" :disabled="isGenerating.title" class="px-3 py-2 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white text-[10px] font-bold hover:shadow-lg disabled:opacity-50 flex items-center gap-1.5"><Loader2 v-if="isGenerating.title" class="w-3.5 h-3.5 animate-spin" /><Wand2 v-else class="w-3.5 h-3.5" /> AI</button>
                            </div>
                            <p v-if="form.errors.title" class="text-red-500 text-[10px] mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-700 mb-1">Kategori</label>
                                <select v-model="form.gallery_category_id" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-violet-500"><option value="">-- Pilih Kategori --</option><option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option></select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-700 mb-1"><MapPin class="w-3 h-3 inline" /> Lokasi</label>
                                <input v-model="form.location" type="text" placeholder="Lembah Bada, Sigi" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-violet-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-700 mb-1"><Tag class="w-3 h-3 inline" /> Tags</label>
                                <div class="w-full px-3 py-2 rounded-xl border-2 border-gray-200 focus-within:border-violet-500">
                                    <div v-if="form.tags.length" class="flex flex-wrap gap-1 mb-1.5"><span v-for="(tag, i) in form.tags" :key="i" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-violet-100 text-violet-700">{{ tag }}<button type="button" @click="removeTag(i)" class="ml-1 text-violet-500 hover:text-violet-700">&times;</button></span></div>
                                    <input v-model="newTag" @keydown="handleTagKeydown" @blur="addTag" type="text" class="w-full bg-transparent border-none focus:ring-0 p-0 text-[11px]" placeholder="Ketik tag Enter...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-700 mb-1"><Calendar class="w-3 h-3 inline" /> Tanggal Pengambilan</label>
                                <input v-model="form.capture_date" type="date" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-violet-500">
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-medium text-gray-700">Deskripsi Album</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-gray-400">{{ descLength }}/500</span>
                                    <button type="button" @click="generateAI('description')" :disabled="isGenerating.description" class="px-2 py-1 rounded-lg bg-violet-100 text-violet-600 text-[9px] font-bold hover:bg-violet-200 disabled:opacity-50 flex items-center gap-1"><Wand2 class="w-3 h-3" /> AI</button>
                                </div>
                            </div>
                            <textarea v-model="form.description" rows="3" maxlength="500" placeholder="Deskripsikan album ini..." class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-violet-500 resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Foto Cover Section -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-violet-500 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><Image class="w-4 h-4 text-violet-600" />Foto Cover <span class="text-[9px] font-normal text-gray-400">(1 foto) *</span></h2>
                    <div v-if="coverPreview" class="relative rounded-xl overflow-hidden shadow-lg group">
                        <img :src="coverPreview" class="w-full h-48 object-cover">
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-violet-500 text-white text-[9px] rounded-lg font-bold">Cover</span>
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <label class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 cursor-pointer"><Upload class="w-4 h-4" /><input type="file" accept=".jpg,.jpeg,.png,.webp" @change="handleCoverUpload" class="hidden"></label>
                        </div>
                    </div>
                    <label v-else class="block cursor-pointer">
                        <div class="border-2 border-dashed border-gray-300 hover:border-violet-400 rounded-xl p-5 text-center transition-all"><Upload class="w-7 h-7 mx-auto text-gray-400 mb-1.5" /><p class="text-xs text-gray-600 font-medium">Upload Cover</p><p class="text-[10px] text-gray-400">JPG, PNG, WEBP (Max 5MB)</p></div>
                        <input type="file" accept=".jpg,.jpeg,.png,.webp" @change="handleCoverUpload" class="hidden">
                    </label>
                    <p v-if="form.errors.cover_image" class="text-red-500 text-[10px] mt-1">{{ form.errors.cover_image }}</p>
                </div>

                <!-- Galeri Foto Section -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-purple-500 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2"><Image class="w-4 h-4 text-purple-600" />Galeri Foto</h2>
                        <span class="text-[10px] font-medium px-2 py-1 rounded-lg" :class="galleryPreviews.length >= 20 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'">{{ galleryPreviews.length }}/20</span>
                    </div>
                    <!-- Gallery Images -->
                    <div v-if="galleryPreviews.length" class="mb-3">
                        <p class="text-[10px] text-gray-500 mb-2">Foto Baru</p>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            <div v-for="(img, i) in galleryPreviews" :key="i" class="relative aspect-square group rounded-lg overflow-hidden">
                                <img :src="img" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click.stop="removeGalleryImage(i)" class="p-1.5 rounded-lg bg-red-500 text-white"><X class="w-3 h-3" /></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Upload Button -->
                    <div v-if="galleryPreviews.length < 20" @click="$refs.galleryInput.click()" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop" :class="['border-2 border-dashed rounded-xl p-3 text-center transition-all cursor-pointer', isDragging ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-400']"><Plus :class="['w-5 h-5 mx-auto', isDragging ? 'text-purple-500' : 'text-gray-400']" /><p class="text-[10px] text-gray-500 mt-0.5">{{ isDragging ? 'Lepas!' : 'Tambah Foto (max 20)' }}</p></div>
                    <input ref="galleryInput" type="file" multiple accept=".jpg,.jpeg,.png,.webp" @change="handleGalleryUpload" class="hidden">
                    <p v-if="galleryPreviews.length >= 20" class="text-center py-3 text-amber-600 text-[10px] font-medium bg-amber-50 rounded-xl">Batas maksimal 20 foto tercapai</p>
                </div>

                <!-- Video URLs -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><Video class="w-4 h-4 text-red-600" />Video YouTube <span class="text-gray-400 text-[10px] font-normal">(Opsional)</span></h2>
                    <div class="space-y-2">
                        <div v-for="(url, i) in form.video_urls" :key="i" class="flex gap-2">
                            <input v-model="form.video_urls[i]" type="url" placeholder="https://youtube.com/watch?v=..." class="flex-1 px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-red-500">
                            <button v-if="form.video_urls.length > 1" type="button" @click="removeVideoUrl(i)" class="p-2 rounded-xl bg-red-50 text-red-500 hover:bg-red-100"><X class="w-4 h-4" /></button>
                        </div>
                        <button type="button" @click="addVideoUrl" class="w-full py-2 rounded-xl border-2 border-dashed border-gray-300 text-gray-500 hover:border-red-400 hover:text-red-500 text-xs font-medium">+ Tambah Video</button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Publication -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-violet-500 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Publikasi</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                            <div><span class="block text-[11px] font-bold text-gray-900">Status Aktif</span><span class="text-[10px] text-gray-500">Tampilkan di galeri</span></div>
                            <label class="relative inline-flex items-center cursor-pointer"><input v-model="form.is_active" type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div></label>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                            <div><span class="block text-[11px] font-bold text-gray-900">Unggulan</span><span class="text-[10px] text-gray-500">Tampilkan di home</span></div>
                            <label class="relative inline-flex items-center cursor-pointer"><input v-model="form.is_featured" type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div></label>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-700 mb-1">Destinasi</label>
                            <select v-model="form.destination_id" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-violet-500"><option value="">-- Pilih --</option><option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option></select>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2"><Search class="w-4 h-4 text-indigo-600" />SEO</h2>
                        <button type="button" @click="generateAI('seo')" :disabled="isGenerating.seo" class="px-2 py-1 rounded-lg bg-indigo-100 text-indigo-600 text-[9px] font-bold hover:bg-indigo-200 disabled:opacity-50 flex items-center gap-1"><Wand2 class="w-3 h-3" /> Generate</button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between mb-1"><label class="text-[10px] font-medium text-gray-700">Meta Title</label><span class="text-[9px] text-gray-400">{{ form.meta_title.length }}/70</span></div>
                            <input v-model="form.meta_title" type="text" maxlength="70" placeholder="Judul SEO" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500">
                        </div>
                        <div>
                            <div class="flex justify-between mb-1"><label class="text-[10px] font-medium text-gray-700">Meta Description</label><span class="text-[9px] text-gray-400">{{ form.meta_description.length }}/170</span></div>
                            <textarea v-model="form.meta_description" rows="2" maxlength="170" placeholder="Deskripsi..." class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-700 mb-1">Keywords</label>
                            <input v-model="form.meta_keywords" type="text" maxlength="255" placeholder="galeri, foto, tnll" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Floating Save Button -->
        <div class="fixed bottom-6 right-6 flex gap-3 z-40">
            <Link href="/admin/gallery" class="px-4 py-3 bg-white text-gray-700 rounded-xl shadow-lg border border-gray-200 text-xs font-semibold hover:bg-gray-50 transition-all">Batal</Link>
            <button @click="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl shadow-lg shadow-violet-500/30 text-xs font-bold hover:shadow-violet-500/50 transition-all flex items-center gap-2 disabled:opacity-50"><Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" /><Save v-else class="w-4 h-4" />{{ form.processing ? 'Menyimpan...' : 'Simpan Album' }}</button>
        </div>
    </div>
</template>
