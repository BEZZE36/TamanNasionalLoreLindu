<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import ImageEditorModal from '@/Components/Admin/ImageEditorModal.vue';
import { gsap } from 'gsap';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { MapPin, Save, ArrowLeft, Upload, X, Plus, Trash2, Pencil, FileText, Image, Map, Loader2, Wand2, CheckCircle, Clock, Eye } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({ destination: { type: Object, required: true } });

const form = useForm({
    name: props.destination.name || '', short_description: props.destination.short_description || '',
    description: props.destination.description || '', address: props.destination.address || '', city: props.destination.city || '',
    operating_hours: props.destination.operating_hours || '', contact_phone: props.destination.contact_phone || '', contact_email: props.destination.contact_email || '',
    latitude: props.destination.latitude || '', longitude: props.destination.longitude || '', google_maps_embed: props.destination.google_maps_embed || '',
    facilities: props.destination.facilities || [], rules: props.destination.rules || [],
    parking_fees: props.destination.parking_fees?.length ? props.destination.parking_fees.map(p => ({ vehicle_type: p.vehicle_type, price: p.price, description: p.description || '' })) : [],
    is_active: props.destination.is_active ?? true, is_featured: props.destination.is_featured ?? false,
    meta_title: props.destination.meta_title || '', meta_description: props.destination.meta_description || '', keywords: props.destination.keywords || '',
    cover_image: null, gallery_images: [],
    prices: props.destination.prices?.length ? props.destination.prices.map(p => ({ id: p.id, ticket_type: p.ticket_type || p.label, price: p.price, description: p.description || '' })) : [{ ticket_type: 'dewasa', price: '', description: '' }],
    _method: 'PUT'
});

