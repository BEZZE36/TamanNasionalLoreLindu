<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Globe, Phone, Mail, MapPin, Clock, Save, CheckCircle, Loader2,
    Facebook, Instagram, Youtube, MapPinned
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    data: Object
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Format phone for display: XXX XXXX XXXX
const formatPhoneDisplay = (phone) => {
    if (!phone) return '';
    let digits = phone.replace(/[^\d]/g, '');
    if (digits.startsWith('0')) {
        digits = digits.substring(1);
    } else if (digits.startsWith('62')) {
        digits = digits.substring(2);
    }
    if (digits.length >= 10) {
        return `${digits.substring(0, 3)} ${digits.substring(3, 7)} ${digits.substring(7)}`;
    } else if (digits.length >= 7) {
        return `${digits.substring(0, 3)} ${digits.substring(3, 7)} ${digits.substring(7)}`;
    } else if (digits.length >= 3) {
        return `${digits.substring(0, 3)} ${digits.substring(3)}`;
    }
    return digits;
};

// Initialize form with formatted phone numbers
const form = useForm({
    contact_phone: formatPhoneDisplay(props.data?.contact_phone || ''),
    contact_whatsapp: formatPhoneDisplay(props.data?.contact_whatsapp || ''),
    contact_email: props.data?.contact_email || '',
    contact_latitude: props.data?.contact_latitude || '',
    contact_longitude: props.data?.contact_longitude || '',
    contact_address: props.data?.contact_address || '',
    google_maps_embed: props.data?.google_maps_embed || '',
    facebook_url: props.data?.facebook_url || '',
    instagram_url: props.data?.instagram_url || '',
    youtube_url: props.data?.youtube_url || '',
    tiktok_url: props.data?.tiktok_url || '',
    // Operational hours
    hours_senin_open: props.data?.operational_hours?.senin?.is_open ?? true,
    hours_senin_start: props.data?.operational_hours?.senin?.open_time || '08:00',
    hours_senin_end: props.data?.operational_hours?.senin?.close_time || '16:00',
    hours_selasa_open: props.data?.operational_hours?.selasa?.is_open ?? true,
    hours_selasa_start: props.data?.operational_hours?.selasa?.open_time || '08:00',
    hours_selasa_end: props.data?.operational_hours?.selasa?.close_time || '16:00',
    hours_rabu_open: props.data?.operational_hours?.rabu?.is_open ?? true,
    hours_rabu_start: props.data?.operational_hours?.rabu?.open_time || '08:00',
    hours_rabu_end: props.data?.operational_hours?.rabu?.close_time || '16:00',
    hours_kamis_open: props.data?.operational_hours?.kamis?.is_open ?? true,
    hours_kamis_start: props.data?.operational_hours?.kamis?.open_time || '08:00',
    hours_kamis_end: props.data?.operational_hours?.kamis?.close_time || '16:00',
    hours_jumat_open: props.data?.operational_hours?.jumat?.is_open ?? true,
    hours_jumat_start: props.data?.operational_hours?.jumat?.open_time || '08:00',
    hours_jumat_end: props.data?.operational_hours?.jumat?.close_time || '16:00',
    hours_sabtu_open: props.data?.operational_hours?.sabtu?.is_open ?? true,
    hours_sabtu_start: props.data?.operational_hours?.sabtu?.open_time || '08:00',
    hours_sabtu_end: props.data?.operational_hours?.sabtu?.close_time || '12:00',
    hours_minggu_open: props.data?.operational_hours?.minggu?.is_open ?? false,
    hours_minggu_start: props.data?.operational_hours?.minggu?.open_time || '08:00',
    hours_minggu_end: props.data?.operational_hours?.minggu?.close_time || '12:00',
});

// Auto-format phone numbers when typing
watch(() => form.contact_phone, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        const formatted = formatPhoneDisplay(newVal);
        if (formatted !== newVal) {
            form.contact_phone = formatted;
        }
    }
});

watch(() => form.contact_whatsapp, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        const formatted = formatPhoneDisplay(newVal);
        if (formatted !== newVal) {
            form.contact_whatsapp = formatted;
        }
    }
});

const days = [
    { key: 'senin', name: 'Senin' },
    { key: 'selasa', name: 'Selasa' },
    { key: 'rabu', name: 'Rabu' },
    { key: 'kamis', name: 'Kamis' },
    { key: 'jumat', name: 'Jumat' },
    { key: 'sabtu', name: 'Sabtu' },
    { key: 'minggu', name: 'Minggu' },
];

const loadingAddress = ref(false);
let ctx;

