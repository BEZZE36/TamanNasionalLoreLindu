<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Calendar } from 'lucide-vue-next';

defineProps({ featuredArticles: { type: Array, default: () => [] } });
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
</script>

<template>
    <section v-if="featuredArticles.length > 0" class="py-12 bg-cyan-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Artikel Unggulan ✨</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Link v-for="item in featuredArticles" :key="item.id" :href="`/blog/${item.slug}`"
                    class="group bg-white rounded-2xl overflow-hidden shadow-lg border border-cyan-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="relative h-40 overflow-hidden">
                        <img :src="item.featured_image || '/images/placeholder-no-image.svg'" :alt="item.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                            @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <span class="absolute bottom-3 left-3 px-2 py-1 bg-white/90 rounded-full text-xs font-bold text-cyan-600">{{ item.category_label || item.category }}</span>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-900 group-hover:text-cyan-600 transition-colors line-clamp-2 mb-2">{{ item.title }}</h3>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span class="flex items-center gap-1"><Calendar class="w-4 h-4" />{{ formatDate(item.published_at) }}</span>
                            <span class="flex items-center gap-1 text-cyan-600">Baca <ArrowRight class="w-4 h-4" /></span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
