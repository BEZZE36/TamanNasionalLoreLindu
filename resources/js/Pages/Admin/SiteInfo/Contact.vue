<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MapPin, Phone, Mail, Clock, Save, MessageCircle } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ contact: { type: Object, default: () => ({}) } });

const form = useForm({
    address: props.contact.address || '',
    phone: props.contact.phone || '',
    whatsapp: props.contact.whatsapp || '',
    email: props.contact.email || '',
    maps_embed: props.contact.maps_embed || '',
    operational_hours: props.contact.operational_hours || ''
});

const showSuccess = ref(false);

const submit = () => {
    form.put('/admin/site-info/contact', {
        onSuccess: () => { showSuccess.value = true; setTimeout(() => showSuccess.value = false, 3000); }
    });
};
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-60 h-60 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                    <Phone class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Informasi Kontak</h1>
                    <p class="mt-1 text-emerald-100/90 text-lg">Kelola alamat, telepon, dan informasi kontak</p>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div v-if="showSuccess" class="flex items-center gap-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-6 py-5 shadow-lg">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div><p class="font-bold text-emerald-800">Berhasil!</p><p class="text-emerald-600 text-sm">Informasi Kontak berhasil diperbarui!</p></div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Contact Info Card -->
            <div class="rounded-3xl bg-white shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="relative px-8 py-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg shadow-emerald-500/30">
                            <MapPin class="w-7 h-7 text-white" />
                        </div>
                        <div><h2 class="text-xl font-bold text-gray-900">Detail Kontak</h2><p class="text-gray-500 text-sm">Informasi kontak utama</p></div>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2"><MapPin class="w-4 h-4 text-emerald-500" />Alamat</label>
                        <textarea v-model="form.address" rows="3" placeholder="Masukkan alamat lengkap..."
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <Phone class="w-4 h-4 text-emerald-500" />Telepon
                            </label>
                            <input v-model="form.phone" type="text" placeholder="+62 xxx xxxx xxxx"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <MessageCircle class="w-4 h-4 text-green-500" />WhatsApp
                            </label>
                            <input v-model="form.whatsapp" type="text" placeholder="62xxxxxxxxxx"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <Mail class="w-4 h-4 text-red-500" />Email
                            </label>
                            <input v-model="form.email" type="email" placeholder="email@domain.com"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <Clock class="w-4 h-4 text-blue-500" />Jam Operasional
                            </label>
                            <input v-model="form.operational_hours" type="text" placeholder="Senin - Jumat: 08:00 - 16:00"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maps Embed Card -->
            <div class="rounded-3xl bg-white shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="relative px-8 py-6 bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 shadow-lg shadow-blue-500/30">
                            <!-- Google Maps icon -->
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="#4285F4"/>
                                <circle cx="12" cy="9" r="2.5" fill="white"/>
                            </svg>
                        </div>
                        <div><h2 class="text-xl font-bold text-gray-900">Google Maps Embed</h2><p class="text-gray-500 text-sm">Kode embed peta lokasi</p></div>
                    </div>
                </div>
                <div class="p-8">
                    <textarea v-model="form.maps_embed" rows="5" placeholder='<iframe src="https://www.google.com/maps/embed?..." ...></iframe>'
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-mono text-sm"></textarea>
                    <div v-if="form.maps_embed" class="mt-4 aspect-video rounded-xl overflow-hidden border border-gray-200" v-html="form.maps_embed"></div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                    class="group inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-1 transition-all duration-500 disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
