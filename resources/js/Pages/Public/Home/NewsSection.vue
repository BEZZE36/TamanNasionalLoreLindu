<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Newspaper, ArrowRight, Calendar, Clock, TrendingUp, Sparkles } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    news: { type: Array, default: () => [] }
});

const sectionRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        // Header entrance - stays visible until section leaves
        gsap.fromTo('.news-header > *', 
            { opacity: 0, y: 25 },
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 85%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                }
            }
        );
        
        // Featured news
        gsap.fromTo('.news-featured', 
            { opacity: 0, scale: 0.95, y: 30 },
            { opacity: 1, scale: 1, y: 0, duration: 0.7, ease: 'power2.out',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                }
            }
        );
        
        // Other news items
        gsap.fromTo('.news-item', 
            { opacity: 0, y: 30, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'back.out(1.2)',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 75%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                }
            }
        );
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-emerald-50/20 to-gray-50 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-0 w-72 h-72 bg-gradient-to-br from-emerald-200/25 to-teal-200/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-gradient-to-tl from-cyan-200/20 to-blue-200/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="news-header flex flex-col md:flex-row items-start md:items-end justify-between mb-10 gap-4">
                <div class="max-w-md">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                        <Newspaper class="w-3.5 h-3.5" />
                        <span class="text-[11px] font-semibold tracking-wide">Berita Terbaru</span>
                        <Sparkles class="w-3 h-3 text-emerald-500" />
                    </div>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-2">
                        Berita & 
                        <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                            Pengumuman
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500">Informasi terkini seputar Taman Nasional Lore Lindu</p>
                </div>
                <Link href="/news" class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-emerald-600 font-semibold text-sm hover:bg-emerald-50 hover:shadow-sm transition-all duration-300 group">
                    Lihat Semua
                    <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>

            <!-- News Grid -->
            <div v-if="news && news.length > 0" class="news-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
                <!-- Featured News (first item) -->
                <Link v-if="news[0]" :href="`/news/${news[0].slug}`" class="news-featured group md:col-span-2 lg:row-span-2 block">
                    <div class="relative h-full min-h-[280px] md:min-h-[400px] rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500">
                        <img 
                            :src="news[0].image_url || '/images/placeholder-no-image.svg'" 
                            :alt="news[0].title" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                            loading="lazy" 
                            @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                        >
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                        
                        <!-- Top Badge -->
                        <div class="absolute top-4 left-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-rose-500 to-pink-500 text-white text-[10px] font-bold shadow-lg">
                                <TrendingUp class="w-3 h-3" />
                                TERBARU
                            </span>
                        </div>
                        
                        <!-- Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-5 md:p-6">
                            <span class="inline-block px-3 py-1 rounded-lg text-[10px] font-semibold text-white bg-gradient-to-r from-emerald-500 to-teal-500 shadow-lg mb-3">
                                {{ news[0].category || 'Berita' }}
                            </span>
                            <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-white group-hover:text-emerald-200 transition-colors line-clamp-2 mb-3">
                                {{ news[0].title }}
                            </h3>
                            <div class="flex items-center gap-4 text-gray-300 text-[11px]">
                                <span class="flex items-center gap-1.5">
                                    <Calendar class="w-3.5 h-3.5" />
                                    {{ news[0].created_at }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <Clock class="w-3.5 h-3.5" />
                                    {{ news[0].read_time || '5 min' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Other News Items -->
                <Link 
                    v-for="item in news.slice(1, 5)" 
                    :key="item.id" 
                    :href="`/news/${item.slug}`" 
                    class="news-item group block"
                >
                    <div class="relative h-40 md:h-44 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                        <img 
                            :src="item.image_url || '/images/placeholder-no-image.svg'" 
                            :alt="item.title" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                            loading="lazy" 
                            @error="(e) => { e.target.onerror = null; e.target.src = '/images/placeholder-no-image.svg'; }"
                        >
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>
                        
                        <!-- Content -->
                        <div class="absolute bottom-0 left-0 right-0 p-3.5">
                            <span class="inline-block px-2 py-0.5 rounded text-[8px] font-semibold text-white bg-gradient-to-r from-teal-500 to-cyan-500 mb-2 shadow">
                                {{ item.category || 'Berita' }}
                            </span>
                            <h3 class="text-xs font-bold text-white group-hover:text-emerald-200 transition-colors line-clamp-2">
                                {{ item.title }}
                            </h3>
                        </div>
                        
                        <!-- Hover Arrow -->
                        <div class="absolute bottom-3 right-3 w-7 h-7 rounded-full bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300">
                            <ArrowRight class="w-3.5 h-3.5 text-white" />
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-4">
                    <Newspaper class="w-7 h-7 text-gray-400" />
                </div>
                <p class="text-gray-500 text-sm">Belum ada berita terbaru</p>
            </div>

            <!-- Mobile CTA -->
            <div class="mt-8 text-center md:hidden">
                <Link href="/news" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-sm shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                    Lihat Semua Berita
                    <ArrowRight class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
