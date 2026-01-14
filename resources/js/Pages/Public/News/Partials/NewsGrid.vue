<script setup>
/**
 * NewsGrid.vue - Premium News Grid with GSAP animations
 * Features: Section header, background decorations, empty state, pagination
 * Theme: Rose/Red for News
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Megaphone, Sparkles, ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import NewsCard from './NewsCard.vue';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ 
    news: { type: Object, required: true } 
});

const gridRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        // Stagger animation for cards
        gsap.fromTo('.news-card', 
            { opacity: 0, y: 30 }, 
            { 
                opacity: 1, 
                y: 0, 
                duration: 0.5, 
                stagger: 0.08, 
                ease: 'power3.out',
                scrollTrigger: { 
                    trigger: gridRef.value, 
                    start: 'top 85%', 
                    toggleActions: 'play none none none' 
                }
            }
        );
    }, gridRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="gridRef" class="py-8 sm:py-12 lg:py-16 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-0 right-0 w-72 h-72 bg-rose-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-red-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 sm:mb-8">
                <div>
                    <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <Sparkles class="w-5 h-5 text-rose-500" />
                        Semua Berita
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500">Update terbaru seputar Taman Nasional Lore Lindu</p>
                </div>
            </div>

            <!-- News Grid -->
            <div v-if="news.data?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <NewsCard v-for="(item, index) in news.data" :key="item.id" :article="item" :index="index" />
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 sm:py-16 lg:py-20">
                <div class="relative w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-6">
                    <!-- Animated rings -->
                    <div class="absolute inset-0 rounded-full bg-gradient-to-r from-rose-400 to-red-400 opacity-20 animate-ping"></div>
                    <div class="absolute inset-2 rounded-full bg-gradient-to-r from-rose-100 to-red-100"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-2xl shadow-rose-500/30 rotate-3">
                            <Megaphone class="w-8 h-8 sm:w-10 sm:h-10 text-white" />
                        </div>
                    </div>
                </div>
                
                <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2">Berita Tidak Ditemukan</h3>
                <p class="text-gray-500 text-xs sm:text-sm mb-6 max-w-md mx-auto leading-relaxed">
                    Coba ubah kata kunci pencarian atau filter untuk menemukan berita lainnya
                </p>
                <Link href="/news" 
                      class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-rose-500 to-red-600 text-white font-semibold rounded-xl shadow-lg shadow-rose-500/30 hover:shadow-xl hover:shadow-rose-500/40 hover:-translate-y-0.5 transition-all text-sm">
                    <Sparkles class="w-4 h-4" />
                    Lihat Semua Berita
                    <ArrowRight class="w-4 h-4" />
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="news.data?.length > 0 && news.last_page > 1" class="mt-8 sm:mt-12 flex justify-center">
                <nav class="flex items-center gap-1 sm:gap-2 flex-wrap justify-center">
                    <!-- Previous -->
                    <Link v-if="news.prev_page_url" 
                          :href="news.prev_page_url" 
                          class="px-3 sm:px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-600 transition-all text-xs sm:text-sm font-medium flex items-center gap-1">
                        <ChevronLeft class="w-4 h-4" />
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </Link>

                    <!-- Page Numbers -->
                    <template v-for="page in news.last_page" :key="page">
                        <Link v-if="Math.abs(page - news.current_page) <= 2 || page === 1 || page === news.last_page" 
                              :href="`/news?page=${page}`"
                              :class="['w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center font-semibold transition-all text-xs sm:text-sm', 
                                       page === news.current_page 
                                           ? 'bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-lg shadow-rose-500/30' 
                                           : 'bg-white border border-gray-200 text-gray-700 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-600']">
                            {{ page }}
                        </Link>
                        <span v-else-if="page === news.current_page - 3 || page === news.current_page + 3" 
                              class="px-1 sm:px-2 text-gray-400 text-xs sm:text-sm">...</span>
                    </template>

                    <!-- Next -->
                    <Link v-if="news.next_page_url" 
                          :href="news.next_page_url" 
                          class="px-3 sm:px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-600 transition-all text-xs sm:text-sm font-medium flex items-center gap-1">
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </nav>
            </div>
        </div>
    </section>
</template>
