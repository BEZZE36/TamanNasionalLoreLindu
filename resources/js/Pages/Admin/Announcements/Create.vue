<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Bell, ArrowLeft, Save, Type, MessageSquare, Palette, Link as LinkIcon, Eye, EyeOff,
    Image, Clock, Calendar, Users, Globe, UserCheck, UserX, Sparkles, Zap,
    ChevronDown, Plus, X, AlertTriangle, Target, Settings, Megaphone,
    Layout, MousePointer, Timer, Loader2, Upload, Trash2
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    pageOptions: { type: Object, default: () => ({}) },
    dayOptions: { type: Object, default: () => ({}) }
});

// Form data with all model fields
const form = useForm({
    title: '',
    message: '',
    type: 'banner',
    notification_type: 'info',
    bg_color: '#f59e0b',
    text_color: '#ffffff',
    link_url: '',
    link_text: '',
    is_dismissible: true,
    is_active: false,
    priority: 1,
    start_at: '',
    end_at: '',
    // Advanced features
    target_audience: 'all',
    target_pages: [],
    exclude_pages: [],
    show_days: [],
    show_time_start: '',
    show_time_end: '',
    // Countdown
    show_countdown: false,
    countdown_label: '',
    // Extra buttons
    extra_buttons: []
});

// UI State
const activeSection = ref('content');
const showPageSelector = ref(false);
const showExcludeSelector = ref(false);

// Touched fields for validation
const touched = ref({
    title: false,
    message: false
});

// Real-time validation errors
const validationErrors = computed(() => {
    const errors = {};
    if (touched.value.title && !form.title.trim()) {
        errors.title = 'Judul wajib diisi';
    }
    if (touched.value.message && !form.message.trim()) {
        errors.message = 'Pesan wajib diisi';
    }
    return errors;
});

// Check if form is valid
const isFormValid = computed(() => {
    return form.title.trim() && form.message.trim();
});

let ctx;