// Animation on mount
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.section-card', 
            { opacity: 0, y: 20, scale: 0.98 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.day-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const getAddress = async () => {
    if (!form.contact_latitude || !form.contact_longitude) {
        alert('Silakan masukkan koordinat latitude dan longitude terlebih dahulu.');
        return;
    }
    loadingAddress.value = true;
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${form.contact_latitude}&lon=${form.contact_longitude}&zoom=18&addressdetails=1`);
        const data = await response.json();
        if (data.display_name) {
            form.contact_address = data.display_name;
        } else {
            alert('Tidak dapat menemukan alamat untuk koordinat tersebut.');
        }
    } catch (error) {
        alert('Terjadi kesalahan saat mengambil alamat.');
    } finally {
        loadingAddress.value = false;
    }
};

const submit = () => {
    form.put('/admin/site-info/detail-web', {
        preserveScroll: true,
    });
};

const showMapPreview = computed(() => {
    return form.google_maps_embed && form.google_maps_embed.includes('iframe');
});
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-purple-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-purple-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex items-center gap-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <Globe class="h-6 w-6 text-white" />
                    </div>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Detail Website</h1>
                    <p class="text-purple-100/80 text-xs">Kelola informasi kontak dan media sosial website</p>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Contact Information Section -->
            <div class="section-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30">
                            <Phone class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Informasi Kontak</h2>
                            <p class="text-[10px] text-gray-500">Nomor telepon dan email yang tampil di website</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Phone & WhatsApp -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-green-100 text-green-600">
                                    <Phone class="w-3 h-3" />
                                </span>
                                Telepon Kantor
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-medium">+62</span>
                                <input type="tel" v-model="form.contact_phone" placeholder="877 5169 0646"
                                    class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 pl-10 pr-4 py-2.5 text-xs focus:border-green-500 focus:ring-4 focus:ring-green-500/10 focus:bg-white transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </span>
                                WhatsApp
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-medium">+62</span>
                                <input type="tel" v-model="form.contact_whatsapp" placeholder="812 345 6789"
                                    class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 pl-10 pr-4 py-2.5 text-xs focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <Mail class="w-3 h-3" />
                            </span>
                            Email
                        </label>
                        <input type="email" v-model="form.contact_email" placeholder="info@lorelindu.info"
                            class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-xs focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all">
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class="section-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-rose-50 via-red-50 to-rose-50 px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-red-600 shadow-lg shadow-rose-500/30">
                            <MapPin class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Lokasi & Alamat</h2>
                            <p class="text-[10px] text-gray-500">Koordinat dan alamat kantor</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Coordinates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                                <MapPinned class="w-3.5 h-3.5 text-rose-500" />
                                Latitude
                            </label>
                            <input type="text" v-model="form.contact_latitude" placeholder="-0.8924096"
                                class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 font-mono text-xs focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                                <MapPinned class="w-3.5 h-3.5 text-rose-500" />
                                Longitude
                            </label>
                            <input type="text" v-model="form.contact_longitude" placeholder="119.8295628"
                                class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 font-mono text-xs focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 focus:bg-white transition-all">
                        </div>
                    </div>

                    <!-- Get Address Button -->
                    <button type="button" @click="getAddress" :disabled="loadingAddress"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-60">
                        <Loader2 v-if="loadingAddress" class="w-4 h-4 animate-spin" />
                        <MapPin v-else class="w-4 h-4" />
                        {{ loadingAddress ? 'Mencari...' : 'Dapatkan Alamat dari Koordinat' }}
                    </button>

                    <!-- Address -->
                    <div>
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                            <MapPin class="w-3.5 h-3.5 text-amber-500" />
                            Alamat Lengkap
                        </label>
                        <textarea v-model="form.contact_address" rows="2" placeholder="Jl. Prof. Dr. Yamin No. 17, Palu, Sulawesi Tengah"
                            class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-xs focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:bg-white transition-all resize-none"></textarea>
                    </div>

                    <!-- Google Maps Embed -->
                    <div>
                        <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 mb-2">
                            <MapPin class="w-3.5 h-3.5 text-red-500" />
                            Kode Embed Google Maps
                        </label>
                        <textarea v-model="form.google_maps_embed" rows="3" placeholder='<iframe src="https://www.google.com/maps/embed?..." ...></iframe>'
                            class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 font-mono text-[10px] focus:border-red-500 focus:ring-4 focus:ring-red-500/10 focus:bg-white transition-all resize-none"></textarea>
                    </div>

                    <!-- Map Preview -->
                    <Transition name="fade">
                        <div v-if="showMapPreview" class="rounded-xl overflow-hidden border-2 border-gray-200 shadow-lg">
                            <div class="bg-gradient-to-r from-gray-100 to-gray-50 px-4 py-2 border-b border-gray-200">
                                <p class="text-[10px] font-semibold text-gray-700 flex items-center gap-2">
                                    <MapPin class="w-3 h-3 text-red-500" />
                                    Preview Peta
                                </p>
                            </div>
                            <div v-html="form.google_maps_embed" class="map-preview"></div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Operational Hours Section -->
            <div class="section-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-50 via-purple-50 to-violet-50 px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30">
                            <Clock class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Jam Operasional</h2>
                            <p class="text-[10px] text-gray-500">Atur jadwal buka kantor per hari</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-2">
                    <div v-for="day in days" :key="day.key"
                        class="day-row flex items-center gap-4 p-3 rounded-xl bg-gray-50/80 border border-gray-100 hover:border-violet-200 hover:bg-violet-50/30 transition-all">
                        <div class="w-20 font-semibold text-xs text-gray-700">{{ day.name }}</div>
                        
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form[`hours_${day.key}_open`]" class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-300 peer-focus:ring-4 peer-focus:ring-violet-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-violet-500 peer-checked:to-purple-600"></div>
                        </label>
                        
                        <span :class="form[`hours_${day.key}_open`] ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                            class="px-2 py-0.5 rounded-lg text-[10px] font-bold transition-all">
                            {{ form[`hours_${day.key}_open`] ? 'Buka' : 'Tutup' }}
                        </span>
                        
                        <div v-if="form[`hours_${day.key}_open`]" class="flex items-center gap-2 flex-1 justify-end">
                            <input type="time" v-model="form[`hours_${day.key}_start`]"
                                class="rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-[10px] focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all">
                            <span class="text-gray-400 text-xs">-</span>
                            <input type="time" v-model="form[`hours_${day.key}_end`]"
                                class="rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-[10px] focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all">
                        </div>
                        <div v-else class="flex-1 text-right">
                            <span class="text-gray-400 text-[10px] italic">Tidak beroperasi</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media Section -->
            <div class="section-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-pink-50 via-rose-50 to-pink-50 px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-500/30">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Media Sosial</h2>
                            <p class="text-[10px] text-gray-500">Link ke akun media sosial resmi</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-3">
                    <!-- Facebook -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/50 border border-transparent hover:border-[#1877F2]/20 hover:bg-blue-50/30 transition-all group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#1877F2] shadow-lg shadow-[#1877F2]/30 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-bold text-gray-800 block mb-1">Facebook</label>
                            <input type="url" v-model="form.facebook_url" placeholder="https://facebook.com/yourpage"
                                class="w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 text-xs focus:border-[#1877F2] focus:ring-4 focus:ring-[#1877F2]/10 transition-all">
                        </div>
                    </div>

                    <!-- Instagram -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/50 border border-transparent hover:border-[#dc2743]/20 hover:bg-pink-50/30 transition-all group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#f09433] via-[#dc2743] to-[#bc1888] shadow-lg shadow-[#dc2743]/30 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-bold text-gray-800 block mb-1">Instagram</label>
                            <input type="url" v-model="form.instagram_url" placeholder="https://instagram.com/yourprofile"
                                class="w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 text-xs focus:border-[#dc2743] focus:ring-4 focus:ring-[#dc2743]/10 transition-all">
                        </div>
                    </div>

                    <!-- YouTube -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/50 border border-transparent hover:border-[#FF0000]/20 hover:bg-red-50/30 transition-all group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF0000] shadow-lg shadow-[#FF0000]/30 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-bold text-gray-800 block mb-1">YouTube</label>
                            <input type="url" v-model="form.youtube_url" placeholder="https://youtube.com/@yourchannel"
                                class="w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 text-xs focus:border-[#FF0000] focus:ring-4 focus:ring-[#FF0000]/10 transition-all">
                        </div>
                    </div>

                    <!-- TikTok -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/50 border border-transparent hover:border-gray-400/20 hover:bg-gray-100/50 transition-all group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-black shadow-lg shadow-black/30 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-bold text-gray-800 block mb-1">TikTok</label>
                            <input type="url" v-model="form.tiktok_url" placeholder="https://tiktok.com/@yourprofile"
                                class="w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 text-xs focus:border-gray-800 focus:ring-4 focus:ring-gray-800/10 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="sticky bottom-4 flex justify-end z-20">
                <button type="submit" :disabled="form.processing"
                    class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 px-6 py-3 text-white text-xs font-bold shadow-2xl shadow-purple-500/40 hover:shadow-purple-500/60 hover:-translate-y-0.5 hover:scale-105 transition-all duration-300 disabled:opacity-50">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    transform: translateY(-16px);
}
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
:deep(.map-preview iframe) {
    width: 100% !important;
    height: 200px !important;
    border: 0 !important;
}
</style>
