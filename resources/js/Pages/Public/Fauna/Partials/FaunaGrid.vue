<script setup>
import { Link } from '@inertiajs/vue3';
import { Bird } from 'lucide-vue-next';
import FaunaCard from './FaunaCard.vue';

defineProps({ fauna: { type: Object, required: true } });
</script>

<template>
    <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div v-if="fauna.data?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <FaunaCard v-for="(item, index) in fauna.data" :key="item.id" :fauna="item" :index="index" />
            </div>

            <div v-else class="text-center py-20">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center">
                    <Bird class="w-12 h-12 text-gray-400" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Fauna Tidak Ditemukan</h3>
                <p class="text-gray-500 mb-8">Coba ubah kata kunci pencarian atau filter</p>
                <Link href="/fauna" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 transition-all">
                    Lihat Semua Fauna
                </Link>
            </div>

            <div v-if="fauna.data?.length > 0 && fauna.last_page > 1" class="mt-12 flex justify-center">
                <nav class="flex items-center gap-2">
                    <Link v-if="fauna.prev_page_url" :href="fauna.prev_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-amber-50 hover:border-amber-300 transition-all">Sebelumnya</Link>
                    <template v-for="page in fauna.last_page" :key="page">
                        <Link v-if="Math.abs(page - fauna.current_page) <= 2 || page === 1 || page === fauna.last_page" :href="`/fauna?page=${page}`"
                            :class="['w-10 h-10 rounded-xl flex items-center justify-center font-semibold transition-all', page === fauna.current_page ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30' : 'bg-white border border-gray-200 text-gray-700 hover:bg-amber-50 hover:border-amber-300']">
                            {{ page }}
                        </Link>
                        <span v-else-if="page === fauna.current_page - 3 || page === fauna.current_page + 3" class="px-2 text-gray-400">...</span>
                    </template>
                    <Link v-if="fauna.next_page_url" :href="fauna.next_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-amber-50 hover:border-amber-300 transition-all">Selanjutnya</Link>
                </nav>
            </div>
        </div>
    </section>
</template>
