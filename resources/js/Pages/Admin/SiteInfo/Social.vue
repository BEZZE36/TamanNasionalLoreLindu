<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Share2, Save } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ social: { type: Object, default: () => ({}) } });

const form = useForm({
    facebook: props.social.facebook || '',
    instagram: props.social.instagram || '',
    twitter: props.social.twitter || '',
    youtube: props.social.youtube || '',
    tiktok: props.social.tiktok || ''
});

const showSuccess = ref(false);

const submit = () => {
    form.put('/admin/site-info/social', {
        onSuccess: () => { showSuccess.value = true; setTimeout(() => showSuccess.value = false, 3000); }
    });
};

const socialPlatforms = [
    { key: 'facebook', name: 'Facebook', color: 'from-blue-500 to-blue-600', icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>` },
    { key: 'instagram', name: 'Instagram', color: 'from-pink-500 via-purple-500 to-orange-400', icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>` },
    { key: 'twitter', name: 'X (Twitter)', color: 'from-gray-800 to-gray-900', icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>` },
    { key: 'youtube', name: 'YouTube', color: 'from-red-500 to-red-600', icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>` },
    { key: 'tiktok', name: 'TikTok', color: 'from-black to-gray-800', icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>` }
];
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-60 h-60 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                    <Share2 class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Sosial Media</h1>
                    <p class="mt-1 text-pink-100/90 text-lg">Kelola tautan sosial media website</p>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div v-if="showSuccess" class="flex items-center gap-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-6 py-5 shadow-lg">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div><p class="font-bold text-emerald-800">Berhasil!</p><p class="text-emerald-600 text-sm">Sosial Media berhasil diperbarui!</p></div>
        </div>

        <form @submit.prevent="submit">
            <div class="rounded-3xl bg-white shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="relative px-8 py-6 bg-gradient-to-r from-pink-50 via-purple-50 to-indigo-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-400 to-purple-500 shadow-lg shadow-pink-500/30">
                            <Share2 class="w-7 h-7 text-white" />
                        </div>
                        <div><h2 class="text-xl font-bold text-gray-900">Tautan Sosial Media</h2><p class="text-gray-500 text-sm">Masukkan URL profil sosial media</p></div>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <div v-for="platform in socialPlatforms" :key="platform.key" class="group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ platform.name }}</label>
                        <div class="flex gap-3">
                            <div :class="['flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-r text-white shadow-lg transition-transform group-hover:scale-110', platform.color]">
                                <div class="w-6 h-6" v-html="platform.icon"></div>
                            </div>
                            <input v-model="form[platform.key]" type="url" :placeholder="`https://${platform.key}.com/...`"
                                class="flex-1 px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button type="submit" :disabled="form.processing"
                    class="group inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-purple-500/30 hover:shadow-purple-500/50 hover:-translate-y-1 transition-all duration-500 disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
