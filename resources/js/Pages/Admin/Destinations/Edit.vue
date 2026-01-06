<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import ImageEditorModal from '@/Components/Admin/ImageEditorModal.vue';
import { MapPin, Save, ArrowLeft, Upload, X, Plus, Trash2, Pencil, FileText, Image, Info, Map, Loader2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    destination: { type: Object, required: true },
    categories: { type: Array, default: () => [] }
});

const form = useForm({
    category_id: props.destination.category_id || '',
    name: props.destination.name || '',
    short_description: props.destination.short_description || '',
    description: props.destination.description || '',
    address: props.destination.address || '',
    city: props.destination.city || '',
    operating_hours: props.destination.operating_hours || '',
    contact_phone: props.destination.contact_phone || '',
    contact_email: props.destination.contact_email || '',
    latitude: props.destination.latitude || '',
    longitude: props.destination.longitude || '',
    google_maps_embed: props.destination.google_maps_embed || '',
    facilities: props.destination.facilities || [],
    rules: props.destination.rules || [],
    parking_fees: props.destination.parking_fees?.length ? props.destination.parking_fees.map(p => ({ vehicle_type: p.vehicle_type, price: p.price, description: p.description || '' })) : [],
    is_active: props.destination.is_active ?? true,
    is_featured: props.destination.is_featured ?? false,
    meta_title: props.destination.meta_title || '',
    meta_description: props.destination.meta_description || '',
    keywords: props.destination.keywords || '',
    cover_image: null,
    gallery_images: [],
    prices: props.destination.prices?.length ? props.destination.prices.map(p => ({ id: p.id, ticket_type: p.ticket_type || p.label, price: p.price, description: p.description || '' })) : [{ ticket_type: 'dewasa', price: '', description: '' }],
    _method: 'PUT'
});

// Reverse geocoding state
const isLoadingAddress = ref(false);

