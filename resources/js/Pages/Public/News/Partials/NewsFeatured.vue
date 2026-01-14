<script setup>
/**
 * NewsFeatured.vue - Premium Featured News Section
 * Features: Large hero card, side featured cards, GSAP animations
 * Theme: Rose/Red for News
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ArrowRight, Calendar, Eye, Heart, Clock, Star, Megaphone, User } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ 
    featuredNews: { type: Array, default: () => [] } 
});

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

const sectionRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.featured-card', 
            { opacity: 0, y: 30 }, 
            { 
                opacity: 1, 
                y: 0, 
                duration: 0.6, 
                stagger: 0.12, 
                ease: 'power3.out',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%', 
                    toggleActions: 'play none none none' 
                }
            }
        );
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Get first news as hero, rest as side cards
const heroNews = props.featuredNews[0];
const sideNews = props.featuredNews.slice(1, 4);
</script>

<template>
    <section v-if="featuredNews.length > 0" ref="sectionRef" class="py-8 sm:py-12 bg-gradient-to-b from-gray-50 via-rose-50/30 to-gray-50 relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-20 left-0 w-72 h-72 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Section Header -->
            <div class="text-center mb-6 sm:mb-8">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gradient-to-r from-rose-500/10 to-red-500/10 border border-rose-200 text-rose-700 mb-3">
                    <Star class="w-3.5 h-3.5 fill-rose-500" />
                    <span class="text-[10px] sm:text-xs font-semibold">Berita Unggulan</span>
                </div>
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900">Highlight Terbaru</h2>
            </div>

            <!-- Featured Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Hero Featured News (Large Card) -->
                <Link v-if="heroNews" :href="`/news/${heroNews.slug}`" 
                      class="featured-card group relative bg-white rounded-2xl overflow-hidden shadow-xl border border-gray-100 hover:border-rose-200 hover:shadow-2xl hover:shadow-rose-500/10 transition-all duration-500 lg:row-span-2">
                    <!-- Image -->
                    <div class="relative h-48 sm:h-56 lg:h-full lg:min-h-[320px] overflow-hidden">
                        <img :src="heroNews.image_url || '/images/placeholder-no-image.svg'" 
                             :alt="heroNews.title"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                             @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

                        <!-- Badge -->
                        <div class="absolute top-4 left-4 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-lg">
                                Berita
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center gap-1">
                                <Star class="w-3 h-3 fill-white" />
                                Unggulan
                            </span>
                        </div>

                        <!-- Content Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-white mb-2 line-clamp-2 group-hover:text-rose-200 transition-colors">
                                {{ heroNews.title }}
                            </h3>
                            <p v-if="heroNews.excerpt" class="text-white/70 text-xs sm:text-sm line-clamp-2 mb-3 leading-relaxed">
                                {{ heroNews.excerpt }}
                            </p>
                            <div class="flex flex-wrap items-center gap-3 text-white/60 text-[10px] sm:text-xs">
                                <span class="flex items-center gap-1">
                                    <User class="w-3 h-3" />{{ heroNews.author_name || 'Admin' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Calendar class="w-3 h-3" />{{ formatDate(heroNews.published_at) }}
                                </span>
                                <span v-if="heroNews.views_count" class="flex items-center gap-1">
                                    <Eye class="w-3 h-3" />{{ heroNews.views_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Side Featured News (Smaller Cards) -->
                <div class="space-y-4 sm:space-y-6">
                    <Link v-for="item in sideNews" :key="item.id" :href="`/news/${item.slug}`"
                          class="featured-card group flex bg-white rounded-xl overflow-hidden shadow-lg border border-gray-100 hover:border-rose-200 hover:shadow-xl transition-all duration-300">
                        <!-- Image -->
                        <div class="relative w-28 sm:w-36 shrink-0 overflow-hidden">
                            <img :src="item.image_url || '/images/placeholder-no-image.svg'" 
                                 :alt="item.title"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-black/20"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 p-3 sm:p-4 flex flex-col justify-center">
                            <div class="mb-1">
                                <span class="px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-bold bg-rose-100 text-rose-700">
                                    Berita
                                </span>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-rose-600 transition-colors mb-1.5 leading-tight">
                                {{ item.title }}
                            </h3>
                            <div class="flex items-center gap-2 text-[9px] sm:text-[10px] text-gray-400">
                                <span class="flex items-center gap-1">
                                    <Calendar class="w-2.5 h-2.5" />{{ formatDate(item.published_at) }}
                                </span>
                                <span v-if="item.views_count" class="flex items-center gap-1">
                                    <Eye class="w-2.5 h-2.5" />{{ item.views_count }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