const isLoadingAddress = ref(false);
const newFacility = ref('');
const newRule = ref('');
const coverPreview = ref(null);
const galleryPreviews = ref([]);
const existingImages = ref(props.destination.images || []);
const isDraggingCover = ref(false);
const isDraggingGallery = ref(false);
const showImageEditor = ref(false);
const editingImageUrl = ref('');
const editingImageType = ref('');
const editingGalleryIndex = ref(-1);
const isGenerating = ref({ seo: false });
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
const mapPreviewUrl = computed(() => { if (form.google_maps_embed) { const match = form.google_maps_embed.match(/src="([^"]+)"/); return match ? match[1] : null; } return null; });
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

watch(() => [form.name, form.description, form.short_description, form.meta_title, form.meta_description], () => {
    autoSaveStatus.value = 'pending';
    if (autoSaveTimer) clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => { autoSaveStatus.value = 'saved'; }, 2000);
}, { deep: true });

const fetchAddressFromCoordinates = async () => {
    if (!form.latitude || !form.longitude) { alert('Masukkan koordinat terlebih dahulu'); return; }
    isLoadingAddress.value = true;
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${form.latitude}&lon=${form.longitude}&addressdetails=1`, { headers: { 'Accept-Language': 'id' } });
        const data = await response.json();
        if (data.display_name) {
            form.address = data.display_name;
            if (data.address) {
                const addr = data.address; const state = addr.state || '';
                let cityName = addr.city || addr.county || addr.city_district || addr.town || '';
                if (!cityName && data.display_name && state) { const parts = data.display_name.split(',').map(p => p.trim()); const stateIndex = parts.findIndex(p => p === state); if (stateIndex > 0) { const potentialCity = parts[stateIndex - 1]; if (potentialCity && !potentialCity.toLowerCase().startsWith('kecamatan') && !potentialCity.toLowerCase().startsWith('jalan')) { cityName = potentialCity; } } }
                let cleanCity = cityName.replace(/^(Kota |Kabupaten |Kecamatan )/i, '');
                if (cleanCity && state) { form.city = `${cleanCity}, ${state}`; } else if (cleanCity) { form.city = cleanCity; } else if (state) { form.city = state; }
            }
        }
    } catch (e) { alert('Gagal mendapatkan alamat'); }
    finally { isLoadingAddress.value = false; }
};

const addFacility = () => { if (newFacility.value.trim()) { form.facilities.push(newFacility.value.trim()); newFacility.value = ''; } };
const removeFacility = (i) => form.facilities.splice(i, 1);
const addRule = () => { if (newRule.value.trim()) { form.rules.push(newRule.value.trim()); newRule.value = ''; } };
const removeRule = (i) => form.rules.splice(i, 1);
const addPrice = () => form.prices.push({ ticket_type: '', price: '', description: '' });
const removePrice = (i) => form.prices.splice(i, 1);

const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
const handleCoverUpload = (e) => { const file = e.target.files?.[0] || e.dataTransfer?.files?.[0]; if (file) { if (!allowedTypes.includes(file.type)) { alert('Format tidak didukung! Hanya JPG, PNG, dan WebP yang diizinkan.'); return; } if (file.size > 2 * 1024 * 1024) { alert('Max 2MB'); return; } form.cover_image = file; coverPreview.value = URL.createObjectURL(file); } };
const handleCoverDrop = (e) => { isDraggingCover.value = false; handleCoverUpload(e); };
const removeCover = () => { form.cover_image = null; coverPreview.value = null; };
const handleGalleryUpload = (e) => { 
    const files = Array.from(e.target.files || e.dataTransfer?.files);
    const totalImages = existingImages.value.length + galleryPreviews.value.length;
    const remaining = 20 - totalImages;
    if (remaining <= 0) { alert('Maksimal 20 foto untuk galeri'); return; }
    files.slice(0, remaining).forEach(file => { 
        if (!allowedTypes.includes(file.type)) { alert('Format tidak didukung! Hanya JPG, PNG, dan WebP yang diizinkan.'); return; } 
        if (file.size > 5 * 1024 * 1024) { alert('Max 5MB per file'); return; } 
        form.gallery_images.push(file); 
        galleryPreviews.value.push(URL.createObjectURL(file)); 
    }); 
};
const handleGalleryDrop = (e) => { isDraggingGallery.value = false; handleGalleryUpload(e); };
const removeGalleryImage = (i) => { form.gallery_images.splice(i, 1); galleryPreviews.value.splice(i, 1); };
const openCoverEditor = () => { if (coverPreview.value) { editingImageUrl.value = coverPreview.value; editingImageType.value = 'cover'; showImageEditor.value = true; } };
const openGalleryEditor = (i) => { if (galleryPreviews.value[i]) { editingImageUrl.value = galleryPreviews.value[i]; editingImageType.value = 'gallery'; editingGalleryIndex.value = i; showImageEditor.value = true; } };
const handleEditorSave = async (dataUrl) => { const blob = await (await fetch(dataUrl)).blob(); const file = new File([blob], 'edited.jpg', { type: 'image/jpeg' }); if (editingImageType.value === 'cover') { form.cover_image = file; coverPreview.value = dataUrl; } else { form.gallery_images[editingGalleryIndex.value] = file; galleryPreviews.value[editingGalleryIndex.value] = dataUrl; } showImageEditor.value = false; };
const deleteExistingImage = async (id) => { if (!confirm('Hapus gambar?')) return; try { await fetch(`/admin/destinations/images/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } }); existingImages.value = existingImages.value.filter(img => img.id !== id); } catch (e) {} };
const setPrimaryImage = async (id) => { try { await fetch(`/admin/destinations/images/${id}/set-primary`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } }); existingImages.value = existingImages.value.map(img => ({ ...img, is_primary: img.id === id })); } catch (e) {} };

const generateAI = async (type) => {
    isGenerating.value[type] = true;
    try { if (type === 'seo') { form.meta_title = form.name.substring(0, 60); form.meta_description = `Kunjungi ${form.name} di ${form.city || 'Taman Nasional Lore Lindu'}. ${form.short_description || 'Destinasi wisata menarik.'}`.substring(0, 160); } }
    finally { setTimeout(() => { isGenerating.value[type] = false; }, 500); }
};