// Fetch address from coordinates using Nominatim (free, no API key needed)
const fetchAddressFromCoordinates = async () => {
    if (!form.latitude || !form.longitude) {
        alert('Silakan masukkan latitude dan longitude terlebih dahulu');
        return;
    }
    
    isLoadingAddress.value = true;
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${form.latitude}&lon=${form.longitude}&addressdetails=1`,
            { headers: { 'Accept-Language': 'id' } }
        );
        const data = await response.json();
        
        if (data.display_name) {
            form.address = data.display_name;
            
            // Auto-fill city from address details (City, Province format)
            if (data.address) {
                const addr = data.address;
                const state = addr.state || '';
                
                // Try multiple fields to find the city/kabupaten name
                let cityName = addr.city || addr.county || addr.city_district || addr.town || '';
                
                // If nothing found, parse from display_name
                // Format: "Street, Kecamatan, City, Province, Region, Postal, Country"
                if (!cityName && data.display_name && state) {
                    const parts = data.display_name.split(',').map(p => p.trim());
                    // Find the index of the province
                    const stateIndex = parts.findIndex(p => p === state);
                    // The city is usually right before the province
                    if (stateIndex > 0) {
                        const potentialCity = parts[stateIndex - 1];
                        // Make sure it's not a district (Kecamatan) or street
                        if (potentialCity && !potentialCity.toLowerCase().startsWith('kecamatan') && 
                            !potentialCity.toLowerCase().startsWith('jalan')) {
                            cityName = potentialCity;
                        }
                    }
                }
                
                // Clean up city name - remove "Kota ", "Kabupaten ", "Kecamatan " prefix
                let cleanCity = cityName.replace(/^(Kota |Kabupaten |Kecamatan )/i, '');
                
                if (cleanCity && state) {
                    form.city = `${cleanCity}, ${state}`;
                } else if (cleanCity) {
                    form.city = cleanCity;
                } else if (state) {
                    form.city = state;
                }
            }
        } else {
            alert('Alamat tidak ditemukan untuk koordinat ini');
        }
    } catch (error) {
        console.error('Error fetching address:', error);
        alert('Gagal mendapatkan alamat. Silakan coba lagi.');
    } finally {
        isLoadingAddress.value = false;
    }
};

// Computed property for map preview URL
const mapPreviewUrl = computed(() => {
    if (form.google_maps_embed) {
        // Extract src URL from iframe code
        const match = form.google_maps_embed.match(/src="([^"]+)"/);
        return match ? match[1] : null;
    }
    return null;
});


const newFacility = ref('');
const newRule = ref('');
const coverPreview = ref(null);
const galleryPreviews = ref([]);
const existingImages = ref(props.destination.images || []);
const isDraggingCover = ref(false);
const isDraggingGallery = ref(false);

// Image Editor state
const showImageEditor = ref(false);
const editingImageUrl = ref('');
const editingImageType = ref('');
const editingGalleryIndex = ref(-1);

const addFacility = () => { if (newFacility.value.trim()) { form.facilities.push(newFacility.value.trim()); newFacility.value = ''; }};
const removeFacility = (i) => form.facilities.splice(i, 1);
const addRule = () => { if (newRule.value.trim()) { form.rules.push(newRule.value.trim()); newRule.value = ''; }};
const removeRule = (i) => form.rules.splice(i, 1);
const addPrice = () => form.prices.push({ ticket_type: '', price: '', description: '' });
const removePrice = (i) => form.prices.splice(i, 1);

// Image handlers
const handleCoverUpload = (e) => {
    const file = e.target.files?.[0] || e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) { if (file.size > 2 * 1024 * 1024) { alert('Max 2MB'); return; } form.cover_image = file; coverPreview.value = URL.createObjectURL(file); }
};
const handleCoverDrop = (e) => { isDraggingCover.value = false; handleCoverUpload(e); };
const removeCover = () => { form.cover_image = null; coverPreview.value = null; };

const handleGalleryUpload = (e) => { Array.from(e.target.files || e.dataTransfer?.files).forEach(file => { if (!file.type.startsWith('image/') || file.size > 5 * 1024 * 1024) return; form.gallery_images.push(file); galleryPreviews.value.push(URL.createObjectURL(file)); }); };
const handleGalleryDrop = (e) => { isDraggingGallery.value = false; handleGalleryUpload(e); };
const removeGalleryImage = (i) => { form.gallery_images.splice(i, 1); galleryPreviews.value.splice(i, 1); };

// Image Editor
const openCoverEditor = () => { if (coverPreview.value) { editingImageUrl.value = coverPreview.value; editingImageType.value = 'cover'; showImageEditor.value = true; }};
const openGalleryEditor = (i) => { if (galleryPreviews.value[i]) { editingImageUrl.value = galleryPreviews.value[i]; editingImageType.value = 'gallery'; editingGalleryIndex.value = i; showImageEditor.value = true; }};
const handleEditorSave = async (dataUrl) => { const blob = await (await fetch(dataUrl)).blob(); const file = new File([blob], 'edited.jpg', { type: 'image/jpeg' }); if (editingImageType.value === 'cover') { form.cover_image = file; coverPreview.value = dataUrl; } else { form.gallery_images[editingGalleryIndex.value] = file; galleryPreviews.value[editingGalleryIndex.value] = dataUrl; } showImageEditor.value = false; };

// Existing images
const deleteExistingImage = async (id) => { if (!confirm('Hapus gambar?')) return; try { await fetch(`/admin/destinations/images/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); existingImages.value = existingImages.value.filter(img => img.id !== id); } catch (e) {} };
const setPrimaryImage = async (id) => { try { await fetch(`/admin/destinations/images/${id}/set-primary`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); existingImages.value = existingImages.value.map(img => ({ ...img, is_primary: img.id === id })); } catch (e) {} };

