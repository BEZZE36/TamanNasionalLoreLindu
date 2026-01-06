<script setup>
import { Link } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';
import FloraCard from './FloraCard.vue';

defineProps({
    flora: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Grid -->
            <div v-if="flora.data?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <FloraCard 
                    v-for="(item, index) in flora.data"
                    :key="item.id"
                    :flora="item"
                    :index="index"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-20">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center">
                    <Sparkles class="w-12 h-12 text-gray-400" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Flora Tidak Ditemukan</h3>
                <p class="text-gray-500 mb-8">Coba ubah kata kunci pencarian atau filter</p>
                <Link 
                    href="/flora"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/30 hover:shadow-green-500/50 transition-all"
                >
                    Lihat Semua Flora
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="flora.data?.length > 0 && flora.last_page > 1" class="mt-12 flex justify-center">
                <nav class="flex items-center gap-2">
                    <!-- Previous -->
                    <Link 
                        v-if="flora.prev_page_url"
                        :href="flora.prev_page_url"
                        class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-300 transition-all"
                    >
                        Sebelumnya
                    </Link>

                    <!-- Page Numbers -->
                    <template v-for="page in flora.last_page" :key="page">
                        <Link 
                            v-if="Math.abs(page - flora.current_page) <= 2 || page === 1 || page === flora.last_page"
                            :href="`/flora?page=${page}`"
                            :class="[
                                'w-10 h-10 rounded-xl flex items-center justify-center font-semibold transition-all',
                                page === flora.current_page
                                    ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/30'
                                    : 'bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-300'
                            ]"
                        >
                            {{ page }}
                        </Link>
                        <span 
                            v-else-if="page === flora.current_page - 3 || page === flora.current_page + 3"
                            class="px-2 text-gray-400"
                        >
                            ...
                        </span>
                    </template>

                    <!-- Next -->
                    <Link 
                        v-if="flora.next_page_url"
                        :href="flora.next_page_url"
                        class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-300 transition-all"
                    >
                        Selanjutnya
                    </Link>
                </nav>
            </div>
        </div>
    </section>
</template>
