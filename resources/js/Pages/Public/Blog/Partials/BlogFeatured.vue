<script setup>
/**
 * BlogFeatured.vue - Premium Featured Articles Section
 * Features: Large hero card, side featured cards, GSAP animations
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ArrowRight, Calendar, Eye, Heart, Clock, Star, Sparkles, User } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ 
    featuredArticles: { type: Array, default: () => [] } 
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

// Get first article as hero, rest as side cards
const heroArticle = props.featuredArticles[0];
const sideArticles = props.featuredArticles.slice(1, 4);
</script>

<template>
    <section v-if="featuredArticles.length > 0" ref="sectionRef" class="py-8 sm:py-12 bg-gradient-to-b from-gray-50 via-cyan-50/30 to-gray-50 relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-20 left-0 w-72 h-72 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Section Header -->
            <div class="text-center mb-6 sm:mb-8">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gradient-to-r from-cyan-500/10 to-blue-500/10 border border-cyan-200 text-cyan-700 mb-3">
                    <Star class="w-3.5 h-3.5 fill-cyan-500" />
                    <span class="text-[10px] sm:text-xs font-semibold">Artikel Unggulan</span>
                </div>
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900">Pilihan Editor</h2>
            </div>

            <!-- Featured Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Hero Featured Article (Large Card) -->
                <Link v-if="heroArticle" :href="`/blog/${heroArticle.slug}`" 
                      class="featured-card group relative bg-white rounded-2xl overflow-hidden shadow-xl border border-gray-100 hover:border-cyan-200 hover:shadow-2xl hover:shadow-cyan-500/10 transition-all duration-500 lg:row-span-2">
                    <!-- Image -->
                    <div class="relative h-48 sm:h-56 lg:h-full lg:min-h-[320px] overflow-hidden">
                        <img :src="heroArticle.image_url || '/images/placeholder-no-image.svg'" 
                             :alt="heroArticle.title"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                             @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

                        <!-- Badge -->
                        <div class="absolute top-4 left-4 flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg">
                                {{ heroArticle.category_label || 'Artikel' }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white flex items-center gap-1">
                                <Star class="w-3 h-3 fill-white" />
                                Unggulan
                            </span>
                        </div>

                        <!-- Content Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6">
                            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-white mb-2 line-clamp-2 group-hover:text-cyan-200 transition-colors">
                                {{ heroArticle.title }}
                            </h3>
                            <p v-if="heroArticle.excerpt" class="text-white/70 text-xs sm:text-sm line-clamp-2 mb-3 leading-relaxed">
                                {{ heroArticle.excerpt }}
                            </p>
                            <div class="flex flex-wrap items-center gap-3 text-white/60 text-[10px] sm:text-xs">
                                <span class="flex items-center gap-1">
                                    <User class="w-3 h-3" />{{ heroArticle.author_name || 'Admin' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Calendar class="w-3 h-3" />{{ formatDate(heroArticle.published_at) }}
                                </span>
                                <span v-if="heroArticle.reading_time" class="flex items-center gap-1">
                                    <Clock class="w-3 h-3" />{{ heroArticle.reading_time }} min
                                </span>
                                <span v-if="heroArticle.views_count" class="flex items-center gap-1">
                                    <Eye class="w-3 h-3" />{{ heroArticle.views_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>

                <!-- Side Featured Articles (Smaller Cards) -->
                <div class="space-y-4 sm:space-y-6">
                    <Link v-for="item in sideArticles" :key="item.id" :href="`/blog/${item.slug}`"
                          class="featured-card group flex bg-white rounded-xl overflow-hidden shadow-lg border border-gray-100 hover:border-cyan-200 hover:shadow-xl transition-all duration-300">
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
                                <span class="px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-bold bg-cyan-100 text-cyan-700">
                                    {{ item.category_label || 'Artikel' }}
                                </span>
                            </div>
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-cyan-600 transition-colors mb-1.5 leading-tight">
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