const submit = () => {
    // Force TinyMCE to sync content before submit
    if (window.tinymce) {
        window.tinymce.triggerSave();
        const editor = window.tinymce.get('dest-edit-desc');
        if (editor) {
            form.description = editor.getContent();
        }
    }
    form.post(`/admin/destinations/${props.destination.id}`, { forceFormData: true, onSuccess: () => router.visit('/admin/destinations') });
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <Link href="/admin/destinations" class="group p-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 transition-all"><ArrowLeft class="w-5 h-5 text-gray-600 group-hover:-translate-x-0.5 transition-transform" /></Link>
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-green-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30"><Pencil class="w-7 h-7 text-white" /></div>
                <div><h1 class="text-2xl font-black text-gray-900">Edit Destinasi</h1><p class="text-gray-500 text-sm">{{ destination.name }}</p></div>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 xl:grid-cols-3 gap-3">
            <div class="xl:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-amber-400/20 to-orange-500/20 rounded-xl flex items-center justify-center"><Info class="w-5 h-5 text-amber-600" /></div><h2 class="text-lg font-bold text-gray-900">Informasi Dasar</h2></div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div><label class="block text-sm font-semibold text-gray-700 mb-2">Nama Destinasi *</label><input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"></div>
                            <div><label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label><select v-model="form.category_id" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"><option value="">Pilih Kategori</option><option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option></select></div>
                        </div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Singkat <span class="text-gray-400 font-normal">(maks 255 karakter)</span></label><input v-model="form.short_description" type="text" maxlength="255" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"><p class="text-xs text-gray-400 mt-1">{{ form.short_description?.length || 0 }}/255 karakter</p></div>
                    </div>
                </div>

                <!-- Description with TinyMCE -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-blue-400/20 to-indigo-500/20 rounded-xl flex items-center justify-center"><FileText class="w-5 h-5 text-blue-600" /></div><div><h2 class="text-lg font-bold text-gray-900">Deskripsi Lengkap</h2><p class="text-xs text-gray-500">Gunakan AI untuk generate konten</p></div></div>
                    <TinyMceEditor v-model="form.description" name="description" id="dest-edit-desc" :height="400" placeholder="Deskripsi destinasi..." />
                </div>

                <!-- Location & Map -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-400/20 to-red-500/20 rounded-xl flex items-center justify-center"><MapPin class="w-5 h-5 text-rose-600" /></div>
                        <h2 class="text-lg font-bold text-gray-900">Lokasi & Peta</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Coordinates -->
                        <div>
                            <p class="text-sm text-gray-500 mb-3">Masukkan koordinat untuk menentukan lokasi destinasi</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="flex items-center gap-2 text-sm font-semibold text-rose-600 mb-2">
                                        <MapPin class="w-4 h-4" /> Latitude
                                    </label>
                                    <input v-model="form.latitude" type="number" step="any" placeholder="-0.8924097" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                                </div>
                                <div>
                                    <label class="flex items-center gap-2 text-sm font-semibold text-rose-600 mb-2">
                                        <MapPin class="w-4 h-4" /> Longitude
                                    </label>
                                    <input v-model="form.longitude" type="number" step="any" placeholder="119.8295628" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Get Address Button -->
                        <button 
                            type="button" 
                            @click="fetchAddressFromCoordinates" 
                            :disabled="isLoadingAddress"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold text-sm shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <Loader2 v-if="isLoadingAddress" class="w-4 h-4 animate-spin" />
                            <MapPin v-else class="w-4 h-4" />
                            {{ isLoadingAddress ? 'Memuat...' : 'Dapatkan Alamat dari Koordinat' }}
                        </button>
                        
                        <!-- Address -->
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-emerald-600 mb-2">
                                <MapPin class="w-4 h-4" /> Alamat Lengkap
                            </label>
                            <textarea v-model="form.address" rows="3" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 resize-none" placeholder="Alamat akan terisi otomatis atau masukkan manual"></textarea>
                        </div>
                        
                        <!-- City -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kota *</label>
                            <input v-model="form.city" type="text" required class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>
                </div>

                <!-- Embed Peta -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-teal-400/20 to-cyan-500/20 rounded-xl flex items-center justify-center"><Map class="w-5 h-5 text-teal-600" /></div>
                        <h2 class="text-lg font-bold text-gray-900">Embed Peta</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Embed Code Input -->
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-teal-600 mb-2">
                                <Map class="w-4 h-4" /> Kode Embed Google Maps
                            </label>
                            <textarea 
                                v-model="form.google_maps_embed" 
                                rows="4" 
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 resize-none font-mono text-xs" 
                                placeholder='<iframe src="https://www.google.com/maps/embed?pb=..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>'
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-2">Paste kode embed dari Google Maps (Share → Embed a map)</p>
                        </div>
                        
                        <!-- Map Preview -->
                        <div v-if="mapPreviewUrl">
                            <label class="flex items-center gap-2 text-sm font-semibold text-teal-600 mb-2">
                                <MapPin class="w-4 h-4" /> Preview Peta
                            </label>
                            <div class="rounded-xl overflow-hidden border border-gray-200 shadow-lg">
                                <iframe 
                                    :src="mapPreviewUrl" 
                                    width="100%" 
                                    height="300" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities & Rules -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Fasilitas & Aturan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Fasilitas</label><div class="flex gap-2 mb-3"><input v-model="newFacility" @keyup.enter.prevent="addFacility" type="text" placeholder="Tambah fasilitas" class="flex-1 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500"><button type="button" @click="addFacility" class="p-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600"><Plus class="w-4 h-4" /></button></div><div class="flex flex-wrap gap-2"><span v-for="(f, i) in form.facilities" :key="i" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">{{ f }}<button type="button" @click="removeFacility(i)" class="hover:text-emerald-900"><X class="w-3 h-3" /></button></span></div></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Aturan</label><div class="flex gap-2 mb-3"><input v-model="newRule" @keyup.enter.prevent="addRule" type="text" placeholder="Tambah aturan" class="flex-1 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-amber-500"><button type="button" @click="addRule" class="p-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600"><Plus class="w-4 h-4" /></button></div><div class="flex flex-wrap gap-2"><span v-for="(r, i) in form.rules" :key="i" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-100 text-amber-700 rounded-full text-sm font-medium">{{ r }}<button type="button" @click="removeRule(i)" class="hover:text-amber-900"><X class="w-3 h-3" /></button></span></div></div>
                    </div>
                </div>

                <!-- Prices -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center justify-between mb-6"><h2 class="text-lg font-bold text-gray-900">Harga Tiket</h2><button type="button" @click="addPrice" class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-sm font-semibold hover:bg-emerald-600 flex items-center gap-1.5"><Plus class="w-4 h-4" />Tambah</button></div>
                    <div class="space-y-3"><div v-for="(price, i) in form.prices" :key="i" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-4 bg-gray-50 rounded-xl"><input v-model="price.ticket_type" type="text" placeholder="Jenis tiket" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 focus:border-emerald-500"><input v-model="price.price" type="number" placeholder="Harga" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 focus:border-emerald-500"><input v-model="price.description" type="text" placeholder="Keterangan" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 focus:border-emerald-500"><button v-if="form.prices.length > 1" type="button" @click="removePrice(i)" class="p-2.5 text-red-500 hover:bg-red-50 rounded-xl self-center"><Trash2 class="w-4 h-4" /></button></div></div>
                </div>

                <!-- Parking Fees -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center justify-between mb-6"><h2 class="text-lg font-bold text-gray-900">Biaya Parkir</h2><button type="button" @click="form.parking_fees.push({ vehicle_type: '', price: '', description: '' })" class="px-4 py-2 bg-blue-500 text-white rounded-xl text-sm font-semibold hover:bg-blue-600 flex items-center gap-1.5"><Plus class="w-4 h-4" />Tambah</button></div>
                    <div class="space-y-3"><div v-for="(fee, i) in form.parking_fees" :key="i" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-4 bg-blue-50 rounded-xl"><input v-model="fee.vehicle_type" type="text" placeholder="Jenis kendaraan (Motor/Mobil/Bus)" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 focus:border-blue-500"><input v-model="fee.price" type="number" placeholder="Biaya parkir" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 focus:border-blue-500"><input v-model="fee.description" type="text" placeholder="Keterangan (opsional)" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 focus:border-blue-500"><button type="button" @click="form.parking_fees.splice(i, 1)" class="p-2.5 text-red-500 hover:bg-red-50 rounded-xl self-center"><Trash2 class="w-4 h-4" /></button></div></div>
                    <p v-if="!form.parking_fees.length" class="text-center py-6 text-gray-400 text-sm">Belum ada biaya parkir. Klik "Tambah" untuk menambahkan.</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Status -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-indigo-400/20 to-purple-500/20 rounded-xl flex items-center justify-center"><MapPin class="w-5 h-5 text-indigo-600" /></div><h2 class="text-lg font-bold text-gray-900">Pengaturan</h2></div>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div><span class="font-medium text-gray-700">Status Aktif</span></div><input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></label>
                        <label class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></div><span class="font-medium text-gray-700">Unggulan</span></div><input v-model="form.is_featured" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-amber-500 focus:ring-amber-500"></label>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full mt-6 py-3.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50"><Save class="w-5 h-5" />{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</button>
                </div>

                <!-- Existing Images -->
                <div v-if="existingImages.length" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Gambar Tersimpan</h2>
                    <div class="grid grid-cols-2 gap-2"><div v-for="img in existingImages" :key="img.id" class="relative aspect-square group"><img :src="img.image_url" class="w-full h-full object-cover rounded-lg"><div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2"><button type="button" @click="setPrimaryImage(img.id)" :class="['p-1.5 rounded-lg text-white text-xs', img.is_primary ? 'bg-amber-500' : 'bg-white/20 hover:bg-amber-500']">★</button><button type="button" @click="deleteExistingImage(img.id)" class="p-1.5 rounded-lg bg-red-500 text-white hover:bg-red-600"><X class="w-3 h-3" /></button></div><span v-if="img.is_primary" class="absolute top-1 left-1 px-1.5 py-0.5 bg-amber-500 text-white text-[10px] rounded font-bold">Utama</span></div></div>
                </div>

                <!-- Cover Image -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-teal-400/20 to-green-500/20 rounded-xl flex items-center justify-center"><Image class="w-5 h-5 text-teal-600" /></div><h2 class="text-lg font-bold text-gray-900">Gambar Baru</h2></div>
                    <label class="block cursor-pointer" @dragover.prevent="isDraggingCover = true" @dragleave.prevent="isDraggingCover = false" @drop.prevent="handleCoverDrop">
                        <div v-if="!coverPreview" :class="[isDraggingCover ? 'border-emerald-500 bg-emerald-50 scale-[1.02]' : 'border-gray-300 hover:border-emerald-400']" class="border-2 border-dashed rounded-xl p-6 text-center transition-all"><Upload class="w-10 h-10 mx-auto text-gray-400 mb-2" /><p class="text-sm text-gray-600">Drag & drop gambar</p><p class="text-xs text-gray-400 mt-1">Max 2MB • JPG, PNG</p></div>
                        <input type="file" accept="image/*" @change="handleCoverUpload" class="hidden">
                    </label>
                    <div v-if="coverPreview" class="mt-4"><div class="relative rounded-xl overflow-hidden shadow-lg border border-gray-100 group"><img :src="coverPreview" class="w-full h-40 object-cover"><div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button type="button" @click.prevent="openCoverEditor" class="bg-blue-500 text-white p-2 rounded-xl hover:bg-blue-600"><Pencil class="w-4 h-4" /></button><button type="button" @click.prevent="removeCover" class="bg-red-500 text-white p-2 rounded-xl hover:bg-red-600"><X class="w-4 h-4" /></button></div></div></div>
                </div>

                <!-- Gallery Upload -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <div class="flex items-center gap-3 mb-6"><div class="w-10 h-10 bg-gradient-to-br from-pink-400/20 to-rose-500/20 rounded-xl flex items-center justify-center"><svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div><h2 class="text-lg font-bold text-gray-900">Tambah Galeri</h2></div>
                    <div v-if="galleryPreviews.length" class="grid grid-cols-3 gap-2 mb-4"><div v-for="(img, i) in galleryPreviews" :key="i" class="relative aspect-square group"><img :src="img" class="w-full h-full object-cover rounded-lg"><div class="absolute inset-0 bg-black/40 rounded-lg flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"><button type="button" @click="openGalleryEditor(i)" class="p-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600"><Pencil class="w-3 h-3" /></button><button type="button" @click="removeGalleryImage(i)" class="p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600"><X class="w-3 h-3" /></button></div></div></div>
                    <label class="block cursor-pointer" @dragover.prevent="isDraggingGallery = true" @dragleave.prevent="isDraggingGallery = false" @drop.prevent="handleGalleryDrop"><div :class="[isDraggingGallery ? 'border-pink-500 bg-pink-50' : 'border-gray-200 hover:border-pink-400']" class="border-2 border-dashed rounded-xl p-4 text-center transition-all"><Plus class="w-6 h-6 mx-auto text-gray-400" /><p class="text-xs text-gray-500 mt-1">Tambah Gambar</p></div><input type="file" accept="image/*" multiple @change="handleGalleryUpload" class="hidden"></label>
                </div>

                <!-- Contact -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/50 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Kontak</h2>
                    <div class="space-y-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Jam Operasional</label><input v-model="form.operating_hours" type="text" placeholder="08:00 - 17:00" class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Telepon</label><input v-model="form.contact_phone" type="tel" class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-2">Email</label><input v-model="form.contact_email" type="email" class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-500"></div>
                    </div>
                </div>
            </div>
        </form>
        
        <ImageEditorModal :show="showImageEditor" :image-url="editingImageUrl" @close="showImageEditor = false" @save="handleEditorSave" />
    </div>
</template>
