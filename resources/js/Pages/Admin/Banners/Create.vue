<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, Image, Smartphone, Link2, Calendar, Power } from 'lucide-vue-next';
import { ref, computed } from 'vue';

defineOptions({ layout: AdminLayout });

const form = useForm({
    title: '', subtitle: '', image: null, mobile_image: null,
    link_url: '', link_target: '_self', start_at: '', end_at: '', is_active: true
});

const desktopPreview = ref(null);
const mobilePreview = ref(null);

const onDesktopSelect = (e) => {
    const file = e.target.files[0];
    if (file) { form.image = file; desktopPreview.value = URL.createObjectURL(file); }
};

const onMobileSelect = (e) => {
    const file = e.target.files[0];
    if (file) { form.mobile_image = file; mobilePreview.value = URL.createObjectURL(file); }
};

const submit = () => { form.post('/admin/banners'); };
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <Link href="/admin/banners" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-6 h-6" />
                </Link>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Tambah Banner</h1>
                    <p class="mt-1 text-orange-100/90">Buat banner promosi baru</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Basic Info -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-orange-50 to-pink-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Image class="w-6 h-6 text-orange-500" />Informasi Banner</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Banner *</label>
                        <input v-model="form.title" type="text" required placeholder="Promo Tahun Baru 2026"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                        <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                        <input v-model="form.subtitle" type="text" placeholder="Diskon hingga 50% untuk semua tiket"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Image class="w-6 h-6 text-blue-500" />Gambar Banner</h2>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Desktop Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><Image class="w-4 h-4 inline mr-1" />Desktop (1920x600) *</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-orange-500 transition-colors">
                            <img v-if="desktopPreview" :src="desktopPreview" class="w-full h-32 object-cover rounded-lg mb-2">
                            <input type="file" @change="onDesktopSelect" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                        </div>
                        <p v-if="form.errors.image" class="text-red-500 text-xs mt-1">{{ form.errors.image }}</p>
                    </div>
                    <!-- Mobile Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><Smartphone class="w-4 h-4 inline mr-1" />Mobile (720x480)</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-pink-500 transition-colors">
                            <img v-if="mobilePreview" :src="mobilePreview" class="w-full h-32 object-cover rounded-lg mb-2">
                            <input type="file" @change="onMobileSelect" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link & Schedule -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-green-50 to-teal-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Link2 class="w-6 h-6 text-green-500" />Link & Jadwal</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL Link</label>
                            <input v-model="form.link_url" type="text" placeholder="https://..."
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Buka di</label>
                            <select v-model="form.link_target" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                                <option value="_self">Tab yang sama</option>
                                <option value="_blank">Tab baru</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><Calendar class="w-4 h-4 inline mr-1" />Mulai Tampil</label>
                            <input v-model="form.start_at" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><Calendar class="w-4 h-4 inline mr-1" />Berakhir</label>
                            <input v-model="form.end_at" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-green-300 transition-all">
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-green-500 focus:ring-green-500">
                            <Power class="w-5 h-5 text-green-500" />
                            <span class="font-medium text-gray-700">Aktifkan Banner</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-orange-500 to-pink-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-orange-500/30 hover:shadow-orange-500/50 hover:-translate-y-1 transition-all disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Simpan Banner' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
