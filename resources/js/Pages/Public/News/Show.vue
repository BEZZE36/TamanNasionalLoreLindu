<script setup>
import { computed } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ChevronRight, ArrowLeft, Calendar, Eye, Share2 } from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });
const props = defineProps({ article: Object, relatedNews: Array });
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
const shareUrl = computed(() => window.location.href);
</script>

<template>
    <div>
        <Head><title>{{ article.title }} - Berita TNLL</title><meta name="description" :content="article.excerpt || article.meta_description"></Head>
        
        <section class="relative pt-32 pb-20 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600 via-red-700 to-rose-900"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-rose-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <nav class="flex items-center gap-2 text-sm text-white/70 mb-6">
                    <Link href="/" class="hover:text-white">Beranda</Link><ChevronRight class="w-4 h-4" />
                    <Link href="/news" class="hover:text-white">Berita</Link><ChevronRight class="w-4 h-4" />
                    <span class="text-white line-clamp-1">{{ article.title }}</span>
                </nav>
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4">Berita</span>
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">{{ article.title }}</h1>
                    <div class="flex items-center gap-4 text-white/70">
                        <span class="flex items-center gap-1"><Calendar class="w-4 h-4" />{{ formatDate(article.published_at) }}</span>
                        <span v-if="article.views" class="flex items-center gap-1"><Eye class="w-4 h-4" />{{ article.views }} views</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-12 md:py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <div v-if="article.featured_image" class="aspect-video rounded-2xl overflow-hidden shadow-xl">
                            <img :src="article.featured_image" :alt="article.title" class="w-full h-full object-cover" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                        </div>
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-8">
                            <div class="prose prose-lg max-w-none text-gray-700" v-html="article.content"></div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><Share2 class="w-5 h-5 text-rose-500" />Bagikan Berita</h3>
                            <div class="flex gap-3">
                                <a :href="`https://wa.me/?text=${encodeURIComponent(article.title + ' - ' + shareUrl)}`" target="_blank" class="flex-1 py-2 bg-green-500 text-white rounded-xl text-center text-sm font-bold hover:bg-green-600 transition-colors">WhatsApp</a>
                                <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`" target="_blank" class="flex-1 py-2 bg-blue-600 text-white rounded-xl text-center text-sm font-bold hover:bg-blue-700 transition-colors">Facebook</a>
                            </div>
                        </div>
                        <div v-if="relatedNews?.length > 0" class="pt-8 border-t border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Berita Terkait</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <Link v-for="item in relatedNews" :key="item.id" :href="`/news/${item.slug}`" class="group bg-white rounded-xl overflow-hidden shadow-lg border border-gray-100 hover:shadow-xl transition-all">
                                    <div class="relative h-32 overflow-hidden"><img :src="item.featured_image || '/images/placeholder-no-image.svg'" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"></div>
                                    <div class="p-4"><h3 class="font-bold text-gray-900 text-sm group-hover:text-rose-600 transition-colors line-clamp-2">{{ item.title }}</h3></div>
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-6 sticky top-24">
                            <Link href="/news" class="w-full py-3 flex items-center justify-center gap-2 bg-gradient-to-r from-rose-500 to-red-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 transition-all"><ArrowLeft class="w-4 h-4" />Kembali ke Berita</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