const submit = () => { 
    if (window.tinymce) { 
        window.tinymce.triggerSave(); 
        const editor = window.tinymce.get('dest-edit-desc'); 
        if (editor) { form.description = editor.getContent(); } 
    } 
    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('name', form.name);
    fd.append('short_description', form.short_description || '');
    fd.append('description', form.description || '');
    fd.append('address', form.address || '');
    fd.append('city', form.city || '');
    fd.append('operating_hours', form.operating_hours || '');
    fd.append('contact_phone', form.contact_phone || '');
    fd.append('contact_email', form.contact_email || '');
    fd.append('latitude', form.latitude || '');
    fd.append('longitude', form.longitude || '');
    fd.append('google_maps_embed', form.google_maps_embed || '');
    fd.append('facilities', JSON.stringify(form.facilities));
    fd.append('rules', JSON.stringify(form.rules));
    fd.append('is_active', form.is_active ? '1' : '0');
    fd.append('is_featured', form.is_featured ? '1' : '0');
    fd.append('meta_title', form.meta_title || '');
    fd.append('meta_description', form.meta_description || '');
    fd.append('keywords', form.keywords || '');
    if (form.cover_image) fd.append('cover_image', form.cover_image);
    form.gallery_images.forEach((file, i) => fd.append(`gallery_images[${i}]`, file));
    fd.append('prices', JSON.stringify(form.prices));
    fd.append('parking_fees', JSON.stringify(form.parking_fees));
    
    router.post(`/admin/destinations/${props.destination.id}`, fd, {
        onSuccess: () => router.visit('/admin/destinations'),
        onError: (errors) => { form.errors = errors; }
    });
};
</script>

