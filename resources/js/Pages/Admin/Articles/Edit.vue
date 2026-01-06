<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import { FileText, ArrowLeft, Save, Image, Type, AlignLeft, Star, Eye, User } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    article: { type: Object, required: true },
    categories: { type: Object, default: () => ({}) }
});

const form = useForm({
    title: props.article.title || '', excerpt: props.article.excerpt || '', content: props.article.content || '',
    featured_image: null, category: props.article.category || 'wisata',
    author_name: props.article.author_name || '', is_featured: props.article.is_featured || false, is_published: props.article.is_published || false
});

const imagePreview = ref(props.article.image_url || null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.featured_image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(`/admin/articles/${props.article.id}`, {
        forceFormData: true,
        _method: 'PUT',
        onSuccess: () => router.visit('/admin/articles')
    });
};
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link href="/admin/articles" class="p-2 rounded-xl bg-rose-100 hover:bg-rose-200 transition-colors">
                <ArrowLeft class="w-5 h-5 text-rose-600" />
            </Link>
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <FileText class="w-7 h-7 text-rose-600" />Edit Artikel
                </h1>
                <p class="text-gray-500 text-sm line-clamp-1">{{ article.title }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title & Excerpt -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><Type class="w-5 h-5 text-rose-500" />Judul & Ringkasan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel *</label>
                            <input v-model="form.title" type="text" required placeholder="Masukkan judul artikel..."
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 text-lg font-medium">
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ringkasan/Excerpt</label>
                            <textarea v-model="form.excerpt" rows="3" placeholder="Ringkasan singkat artikel..."
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><AlignLeft class="w-5 h-5 text-rose-500" />Konten Artikel *</h3>
                    <TinyMceEditor v-model="form.content" id="article-content-edit" :height="400" placeholder="Tulis konten artikel..." />
                    <p v-if="form.errors.content" class="text-red-500 text-xs mt-1">{{ form.errors.content }}</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Featured Image -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><Image class="w-5 h-5 text-rose-500" />Gambar Utama</h3>
                    <div class="space-y-4">
                        <div v-if="imagePreview" class="aspect-video rounded-xl overflow-hidden bg-gray-100">
                            <img :src="imagePreview" class="w-full h-full object-cover">
                        </div>
                        <label class="block w-full py-3 bg-rose-50 text-rose-600 font-medium text-center rounded-xl cursor-pointer hover:bg-rose-100 transition-colors">
                            <input type="file" accept="image/*" @change="handleImageChange" class="hidden">
                            {{ imagePreview ? 'Ganti Gambar' : 'Pilih Gambar' }}
                        </label>
                        <p v-if="form.errors.featured_image" class="text-red-500 text-xs">{{ form.errors.featured_image }}</p>
                    </div>
                </div>

                <!-- Settings -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Pengaturan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                            <select v-model="form.category" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                                <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><User class="w-4 h-4 inline" /> Penulis</label>
                            <input v-model="form.author_name" type="text" placeholder="Nama penulis..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20">
                        </div>
                        <label class="flex items-center gap-3 p-3 bg-yellow-50 rounded-xl cursor-pointer">
                            <input v-model="form.is_featured" type="checkbox" class="w-5 h-5 rounded text-yellow-500 focus:ring-yellow-500">
                            <span class="font-medium text-yellow-700 flex items-center gap-1"><Star class="w-4 h-4" />Artikel Unggulan</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-green-50 rounded-xl cursor-pointer">
                            <input v-model="form.is_published" type="checkbox" class="w-5 h-5 rounded text-green-500 focus:ring-green-500">
                            <span class="font-medium text-green-700 flex items-center gap-1"><Eye class="w-4 h-4" />Publikasikan</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="flex-1 py-4 bg-gradient-to-r from-rose-500 to-pink-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Save class="w-5 h-5" />{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                    <Link href="/admin/articles" class="px-4 py-4 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors">Batal</Link>
                </div>
            </div>
        </form>
    </div>
</template>
