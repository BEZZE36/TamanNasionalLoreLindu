<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, MapPin, Eye, Globe, ExternalLink, Edit, Search, Tag, Calendar, Clock, Play, Image as ImageIcon } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    gallery: Object
});

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);

const hasMedia = computed(() => (props.gallery?.media?.length || 0) > 0);
const totalMedia = computed(() => (props.gallery?.media?.length || 0) + 1);
</script>

<template>
    <div class="min-h-screen -m-6 bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 p-6">
        <!-- Preview Header -->
        <div class="max-w-6xl mx-auto mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="`/admin/gallery/${gallery.id}/edit`" class="p-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-white transition-all duration-300 backdrop-blur-sm border border-white/10">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500/20 to-rose-500/20 rounded-xl backdrop-blur-sm border border-pink-500/20">
                        <Eye class="w-4 h-4 text-pink-400" />
                        <span class="text-white/80 text-sm font-medium">Preview Mode</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="[
                        'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg',
                        gallery.is_active ? 'bg-gradient-to-r from-emerald-500 to-green-600 text-white shadow-emerald-500/30' : 'bg-gray-600 text-gray-200'
                    ]">
                        {{ gallery.is_active ? 'Aktif' : 'Draft' }}
                    </span>
                    <span v-if="gallery.is_featured" class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30">
                        Unggulan
                    </span>
                </div>
            </div>
        </div>

        <!-- Preview Content -->
        <div class="max-w-6xl mx-auto bg-white rounded-3xl overflow-hidden shadow-2xl shadow-black/50">
            <!-- Hero Section -->
            <div class="relative h-[400px] overflow-hidden">
                <img :src="gallery.image_url || '/images/placeholder-no-image.svg'" :alt="gallery.title" class="w-full h-full object-cover" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <div v-if="gallery.category?.name" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-pink-500 to-rose-600 text-white text-xs font-bold uppercase tracking-wider mb-4 shadow-lg shadow-pink-500/30">
                        <Tag class="w-3 h-3" />
                        {{ gallery.category.name }}
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white leading-tight mb-4">
                        {{ gallery.title }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-6 text-white/80 text-sm">
                        <div v-if="gallery.location" class="flex items-center gap-2">
                            <MapPin class="w-4 h-4 text-pink-400" />
                            {{ gallery.location }}
                        </div>
                        <div v-if="gallery.destination?.name" class="flex items-center gap-2">
                            <Globe class="w-4 h-4 text-blue-400" />
                            {{ gallery.destination.name }}
                        </div>
                        <div class="flex items-center gap-2">
                            <Eye class="w-4 h-4 text-emerald-400" />
                            {{ formatNumber(gallery.view_count) }} views
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8 space-y-8">
                <!-- Description -->
                <div v-if="gallery.description" class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg flex items-center justify-center">
                            <span class="text-white text-sm">📝</span>
                        </div>
                        Deskripsi
                    </h2>
                    <div class="prose prose-gray max-w-none text-gray-600" v-html="gallery.description"></div>
                </div>

                <!-- Media Grid -->
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">
                            <ImageIcon class="w-4 h-4 text-white" />
                        </div>
                        Media ({{ totalMedia }} items)
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <!-- Cover Image -->
                        <div class="aspect-square rounded-2xl overflow-hidden relative group shadow-lg">
                            <img :src="gallery.image_url || '/images/placeholder-no-image.svg'" :alt="gallery.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                            <span class="absolute top-3 left-3 px-3 py-1 bg-gradient-to-r from-pink-500 to-rose-600 text-white text-xs font-bold rounded-full shadow-lg">Cover</span>
                        </div>
                        <!-- Other Media -->
                        <div v-for="media in gallery.media" :key="media.id" class="aspect-square rounded-2xl overflow-hidden relative group shadow-lg">
                            <template v-if="media.type === 'image'">
                                <img :src="media.image_url || `/images/gallery-media/${media.id}`" :alt="media.alt_text" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" @error="(e) => e.target.src='/images/placeholder-no-image.svg'">
                            </template>
                            <template v-else>
                                <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                                    <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
                                        <Play class="w-8 h-8 text-white fill-white" />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- SEO Info -->
                <div v-if="gallery.meta_title || gallery.meta_description || gallery.meta_keywords" class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                    <h2 class="text-lg font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                            <Search class="w-4 h-4 text-white" />
                        </div>
                        SEO Info
                    </h2>
                    <dl class="space-y-4 text-sm">
                        <div v-if="gallery.meta_title">
                            <dt class="font-semibold text-blue-700 mb-1">Meta Title</dt>
                            <dd class="text-gray-700 bg-white/50 rounded-lg p-3">{{ gallery.meta_title }}</dd>
                        </div>
                        <div v-if="gallery.meta_description">
                            <dt class="font-semibold text-blue-700 mb-1">Meta Description</dt>
                            <dd class="text-gray-700 bg-white/50 rounded-lg p-3">{{ gallery.meta_description }}</dd>
                        </div>
                        <div v-if="gallery.meta_keywords">
                            <dt class="font-semibold text-blue-700 mb-1">Meta Keywords</dt>
                            <dd class="text-gray-700 bg-white/50 rounded-lg p-3">{{ gallery.meta_keywords }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Tags -->
                <div v-if="gallery.tags?.length > 0">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center">
                            <Tag class="w-4 h-4 text-white" />
                        </div>
                        Tags
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(tag, i) in gallery.tags" :key="i" class="px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 text-sm font-medium rounded-full hover:from-gray-200 hover:to-gray-300 transition-all cursor-default">
                            #{{ tag }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-100 p-6 flex flex-col md:flex-row items-center justify-between gap-4 bg-gradient-to-br from-gray-50 to-gray-100">
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-gray-400" />
                        <span>Dibuat: {{ formatDate(gallery.created_at) }}</span>
                    </div>
                    <div v-if="gallery.updated_at !== gallery.created_at" class="flex items-center gap-2">
                        <Clock class="w-4 h-4 text-gray-400" />
                        <span>Diperbarui: {{ formatDate(gallery.updated_at) }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="`/admin/gallery/${gallery.id}/edit`" class="px-5 py-2.5 bg-gradient-to-r from-gray-800 to-gray-900 text-white font-bold rounded-xl hover:from-gray-900 hover:to-black transition-all shadow-lg flex items-center gap-2">
                        <Edit class="w-4 h-4" />
                        Edit Album
                    </Link>
                    <a v-if="gallery.is_active && gallery.slug" :href="`/gallery/${gallery.slug}`" target="_blank" class="px-5 py-2.5 bg-gradient-to-r from-pink-500 to-rose-600 text-white font-bold rounded-xl hover:from-pink-600 hover:to-rose-700 transition-all shadow-lg shadow-pink-500/30 flex items-center gap-2">
                        <ExternalLink class="w-4 h-4" />
                        Lihat di Publik
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
