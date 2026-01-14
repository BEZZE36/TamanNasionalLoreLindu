<script setup>
/**
 * NewsArticles.vue - Premium News & Articles Section
 * Modern card design with GSAP stagger animations
 */
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Newspaper, FileText, Calendar, ArrowRight, Eye, Sparkles } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    news: {
        type: Array,
        default: () => []
    },
    articles: {
        type: Array,
        default: () => []
    }
});

const newsRef = ref(null);
const articlesRef = ref(null);

onMounted(() => {
    if (props.news.length > 0) {
        gsap.context(() => {
            gsap.fromTo('.news-card', 
                { opacity: 0, y: 20, scale: 0.95 }, 
                { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.1, ease: 'power2.out',
                    scrollTrigger: { trigger: newsRef.value, start: 'top 85%' }
                }
            );
        }, newsRef.value);
    }
    
    if (props.articles.length > 0) {
        gsap.context(() => {
            gsap.fromTo('.article-card', 
                { opacity: 0, y: 20, scale: 0.95 }, 
                { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.1, ease: 'power2.out',
                    scrollTrigger: { trigger: articlesRef.value, start: 'top 85%' }
                }
            );
        }, articlesRef.value);
    }
});
</script>

<template>
    <div class="space-y-4 sm:space-y-5">
        <!-- Latest News -->
        <div v-if="news.length > 0" ref="newsRef" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="p-4 sm:p-5 lg:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white shadow-lg shadow-red-500/25">
                            <Newspaper class="w-4 h-4 sm:w-5 sm:h-5" />
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900">Berita Terbaru</h2>
                            <p class="text-[10px] sm:text-xs text-gray-500">Informasi terkini dari TNLL</p>
                        </div>
                    </div>
                    <Link href="/news" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-700 transition-colors">
                        <span>Lihat Semua</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>

            <!-- News Grid -->
            <div class="p-3 sm:p-4 lg:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <Link v-for="item in news" :key="item.id" :href="`/news/${item.slug}`"
                        class="news-card group bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-lg sm:rounded-xl overflow-hidden border border-gray-100 hover:border-red-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <div class="aspect-video overflow-hidden">
                            <img :src="item.image_url" :alt="item.title"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        </div>
                        <div class="p-3 sm:p-4">
                            <h3 class="font-bold text-gray-900 text-xs sm:text-sm line-clamp-2 group-hover:text-red-600 transition-colors mb-2">{{ item.title }}</h3>
                            <div class="flex items-center gap-2 text-[9px] sm:text-[10px] text-gray-500">
                                <div class="flex items-center gap-1">
                                    <Calendar class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                    {{ item.formatted_date }}
                                </div>
                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                <div class="flex items-center gap-1">
                                    <Eye class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                    {{ item.views || 0 }}
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
                
                <!-- Mobile View All -->
                <Link href="/news" 
                    class="sm:hidden flex items-center justify-center gap-2 w-full py-3 mt-3 text-xs font-semibold text-red-600 hover:text-red-700 transition-colors border-t border-gray-100">
                    <span>Lihat Semua Berita</span>
                    <ArrowRight class="w-3.5 h-3.5" />
                </Link>
            </div>
        </div>

        <!-- Latest Articles -->
        <div v-if="articles.length > 0" ref="articlesRef" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="p-4 sm:p-5 lg:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/25">
                            <FileText class="w-4 h-4 sm:w-5 sm:h-5" />
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900">Artikel Terbaru</h2>
                            <p class="text-[10px] sm:text-xs text-gray-500">Tips dan panduan wisata</p>
                        </div>
                    </div>
                    <Link href="/blog" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        <span>Lihat Semua</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>

            <!-- Articles Grid -->
            <div class="p-3 sm:p-4 lg:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <Link v-for="item in articles" :key="item.id" :href="`/blog/${item.slug}`"
                        class="article-card group bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-lg sm:rounded-xl overflow-hidden border border-gray-100 hover:border-blue-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <div class="aspect-video overflow-hidden relative">
                            <img :src="item.image_url" :alt="item.title"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-2 left-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[8px] sm:text-[9px] font-semibold bg-white/90 backdrop-blur-sm text-blue-600 shadow-sm">
                                    <Sparkles class="w-2 h-2 sm:w-2.5 sm:h-2.5" />
                                    {{ item.category_label }}
                                </span>
                            </div>
                        </div>
                        <div class="p-3 sm:p-4">
                            <h3 class="font-bold text-gray-900 text-xs sm:text-sm line-clamp-2 group-hover:text-blue-600 transition-colors">{{ item.title }}</h3>
                        </div>
                    </Link>
                </div>
                
                <!-- Mobile View All -->
                <Link href="/blog" 
                    class="sm:hidden flex items-center justify-center gap-2 w-full py-3 mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors border-t border-gray-100">
                    <span>Lihat Semua Artikel</span>
                    <ArrowRight class="w-3.5 h-3.5" />
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.news-card, .article-card {
    will-change: transform, opacity;
}
</style>