<template>
    <div class="min-h-screen space-y-4">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-500 via-green-500 to-teal-500 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden"><div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div></div>
            <div class="header-content relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/admin/destinations" class="p-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/20 transition-all"><ArrowLeft class="w-5 h-5 text-white" /></Link>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center"><Pencil class="w-5 h-5 text-white" /></div>
                        <div><h1 class="text-lg font-bold text-white flex items-center gap-2"><MapPin class="w-5 h-5" />Edit Destinasi</h1><p class="text-emerald-100/80 text-xs truncate max-w-xs">{{ destination.name }}</p></div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div v-if="autoSaveStatus === 'saved'" class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 rounded-lg"><CheckCircle class="w-3.5 h-3.5 text-green-300" /><span class="text-[10px] text-white/80">Tersimpan</span></div>
                    <a v-if="destination.is_active" :href="`/destinations/${destination.slug || destination.id}`" target="_blank" class="p-2 rounded-xl bg-white/10 border border-white/20 hover:bg-white/20 transition-all"><Eye class="w-4 h-4 text-white" /></a>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="form-section bg-white rounded-xl p-4 shadow-lg border border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', destination.is_active ? 'bg-emerald-100' : 'bg-amber-100']"><CheckCircle :class="['w-5 h-5', destination.is_active ? 'text-emerald-600' : 'text-amber-600']" /></div>
                <div><p class="text-xs font-bold text-gray-900">{{ destination.is_active ? 'Dipublikasi' : 'Draft' }}</p><p class="text-[10px] text-gray-500">{{ formatDate(destination.updated_at) }}</p></div>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-gray-500"><Clock class="w-3.5 h-3.5" /><span>Dibuat {{ formatDate(destination.created_at) }}</span></div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <!-- Basic Info -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><MapPin class="w-4 h-4 text-emerald-600" />Informasi Dasar</h2>
                    <div class="space-y-3">
                        <div><div class="flex justify-between mb-1"><label class="text-[11px] font-medium text-gray-700">Nama Destinasi *</label><span class="text-[10px] text-gray-400">{{ nameLength }}/100</span></div><input v-model="form.name" type="text" required maxlength="100" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"></div>
                        <div><div class="flex justify-between mb-1"><label class="text-[11px] font-medium text-gray-700">Deskripsi Singkat</label><span class="text-[10px] text-gray-400">{{ form.short_description?.length || 0 }}/255</span></div><input v-model="form.short_description" type="text" maxlength="255" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"></div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><FileText class="w-4 h-4 text-blue-600" />Deskripsi Lengkap</h2>
                    <TinyMceEditor v-model="form.description" name="description" id="dest-edit-desc" :height="350" placeholder="Deskripsi destinasi..." />
                </div>

                <!-- Location -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><MapPin class="w-4 h-4 text-rose-600" />Lokasi & Peta</h2>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Latitude</label><input v-model="form.latitude" type="number" step="any" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-rose-500"></div>
                            <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Longitude</label><input v-model="form.longitude" type="number" step="any" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-rose-500"></div>
                        </div>
                        <button type="button" @click="fetchAddressFromCoordinates" :disabled="isLoadingAddress" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-[10px] font-bold shadow-lg disabled:opacity-50"><Loader2 v-if="isLoadingAddress" class="w-3 h-3 animate-spin" /><MapPin v-else class="w-3 h-3" />{{ isLoadingAddress ? 'Memuat...' : 'Dapatkan Alamat' }}</button>
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Alamat Lengkap</label><textarea v-model="form.address" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500 resize-none"></textarea></div>
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Kota *</label><input v-model="form.city" type="text" required class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"></div>
                    </div>
                </div>

                <!-- Map Embed -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><Map class="w-4 h-4 text-teal-600" />Embed Peta</h2>
                    <textarea v-model="form.google_maps_embed" rows="3" class="w-full px-3 py-2 text-[10px] font-mono rounded-xl border-2 border-gray-200 focus:border-teal-500 resize-none" placeholder='<iframe src="..."></iframe>'></textarea>
                    <div v-if="mapPreviewUrl" class="mt-3 rounded-xl overflow-hidden border border-gray-200 shadow-lg"><iframe :src="mapPreviewUrl" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe></div>
                </div>

                <!-- Facilities & Rules -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-4">Fasilitas & Aturan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-2">Fasilitas</label><div class="flex gap-2 mb-2"><input v-model="newFacility" @keyup.enter.prevent="addFacility" type="text" placeholder="Tambah fasilitas" class="flex-1 px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"><button type="button" @click="addFacility" class="p-2 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600"><Plus class="w-3 h-3" /></button></div><div class="flex flex-wrap gap-1"><span v-for="(f, i) in form.facilities" :key="i" class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px]">{{ f }}<button type="button" @click="removeFacility(i)" class="hover:text-emerald-900"><X class="w-2.5 h-2.5" /></button></span></div></div>
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-2">Aturan</label><div class="flex gap-2 mb-2"><input v-model="newRule" @keyup.enter.prevent="addRule" type="text" placeholder="Tambah aturan" class="flex-1 px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500"><button type="button" @click="addRule" class="p-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600"><Plus class="w-3 h-3" /></button></div><div class="flex flex-wrap gap-1"><span v-for="(r, i) in form.rules" :key="i" class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px]">{{ r }}<button type="button" @click="removeRule(i)" class="hover:text-amber-900"><X class="w-2.5 h-2.5" /></button></span></div></div>
                    </div>
                </div>

                <!-- Prices -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4"><h2 class="text-sm font-bold text-gray-900">Harga Tiket</h2><button type="button" @click="addPrice" class="px-2 py-1 bg-emerald-500 text-white rounded-lg text-[10px] font-medium hover:bg-emerald-600 flex items-center gap-1"><Plus class="w-3 h-3" />Tambah</button></div>
                    <div class="space-y-2"><div v-for="(price, i) in form.prices" :key="i" class="grid grid-cols-1 md:grid-cols-4 gap-2 p-3 bg-gray-50 rounded-xl"><input v-model="price.ticket_type" type="text" placeholder="Jenis tiket" class="px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"><input v-model="price.price" type="number" placeholder="Harga" class="px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"><input v-model="price.description" type="text" placeholder="Keterangan" class="px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"><button v-if="form.prices.length > 1" type="button" @click="removePrice(i)" class="p-2 text-red-500 hover:bg-red-50 rounded-xl self-center"><Trash2 class="w-3 h-3" /></button></div></div>
                </div>

                <!-- Parking Fees -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4"><h2 class="text-sm font-bold text-gray-900">Biaya Parkir</h2><button type="button" @click="form.parking_fees.push({ vehicle_type: '', price: '', description: '' })" class="px-2 py-1 bg-blue-500 text-white rounded-lg text-[10px] font-medium hover:bg-blue-600 flex items-center gap-1"><Plus class="w-3 h-3" />Tambah</button></div>
                    <div class="space-y-2"><div v-for="(fee, i) in form.parking_fees" :key="i" class="grid grid-cols-1 md:grid-cols-4 gap-2 p-3 bg-blue-50 rounded-xl"><input v-model="fee.vehicle_type" type="text" placeholder="Jenis kendaraan" class="px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500"><input v-model="fee.price" type="number" placeholder="Biaya" class="px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500"><input v-model="fee.description" type="text" placeholder="Keterangan" class="px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500"><button type="button" @click="form.parking_fees.splice(i, 1)" class="p-2 text-red-500 hover:bg-red-50 rounded-xl self-center"><Trash2 class="w-3 h-3" /></button></div></div>
                    <p v-if="!form.parking_fees.length" class="text-center py-3 text-gray-400 text-[10px]">Belum ada biaya parkir</p>
                </div>

                <!-- SEO -->
                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4"><h2 class="text-sm font-bold text-gray-900">SEO</h2><button type="button" @click="generateAI('seo')" class="px-2 py-1 rounded-lg bg-indigo-100 text-indigo-600 text-[9px] font-bold hover:bg-indigo-200 flex items-center gap-1"><Wand2 class="w-3 h-3" /> Generate</button></div>
                    <div class="space-y-3">
                        <div><div class="flex justify-between mb-1"><label class="text-[10px] font-medium text-gray-700">Meta Title</label><span class="text-[9px] text-gray-400">{{ form.meta_title?.length || 0 }}/60</span></div><input v-model="form.meta_title" type="text" maxlength="60" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500"></div>
                        <div><div class="flex justify-between mb-1"><label class="text-[10px] font-medium text-gray-700">Meta Description</label><span class="text-[9px] text-gray-400">{{ form.meta_description?.length || 0 }}/160</span></div><textarea v-model="form.meta_description" rows="2" maxlength="160" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500 resize-none"></textarea></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-emerald-500 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Status</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl"><div><span class="block text-[11px] font-bold text-gray-900">Status Aktif</span></div><label class="relative inline-flex items-center cursor-pointer"><input v-model="form.is_active" type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div></label></div>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl"><div><span class="block text-[11px] font-bold text-gray-900">Unggulan</span></div><label class="relative inline-flex items-center cursor-pointer"><input v-model="form.is_featured" type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div></label></div>
                    </div>
                </div>

                <!-- Cover Image Section -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-teal-500 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><Image class="w-4 h-4 text-teal-600" />Foto Cover <span class="text-[9px] font-normal text-gray-400">(1 foto)</span></h2>
                    <div v-if="existingImages.find(img => img.is_primary)" class="mb-3">
                        <div class="relative aspect-video rounded-xl overflow-hidden shadow-lg group">
                            <img :src="existingImages.find(img => img.is_primary)?.image_url" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-amber-500 text-white text-[9px] rounded-lg font-bold">Cover Aktif</span>
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click="deleteExistingImage(existingImages.find(img => img.is_primary)?.id)" class="p-2 bg-red-500 text-white rounded-lg hover:bg-red-600"><X class="w-4 h-4" /></button>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="coverPreview" class="relative rounded-xl overflow-hidden shadow-lg group">
                        <img :src="coverPreview" class="w-full h-32 object-cover">
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" @click.prevent="openCoverEditor" class="p-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600"><Pencil class="w-4 h-4" /></button>
                            <button type="button" @click.prevent="removeCover" class="p-2 bg-red-500 text-white rounded-xl hover:bg-red-600"><X class="w-4 h-4" /></button>
                        </div>
                    </div>
                    <label v-else class="block cursor-pointer" @dragover.prevent="isDraggingCover = true" @dragleave.prevent="isDraggingCover = false" @drop.prevent="handleCoverDrop">
                        <div :class="[isDraggingCover ? 'border-teal-500 bg-teal-50 scale-[1.02]' : 'border-gray-300 hover:border-teal-400']" class="border-2 border-dashed rounded-xl p-5 text-center transition-all"><Upload class="w-7 h-7 mx-auto text-gray-400 mb-1.5" /><p class="text-xs text-gray-600 font-medium">Upload Cover</p></div>
                        <input type="file" accept=".jpg,.jpeg,.png,.webp" @change="handleCoverUpload" class="hidden">
                    </label>
                </div>

                <!-- Gallery Section -->
                <div class="form-section bg-white rounded-2xl shadow-lg border-l-4 border-purple-500 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2"><Image class="w-4 h-4 text-purple-600" />Galeri Foto</h2>
                        <span class="text-[10px] font-medium px-2 py-1 rounded-lg" :class="(existingImages.filter(img => !img.is_primary).length + galleryPreviews.length) >= 20 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'">{{ existingImages.filter(img => !img.is_primary).length + galleryPreviews.length }}/20</span>
                    </div>
                    <!-- Existing Gallery Images -->
                    <div v-if="existingImages.filter(img => !img.is_primary).length" class="mb-3">
                        <p class="text-[10px] text-gray-500 mb-2">Tersimpan</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="img in existingImages.filter(i => !i.is_primary)" :key="img.id" class="relative aspect-square group rounded-lg overflow-hidden">
                                <img :src="img.image_url" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1">
                                    <button type="button" @click="setPrimaryImage(img.id)" class="p-1 rounded-lg bg-white/20 hover:bg-amber-500 text-white text-xs">★</button>
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
                    <label v-if="(existingImages.filter(img => !img.is_primary).length + galleryPreviews.length) < 20" class="block cursor-pointer" @dragover.prevent="isDraggingGallery = true" @dragleave.prevent="isDraggingGallery = false" @drop.prevent="handleGalleryDrop">
                        <div :class="[isDraggingGallery ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-400']" class="border-2 border-dashed rounded-xl p-3 text-center transition-all"><Plus class="w-5 h-5 mx-auto text-gray-400" /><p class="text-[10px] text-gray-500 mt-0.5">Tambah Foto (max 20)</p></div>
                        <input type="file" accept=".jpg,.jpeg,.png,.webp" multiple @change="handleGalleryUpload" class="hidden">
                    </label>
                    <p v-else class="text-center py-3 text-amber-600 text-[10px] font-medium bg-amber-50 rounded-xl">Batas maksimal 20 foto tercapai</p>
                </div>

                <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Kontak</h2>
                    <div class="space-y-3">
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Jam Operasional</label><input v-model="form.operating_hours" type="text" placeholder="08:00 - 17:00" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"></div>
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Telepon</label><input v-model="form.contact_phone" type="tel" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"></div>
                        <div><label class="block text-[11px] font-medium text-gray-700 mb-1">Email</label><input v-model="form.contact_email" type="email" class="w-full px-3 py-2 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500"></div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Floating Buttons -->
        <div class="fixed bottom-6 right-6 flex gap-3 z-40">
            <Link href="/admin/destinations" class="px-4 py-3 bg-white text-gray-700 rounded-xl shadow-lg border border-gray-200 text-xs font-semibold hover:bg-gray-50 transition-all">Batal</Link>
            <button @click="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl shadow-lg shadow-emerald-500/30 text-xs font-bold hover:shadow-emerald-500/50 transition-all flex items-center gap-2 disabled:opacity-50"><Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" /><Save v-else class="w-4 h-4" />{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
        </div>

        <ImageEditorModal :show="showImageEditor" :image-url="editingImageUrl" @close="showImageEditor = false" @save="handleEditorSave" />
    </div>
</template>
