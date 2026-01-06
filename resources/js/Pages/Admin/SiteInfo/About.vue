<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import { Info, Save } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ content: { type: String, default: '' } });

const form = useForm({ content: props.content });
const showSuccess = ref(false);

const submit = () => {
    form.put('/admin/site-info/about', {
        onSuccess: () => { showSuccess.value = true; setTimeout(() => showSuccess.value = false, 3000); }
    });
};
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-60 h-60 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                    <Info class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Tentang Kami</h1>
                    <p class="mt-1 text-blue-100/90 text-lg">Edit konten halaman tentang kami</p>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div v-if="showSuccess" class="flex items-center gap-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-6 py-5 shadow-lg">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div><p class="font-bold text-emerald-800">Berhasil!</p><p class="text-emerald-600 text-sm">Tentang Kami berhasil diperbarui!</p></div>
        </div>

        <form @submit.prevent="submit">
            <div class="rounded-3xl bg-white shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="relative px-8 py-6 bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 shadow-lg shadow-blue-500/30">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div><h2 class="text-xl font-bold text-gray-900">Editor Konten</h2><p class="text-gray-500 text-sm">Gunakan TinyMCE untuk format teks kaya</p></div>
                    </div>
                </div>
                <div class="p-8">
                    <TinyMceEditor v-model="form.content" id="about-editor" :height="500" placeholder="Tulis informasi tentang kami di sini..." />
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button type="submit" :disabled="form.processing"
                    class="group relative inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-blue-500 to-indigo-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-500 disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