onMounted(() => {
    nextTick(() => {
        ctx = gsap.context(() => {
            gsap.fromTo('.form-section', 
                { opacity: 0, y: 20 }, 
                { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power2.out' }
            );
        });
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Page options (default if not passed from controller)
const defaultPageOptions = {
    'home': 'Beranda',
    'destinations': 'Destinasi',
    'destinations.detail': 'Detail Destinasi',
    'about': 'Tentang',
    'flora': 'Flora',
    'flora.detail': 'Detail Flora',
    'fauna': 'Fauna',
    'fauna.detail': 'Detail Fauna',
    'gallery': 'Galeri',
    'news': 'Berita/Blog',
    'contact': 'Kontak',
    'booking': 'Booking',
    'all': '📍 Semua Halaman'
};

const defaultDayOptions = {
    0: 'Minggu',
    1: 'Senin',
    2: 'Selasa',
    3: 'Rabu',
    4: 'Kamis',
    5: 'Jumat',
    6: 'Sabtu'
};

const pageOptions = computed(() => Object.keys(props.pageOptions).length ? props.pageOptions : defaultPageOptions);
const dayOptions = computed(() => Object.keys(props.dayOptions).length ? props.dayOptions : defaultDayOptions);



// Toggle page selection
const togglePage = (page) => {
    const index = form.target_pages.indexOf(page);
    if (index > -1) {
        form.target_pages.splice(index, 1);
    } else {
        form.target_pages.push(page);
    }
};

const toggleExcludePage = (page) => {
    const index = form.exclude_pages.indexOf(page);
    if (index > -1) {
        form.exclude_pages.splice(index, 1);
    } else {
        form.exclude_pages.push(page);
    }
};

const toggleDay = (day) => {
    const dayNum = parseInt(day);
    const index = form.show_days.indexOf(dayNum);
    if (index > -1) {
        form.show_days.splice(index, 1);
    } else {
        form.show_days.push(dayNum);
    }
};

// Extra button handling
const addExtraButton = () => {
    form.extra_buttons.push({ text: '', url: '', style: 'primary' });
};

const removeExtraButton = (index) => {
    form.extra_buttons.splice(index, 1);
};

// Form submission
const submit = () => {
    // Mark all as touched for validation
    touched.value.title = true;
    touched.value.message = true;
    
    // Check validation
    if (!isFormValid.value) {
        return;
    }
    
    form.post('/admin/announcements', {
        forceFormData: true,
        onSuccess: () => router.visit('/admin/announcements')
    });
};

// Type options
const typeOptions = [
    { value: 'banner', label: 'Banner', icon: Layout, desc: 'Header notification bar' },
    { value: 'fullscreen', label: 'Fullscreen', icon: Megaphone, desc: 'Modal popup fullscreen' }
];

// Notification type options (3 themes only)
const notificationTypes = [
    { value: 'info', label: 'Info', color: 'from-indigo-600 to-purple-600', desc: 'Informasi umum' },
    { value: 'success', label: 'Success', color: 'from-emerald-500 to-green-600', desc: 'Berhasil/Sukses' },
    { value: 'warning', label: 'Warning', color: 'from-amber-500 to-orange-500', desc: 'Peringatan' },
    { value: 'danger', label: 'Danger', color: 'from-red-600 to-rose-600', desc: 'Penting/Urgent' }
];



// Audience options
const audienceOptions = [
    { value: 'all', label: 'Semua Pengunjung', icon: Globe, desc: 'Tampilkan ke semua' },
    { value: 'guest', label: 'Guest Only', icon: UserX, desc: 'Hanya pengunjung belum login' },
    { value: 'authenticated', label: 'User Login', icon: UserCheck, desc: 'Hanya user yang sudah login' },
    { value: 'first_visit', label: 'Pengunjung Baru', icon: Sparkles, desc: 'Hanya kunjungan pertama' }
];

// Preview background style
const previewStyle = computed(() => ({
    backgroundColor: form.bg_color,
    color: form.text_color
}));

// Character count
const messageCharCount = computed(() => form.message?.length || 0);
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 via-orange-600 to-yellow-500 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link href="/admin/announcements" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-all">
                        <ArrowLeft class="h-5 w-5 text-white" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <Bell class="h-6 w-6 text-white" />
                            <h1 class="text-xl font-bold text-white tracking-tight">Buat Pengumuman Baru</h1>
                        </div>
                        <p class="text-amber-100/80 text-xs">Buat banner, popup, atau notifikasi untuk pengunjung</p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Main Content - Left Column -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Content Section -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                                <Type class="w-4 h-4 text-white" />
                            </div>
                            Konten Pengumuman
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Judul <span class="text-red-500">*</span></label>
                                <input 
                                    v-model="form.title" 
                                    type="text" 
                                    required 
                                    placeholder="Masukkan judul pengumuman..."
                                    @blur="touched.title = true"
                                    :class="[
                                        'w-full px-4 py-2.5 text-sm rounded-xl border-2 focus:ring-4 transition-all',
                                        validationErrors.title ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-gray-200 focus:border-amber-500 focus:ring-amber-500/10'
                                    ]"
                                />
                                <p v-if="validationErrors.title" class="mt-1 text-xs text-red-500 flex items-center gap-1">
                                    <AlertTriangle class="w-3 h-3" /> {{ validationErrors.title }}
                                </p>
                                <p v-else-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pesan <span class="text-red-500">*</span></label>
                                <textarea 
                                    v-model="form.message" 
                                    rows="4" 
                                    required 
                                    placeholder="Tulis pesan pengumuman..."
                                    @blur="touched.message = true"
                                    :class="[
                                        'w-full px-4 py-2.5 text-sm rounded-xl border-2 focus:ring-4 transition-all resize-none',
                                        validationErrors.message ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-gray-200 focus:border-amber-500 focus:ring-amber-500/10'
                                    ]"
                                ></textarea>
                                <div class="flex justify-between mt-1">
                                    <p v-if="validationErrors.message" class="text-xs text-red-500 flex items-center gap-1">
                                        <AlertTriangle class="w-3 h-3" /> {{ validationErrors.message }}
                                    </p>
                                    <p v-else-if="form.errors.message" class="text-xs text-red-500">{{ form.errors.message }}</p>
                                    <p class="text-[10px] text-gray-400 ml-auto">{{ messageCharCount }} karakter</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Settings -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                <Palette class="w-4 h-4 text-white" />
                            </div>
                            Tampilan & Gaya
                        </h3>
                        
                        <!-- Type Selection -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Tipe Pengumuman</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    v-for="opt in typeOptions"
                                    :key="opt.value"
                                    type="button"
                                    @click="form.type = opt.value"
                                    :class="[
                                        'p-4 rounded-xl border-2 text-left transition-all',
                                        form.type === opt.value 
                                            ? 'border-amber-500 bg-amber-50' 
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <component :is="opt.icon" :class="['w-5 h-5 mb-2', form.type === opt.value ? 'text-amber-600' : 'text-gray-400']" />
                                    <p :class="['text-xs font-bold', form.type === opt.value ? 'text-amber-700' : 'text-gray-700']">{{ opt.label }}</p>
                                    <p class="text-[10px] text-gray-500">{{ opt.desc }}</p>
                                </button>
                            </div>
                        </div>

                        <!-- Notification Type -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Jenis Notifikasi</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    v-for="nt in notificationTypes"
                                    :key="nt.value"
                                    type="button"
                                    @click="form.notification_type = nt.value"
                                    :class="[
                                        'px-3 py-2 rounded-xl text-xs font-bold transition-all',
                                        form.notification_type === nt.value 
                                            ? `bg-gradient-to-r ${nt.color} text-white shadow-lg` 
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ nt.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Colors -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Warna Background</label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        v-model="form.bg_color" 
                                        type="color" 
                                        class="w-12 h-10 rounded-lg border-2 border-gray-200 cursor-pointer"
                                    />
                                    <input 
                                        v-model="form.bg_color" 
                                        type="text" 
                                        class="flex-1 px-3 py-2 text-xs rounded-lg border-2 border-gray-200 focus:border-amber-500 font-mono"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Warna Teks</label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        v-model="form.text_color" 
                                        type="color" 
                                        class="w-12 h-10 rounded-lg border-2 border-gray-200 cursor-pointer"
                                    />
                                    <input 
                                        v-model="form.text_color" 
                                        type="text" 
                                        class="flex-1 px-3 py-2 text-xs rounded-lg border-2 border-gray-200 focus:border-amber-500 font-mono"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Target Audience -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                <Users class="w-4 h-4 text-white" />
                            </div>
                            Target Audience
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <button
                                v-for="aud in audienceOptions"
                                :key="aud.value"
                                type="button"
                                @click="form.target_audience = aud.value"
                                :class="[
                                    'p-4 rounded-xl border-2 text-left transition-all',
                                    form.target_audience === aud.value 
                                        ? 'border-blue-500 bg-blue-50' 
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <component :is="aud.icon" :class="['w-5 h-5 mb-2', form.target_audience === aud.value ? 'text-blue-600' : 'text-gray-400']" />
                                <p :class="['text-xs font-bold', form.target_audience === aud.value ? 'text-blue-700' : 'text-gray-700']">{{ aud.label }}</p>
                                <p class="text-[10px] text-gray-500">{{ aud.desc }}</p>
                            </button>
                        </div>

                        <!-- Target Pages -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Tampilkan di Halaman (opsional)</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="(label, key) in pageOptions"
                                    :key="key"
                                    type="button"
                                    @click="togglePage(key)"
                                    :class="[
                                        'px-3 py-1.5 rounded-full text-[10px] font-bold transition-all',
                                        form.target_pages.includes(key)
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ label }}
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">Kosongkan untuk tampil di semua halaman</p>
                        </div>

                        <!-- Exclude Pages -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Kecualikan Halaman (opsional)</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="(label, key) in pageOptions"
                                    :key="key"
                                    type="button"
                                    @click="toggleExcludePage(key)"
                                    :class="[
                                        'px-3 py-1.5 rounded-full text-[10px] font-bold transition-all',
                                        form.exclude_pages.includes(key)
                                            ? 'bg-red-500 text-white'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule & Time -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                                <Calendar class="w-4 h-4 text-white" />
                            </div>
                            Jadwal & Waktu
                        </h3>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal Mulai</label>
                                <input 
                                    v-model="form.start_at" 
                                    type="datetime-local" 
                                    class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal Selesai</label>
                                <input 
                                    v-model="form.end_at" 
                                    type="datetime-local" 
                                    class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Show Days -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Tampilkan di Hari (opsional)</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="(label, day) in dayOptions"
                                    :key="day"
                                    type="button"
                                    @click="toggleDay(day)"
                                    :class="[
                                        'px-3 py-1.5 rounded-full text-[10px] font-bold transition-all',
                                        form.show_days.includes(parseInt(day))
                                            ? 'bg-emerald-500 text-white'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ label }}
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">Kosongkan untuk tampil setiap hari</p>
                        </div>

                        <!-- Time Range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Jam Mulai Tampil</label>
                                <input 
                                    v-model="form.show_time_start" 
                                    type="time" 
                                    class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Jam Selesai Tampil</label>
                                <input 
                                    v-model="form.show_time_end" 
                                    type="time" 
                                    class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Link & Buttons -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center">
                                <LinkIcon class="w-4 h-4 text-white" />
                            </div>
                            Link & Tombol
                        </h3>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">URL Link</label>
                                <input 
                                    v-model="form.link_url" 
                                    type="url" 
                                    placeholder="https://..."
                                    class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teks Tombol</label>
                                <input 
                                    v-model="form.link_text" 
                                    type="text" 
                                    placeholder="Selengkapnya"
                                    class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Extra Buttons -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-gray-700">Tombol Tambahan</label>
                                <button
                                    type="button"
                                    @click="addExtraButton"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold hover:bg-gray-200 transition-all"
                                >
                                    <Plus class="w-3 h-3" />
                                    Tambah
                                </button>
                            </div>
                            <div class="space-y-2">
                                <div 
                                    v-for="(btn, index) in form.extra_buttons" 
                                    :key="index"
                                    class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl"
                                >
                                    <input 
                                        v-model="btn.text" 
                                        type="text" 
                                        placeholder="Teks"
                                        class="flex-1 px-3 py-2 text-xs rounded-lg border border-gray-200 focus:border-amber-500"
                                    />
                                    <input 
                                        v-model="btn.url" 
                                        type="url" 
                                        placeholder="URL"
                                        class="flex-1 px-3 py-2 text-xs rounded-lg border border-gray-200 focus:border-amber-500"
                                    />
                                    <button
                                        type="button"
                                        @click="removeExtraButton(index)"
                                        class="p-2 rounded-lg text-red-500 hover:bg-red-50 transition-all"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Right Column -->
                <div class="space-y-5">
                    <!-- Live Preview -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5 sticky top-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                                <Eye class="w-4 h-4 text-white" />
                            </div>
                            Preview Banner
                        </h3>
                        
                        <div 
                            :style="previewStyle"
                            class="rounded-xl p-4 shadow-lg overflow-hidden"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm line-clamp-1">{{ form.title || 'Judul Pengumuman' }}</p>
                                    <p class="text-xs opacity-90 line-clamp-2 mt-1">{{ form.message || 'Pesan pengumuman akan tampil di sini...' }}</p>
                                    <button 
                                        v-if="form.link_text"
                                        class="mt-2 px-3 py-1 bg-white/20 rounded-lg text-xs font-bold hover:bg-white/30 transition-all"
                                    >
                                        {{ form.link_text }}
                                    </button>
                                </div>
                                <button v-if="form.is_dismissible" class="p-1 rounded-full hover:bg-white/20 transition-all flex-shrink-0">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-gray-100 rounded-lg text-[10px] font-bold text-gray-600">{{ typeOptions.find(t => t.value === form.type)?.label }}</span>
                        </div>
                    </div>



                    <!-- Countdown Timer -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center">
                                <Timer class="w-4 h-4 text-white" />
                            </div>
                            Countdown Timer
                        </h3>
                        
                        <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer mb-3">
                            <input v-model="form.show_countdown" type="checkbox" class="w-5 h-5 rounded text-amber-500 focus:ring-amber-500" />
                            <span class="text-xs font-medium text-gray-700">Tampilkan countdown</span>
                        </label>
                        
                        <div v-if="form.show_countdown">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Label Countdown</label>
                            <input 
                                v-model="form.countdown_label" 
                                type="text" 
                                placeholder="Berakhir dalam..."
                                class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all"
                            />
                        </div>
                    </div>

                    <!-- Priority -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-yellow-600 flex items-center justify-center">
                                <Zap class="w-4 h-4 text-white" />
                            </div>
                            Prioritas
                        </h3>
                        
                        <div class="flex items-center gap-3">
                            <input 
                                v-model="form.priority" 
                                type="range" 
                                min="1" 
                                max="10" 
                                class="flex-1 accent-amber-500"
                            />
                            <span class="w-10 h-10 flex items-center justify-center bg-amber-100 rounded-xl text-amber-700 font-black">{{ form.priority }}</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">Prioritas lebih tinggi akan ditampilkan lebih dahulu</p>
                    </div>

                    <!-- Settings -->
                    <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-500 to-gray-700 flex items-center justify-center">
                                <Settings class="w-4 h-4 text-white" />
                            </div>
                            Pengaturan
                        </h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                                <input v-model="form.is_dismissible" type="checkbox" class="w-5 h-5 rounded text-amber-500 focus:ring-amber-500" />
                                <div>
                                    <span class="text-xs font-medium text-gray-700 block">Dapat ditutup</span>
                                    <span class="text-[10px] text-gray-500">Pengunjung dapat menutup pengumuman</span>
                                </div>
                            </label>
                            
                            <label class="flex items-center gap-3 p-3 bg-emerald-50 rounded-xl cursor-pointer">
                                <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded text-emerald-500 focus:ring-emerald-500" />
                                <div>
                                    <span class="text-xs font-medium text-emerald-700 block flex items-center gap-1">
                                        <Eye class="w-3.5 h-3.5" />
                                        Aktifkan sekarang
                                    </span>
                                    <span class="text-[10px] text-emerald-600">Langsung tampil setelah disimpan</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                        <Save v-else class="w-5 h-5" />
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Pengumuman' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.form-section {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
