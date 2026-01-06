<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Calendar, Eye, ArrowRight } from 'lucide-vue-next';

const props = defineProps({ article: { type: Object, required: true }, index: { type: Number, default: 0 } });
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
const animationDelay = computed(() => `${props.index * 0.05}s`);
</script>

<template>
    <Link :href="`/news/${article.slug}`" class="group block bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-500 hover:-translate-y-2 border border-gray-100" :style="{ animationDelay }">
        <div class="relative h-48 overflow-hidden">
            <img :src="article.featured_image || '/images/placeholder-no-image.svg'" :alt="article.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" loading="lazy" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <div class="absolute top-4 left-4"><span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500 text-white">Berita</span></div>
        </div>
        <div class="p-5">
            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-rose-600 transition-colors">{{ article.title }}</h3>
            <p v-if="article.excerpt" class="text-sm text-gray-600 line-clamp-2 mb-4">{{ article.excerpt }}</p>
            <div class="flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1"><Calendar class="w-4 h-4" />{{ formatDate(article.published_at) }}</span>
                    <span v-if="article.views" class="flex items-center gap-1"><Eye class="w-4 h-4" />{{ article.views }}</span>
                </div>
                <span class="flex items-center gap-1 text-rose-600 font-medium">Baca <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" /></span>
            </div>
        </div>
    </Link>
</template>

<style scoped>
a { animation: fade-in-up 0.6s ease-out forwards; opacity: 0; }
@keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); }}
</style>
