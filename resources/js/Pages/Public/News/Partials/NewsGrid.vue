<script setup>
import { Link } from '@inertiajs/vue3';
import { Megaphone } from 'lucide-vue-next';
import NewsCard from './NewsCard.vue';

defineProps({ news: { type: Object, required: true } });
</script>

<template>
    <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div v-if="news.data?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <NewsCard v-for="(item, index) in news.data" :key="item.id" :article="item" :index="index" />
            </div>
            <div v-else class="text-center py-20">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center"><Megaphone class="w-12 h-12 text-gray-400" /></div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Berita Tidak Ditemukan</h3>
                <p class="text-gray-500 mb-8">Coba ubah kata kunci pencarian</p>
                <Link href="/news" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-rose-500 to-red-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 transition-all">Lihat Semua Berita</Link>
            </div>
            <div v-if="news.data?.length > 0 && news.last_page > 1" class="mt-12 flex justify-center">
                <nav class="flex items-center gap-2">
                    <Link v-if="news.prev_page_url" :href="news.prev_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-rose-50 hover:border-rose-300 transition-all">Sebelumnya</Link>
                    <template v-for="page in news.last_page" :key="page">
                        <Link v-if="Math.abs(page - news.current_page) <= 2 || page === 1 || page === news.last_page" :href="`/news?page=${page}`"
                            :class="['w-10 h-10 rounded-xl flex items-center justify-center font-semibold transition-all', page === news.current_page ? 'bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-lg shadow-rose-500/30' : 'bg-white border border-gray-200 text-gray-700 hover:bg-rose-50 hover:border-rose-300']">{{ page }}</Link>
                    </template>
                    <Link v-if="news.next_page_url" :href="news.next_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-rose-50 hover:border-rose-300 transition-all">Selanjutnya</Link>
                </nav>
            </div>
        </div>
    </section>
</template>
