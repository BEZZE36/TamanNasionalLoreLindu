<script setup>
import { Link } from '@inertiajs/vue3';
import { Newspaper } from 'lucide-vue-next';
import BlogCard from './BlogCard.vue';

defineProps({ articles: { type: Object, required: true } });
</script>

<template>
    <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div v-if="articles.data?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <BlogCard v-for="(item, index) in articles.data" :key="item.id" :article="item" :index="index" />
            </div>

            <div v-else class="text-center py-20">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center">
                    <Newspaper class="w-12 h-12 text-gray-400" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Artikel Tidak Ditemukan</h3>
                <p class="text-gray-500 mb-8">Coba ubah kata kunci pencarian atau filter</p>
                <Link href="/blog" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold rounded-xl shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all">Lihat Semua Artikel</Link>
            </div>

            <div v-if="articles.data?.length > 0 && articles.last_page > 1" class="mt-12 flex justify-center">
                <nav class="flex items-center gap-2">
                    <Link v-if="articles.prev_page_url" :href="articles.prev_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-cyan-50 hover:border-cyan-300 transition-all">Sebelumnya</Link>
                    <template v-for="page in articles.last_page" :key="page">
                        <Link v-if="Math.abs(page - articles.current_page) <= 2 || page === 1 || page === articles.last_page" :href="`/blog?page=${page}`"
                            :class="['w-10 h-10 rounded-xl flex items-center justify-center font-semibold transition-all', page === articles.current_page ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/30' : 'bg-white border border-gray-200 text-gray-700 hover:bg-cyan-50 hover:border-cyan-300']">
                            {{ page }}
                        </Link>
                        <span v-else-if="page === articles.current_page - 3 || page === articles.current_page + 3" class="px-2 text-gray-400">...</span>
                    </template>
                    <Link v-if="articles.next_page_url" :href="articles.next_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-cyan-50 hover:border-cyan-300 transition-all">Selanjutnya</Link>
                </nav>
            </div>
        </div>
    </section>
</template>
