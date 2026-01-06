<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { BookOpen, ArrowRight, Calendar, Clock, User, Sparkles } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ articles: { type: Array, default: () => [] } });
const sectionRef = ref(null);
let ctx;

const getCategoryColor = (c) => ({
    primary: 'from-emerald-500 to-teal-500',
    emerald: 'from-emerald-500 to-teal-500',
    amber: 'from-amber-500 to-orange-500',
    cyan: 'from-cyan-500 to-blue-500',
    rose: 'from-rose-500 to-pink-500',
    blue: 'from-blue-500 to-indigo-500'
}[c] || 'from-emerald-500 to-teal-500');

onMounted(() => {
    ctx = gsap.context(() => {
        // Header entrance - stays visible until section leaves
        gsap.fromTo('.blog-header > *', 
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
        
        // Articles with stagger
        gsap.fromTo('.blog-article', 
            { opacity: 0, y: 40, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.6, stagger: 0.1, ease: 'back.out(1.2)', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
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
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-emerald-50/20 to-white overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-emerald-200/25 to-teal-200/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-56 h-56 bg-gradient-to-tr from-cyan-200/20 to-blue-200/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="blog-header flex flex-col md:flex-row items-start md:items-end justify-between mb-10 gap-4">
                <div class="max-w-md">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                        <BookOpen class="w-3.5 h-3.5" />
                        <span class="text-[11px] font-semibold tracking-wide">Blog & Artikel</span>
                        <Sparkles class="w-3 h-3 text-emerald-500" />
                    </div>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-2">
                        Jelajahi 
                        <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                            Inspirasi Wisata
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500">Tips perjalanan dan cerita petualangan menarik</p>
                </div>
                <Link href="/blog" class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-emerald-600 font-semibold text-sm hover:bg-emerald-50 hover:shadow-sm transition-all duration-300 group">
                    Lihat Semua
                    <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>

            <!-- Articles Grid -->
            <div v-if="articles && articles.length > 0" class="blog-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">
                <Link 
                    v-for="a in articles" 
                    :key="a.id" 
                    :href="`/blog/${a.slug}`" 
                    class="blog-article group block"
                >
                    <div class="relative bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                        <!-- Image Container -->
                        <div class="relative h-40 sm:h-44 overflow-hidden">
                            <img 
                                :src="a.image_url" 
                                :alt="a.title" 
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            
                            <!-- Category Badge -->
                            <div class="absolute bottom-3 left-3">
                                <span :class="['inline-block px-2.5 py-1 rounded-lg text-[10px] font-semibold text-white bg-gradient-to-r shadow-lg', getCategoryColor(a.category_color)]">
                                    {{ a.category_label }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <!-- Meta Info -->
                            <div class="flex items-center gap-3 text-gray-400 text-[10px] mb-2">
                                <span class="flex items-center gap-1">
                                    <Calendar class="w-3 h-3" />
                                    {{ a.created_at }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Clock class="w-3 h-3" />
                                    {{ a.read_time || '5 min' }}
                                </span>
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 mb-2">
                                {{ a.title }}
                            </h3>
                            
                            <!-- Excerpt -->
                            <p class="text-gray-500 text-xs line-clamp-2 leading-relaxed mb-3">
                                {{ a.excerpt }}
                            </p>
                            
                            <!-- Author & CTA -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center ring-2 ring-white shadow-sm">
                                        <User class="w-3.5 h-3.5 text-emerald-600" />
                                    </div>
                                    <span class="text-[11px] text-gray-600 font-medium">{{ a.author || 'Admin' }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-emerald-600 font-semibold text-[11px] group-hover:gap-2 transition-all">
                                    Baca
                                    <ArrowRight class="w-3 h-3" />
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-4">
                    <BookOpen class="w-7 h-7 text-gray-400" />
                </div>
                <p class="text-gray-500 text-sm">Belum ada artikel tersedia</p>
            </div>

            <!-- Mobile CTA -->
            <div class="mt-8 text-center md:hidden">
                <Link href="/blog" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-sm shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                    Lihat Semua Artikel
                    <ArrowRight class="w-4 h-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
