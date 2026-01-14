<script setup>
/**
 * FaunaGrid.vue - Premium Grid with GSAP Animations
 */
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Bird, ChevronLeft, ChevronRight, Rabbit } from 'lucide-vue-next';
import FaunaCard from './FaunaCard.vue';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ fauna: { type: Object, required: true } });
const gridRef = ref(null);
let ctx;

onMounted(async () => {
    await nextTick();
    if (!gridRef.value) return;
    
    ctx = gsap.context(() => {
        gsap.fromTo('.fauna-card', { opacity: 0, y: 30, scale: 0.95 }, {
            opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.06, ease: 'power2.out',
            scrollTrigger: { trigger: gridRef.value, start: 'top 85%', end: 'bottom top', toggleActions: 'play reverse play reverse' }
        });
    }, gridRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="gridRef" class="py-8 sm:py-12 md:py-16 bg-gradient-to-b from-white via-gray-50/50 to-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6 sm:mb-8">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25">
                        <Rabbit class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-white" />
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-gray-900">Koleksi Fauna</h2>
                        <p class="text-[10px] sm:text-[11px] text-gray-500">{{ fauna.total || 0 }} spesies ditemukan</p>
                    </div>
                </div>
            </div>

            <div v-if="fauna.data?.length > 0" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                <FaunaCard v-for="(item, index) in fauna.data" :key="item.id" :fauna="item" :index="index" />
            </div>

            <div v-else class="text-center py-16 sm:py-20">
                <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-5 sm:mb-6 bg-gradient-to-br from-amber-100 to-orange-100 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/10">
                    <Bird class="w-10 h-10 sm:w-12 sm:h-12 text-amber-400" />
                </div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Fauna Tidak Ditemukan</h3>
                <p class="text-gray-500 text-xs sm:text-sm mb-6 max-w-sm mx-auto">Coba ubah kata kunci atau filter</p>
                <Link href="/fauna" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 hover:-translate-y-0.5 transition-all">
                    <Bird class="w-4 h-4" />Lihat Semua Fauna
                </Link>
            </div>

            <div v-if="fauna.data?.length > 0 && fauna.last_page > 1" class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
                <p class="sm:hidden text-[11px] text-gray-500">Halaman {{ fauna.current_page }} dari {{ fauna.last_page }}</p>
                
                <nav class="flex items-center gap-1.5 sm:gap-2">
                    <Link v-if="fauna.prev_page_url" :href="fauna.prev_page_url" class="flex items-center gap-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-amber-50 hover:border-amber-300 transition-all text-[11px] sm:text-xs font-medium shadow-sm">
                        <ChevronLeft class="w-3.5 h-3.5" /><span class="hidden sm:inline">Sebelumnya</span>
                    </Link>
                    <span v-else class="flex items-center gap-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-50 border border-gray-100 text-gray-300 text-[11px] sm:text-xs font-medium cursor-not-allowed">
                        <ChevronLeft class="w-3.5 h-3.5" /><span class="hidden sm:inline">Sebelumnya</span>
                    </span>

                    <div class="hidden sm:flex items-center gap-1">
                        <template v-for="page in fauna.last_page" :key="page">
                            <Link v-if="Math.abs(page - fauna.current_page) <= 2 || page === 1 || page === fauna.last_page" :href="`/fauna?page=${page}`" :class="['w-9 h-9 rounded-xl flex items-center justify-center font-semibold text-xs transition-all', page === fauna.current_page ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30' : 'bg-white border border-gray-200 text-gray-700 hover:bg-amber-50 hover:border-amber-300']">{{ page }}</Link>
                            <span v-else-if="page === fauna.current_page - 3 || page === fauna.current_page + 3" class="px-1 text-gray-400 text-xs">...</span>
                        </template>
                    </div>

                    <div class="sm:hidden flex items-center gap-1">
                        <span class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-xs shadow-lg shadow-amber-500/30">{{ fauna.current_page }}</span>
                        <span class="text-gray-400 text-xs">/</span>
                        <span class="text-gray-500 text-xs font-medium">{{ fauna.last_page }}</span>
                    </div>

                    <Link v-if="fauna.next_page_url" :href="fauna.next_page_url" class="flex items-center gap-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-amber-50 hover:border-amber-300 transition-all text-[11px] sm:text-xs font-medium shadow-sm">
                        <span class="hidden sm:inline">Selanjutnya</span><ChevronRight class="w-3.5 h-3.5" />
                    </Link>
                    <span v-else class="flex items-center gap-1 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-gray-50 border border-gray-100 text-gray-300 text-[11px] sm:text-xs font-medium cursor-not-allowed">
                        <span class="hidden sm:inline">Selanjutnya</span><ChevronRight class="w-3.5 h-3.5" />
                    </span>
                </nav>
            </div>
        </div>
    </section>
</template>
