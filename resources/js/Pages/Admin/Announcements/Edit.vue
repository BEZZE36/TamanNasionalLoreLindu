<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Bell, ArrowLeft, Save, Type, Palette, Link as LinkIcon, Eye } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ announcement: { type: Object, required: true } });

const form = useForm({
    title: props.announcement.title || '', message: props.announcement.message || '',
    type: props.announcement.type || 'banner', notification_type: props.announcement.notification_type || 'info',
    bg_color: props.announcement.bg_color || '#fbbf24', text_color: props.announcement.text_color || '#ffffff',
    link_url: props.announcement.link_url || '', link_text: props.announcement.link_text || '',
    is_dismissible: props.announcement.is_dismissible ?? true, is_active: props.announcement.is_active || false,
    start_date: props.announcement.start_date?.split('T')[0] || '', end_date: props.announcement.end_date?.split('T')[0] || ''
});

const submit = () => form.put(`/admin/announcements/${props.announcement.id}`, { onSuccess: () => router.visit('/admin/announcements') });
</script>

<template>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link href="/admin/announcements" class="p-2 rounded-xl bg-amber-100 hover:bg-amber-200 transition-colors">
                <ArrowLeft class="w-5 h-5 text-amber-600" />
            </Link>
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <Bell class="w-7 h-7 text-amber-600" />Edit Pengumuman
                </h1>
                <p class="text-gray-500 text-sm">{{ announcement.title }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Content -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><Type class="w-5 h-5 text-amber-500" />Konten</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul *</label>
                        <input v-model="form.title" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pesan *</label>
                        <textarea v-model="form.message" rows="3" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"></textarea>
                    </div>
                </div>
            </div>

            <!-- Type & Style -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><Palette class="w-5 h-5 text-amber-500" />Tipe & Tampilan</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
                        <select v-model="form.type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                            <option value="banner">Banner</option>
                            <option value="popup">Popup</option>
                            <option value="floating">Floating</option>
                            <option value="marquee">Running Text</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Notifikasi</label>
                        <select v-model="form.notification_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="success">Success</option>
                            <option value="danger">Danger</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warna Background</label>
                        <input v-model="form.bg_color" type="color" class="w-full h-10 rounded-xl border border-gray-200 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warna Teks</label>
                        <input v-model="form.text_color" type="color" class="w-full h-10 rounded-xl border border-gray-200 cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Link & Settings -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><LinkIcon class="w-5 h-5 text-amber-500" />Link & Jadwal</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Link</label>
                            <input v-model="form.link_url" type="url" placeholder="https://..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teks Link</label>
                            <input v-model="form.link_text" type="text" placeholder="Selengkapnya" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input v-model="form.start_date" type="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                            <input v-model="form.end_date" type="date" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer flex-1">
                            <input v-model="form.is_dismissible" type="checkbox" class="w-5 h-5 rounded text-amber-500 focus:ring-amber-500">
                            <span class="font-medium text-gray-700">Dapat ditutup</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-green-50 rounded-xl cursor-pointer flex-1">
                            <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded text-green-500 focus:ring-green-500">
                            <span class="font-medium text-green-700 flex items-center gap-1"><Eye class="w-4 h-4" />Aktifkan</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing"
                    class="flex-1 py-4 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-bold rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                    <Save class="w-5 h-5" />{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
                <Link href="/admin/announcements" class="px-6 py-4 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors">Batal</Link>
            </div>
        </form>
    </div>
</template>
