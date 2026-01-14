<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, FolderOpen, Power } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ galleryCategory: { type: Object, required: true } });

const form = useForm({
    name: props.galleryCategory.name || '',
    description: props.galleryCategory.description || '',
    is_active: props.galleryCategory.is_active ?? true
});

const submit = () => { form.put(`/admin/gallery-categories/${props.galleryCategory.id}`); };
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <Link href="/admin/gallery-categories" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-6 h-6" />
                </Link>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Kategori</h1>
                    <p class="mt-1 text-violet-100/90">{{ galleryCategory.name }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-violet-50 to-purple-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><FolderOpen class="w-6 h-6 text-violet-500" />Detail Kategori</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori *</label>
                        <input v-model="form.name" type="text" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-violet-300 transition-all">
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-violet-500 focus:ring-violet-500">
                            <Power class="w-5 h-5 text-violet-500" />
                            <span class="font-medium text-gray-700">Aktifkan Kategori</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-violet-500 to-purple-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-violet-500/30 hover:shadow-violet-500/50 hover:-translate-y-1 transition-all disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Perbarui Kategori' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
