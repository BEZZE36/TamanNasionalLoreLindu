<script setup>
/**
 * DetailRelated.vue - Related Destinations using IndexCard Style
 * Uses same card design as Index page for consistency
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Compass, ArrowRight, MapPin, Star, Heart, Clock, TrendingUp } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    destination: { type: Object, required: true },
    relatedDestinations: { type: Array, default: () => [] }
});

const sectionRef = ref(null);
let ctx;

const formatPrice = (dest) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(dest?.prices?.[0]?.price || dest?.adult_price || 0);

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.related-header', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out', scrollTrigger: { trigger: sectionRef.value, start: 'top 85%' } });
        gsap.fromTo('.related-card', { opacity: 0, y: 30, scale: 0.95 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.5)', scrollTrigger: { trigger: sectionRef.value, start: 'top 80%' } });
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section v-if="relatedDestinations?.length > 0" ref="sectionRef" class="py-14 bg-gradient-to-b from-white via-gray-50/80 to-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="related-header flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                        <Compass class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900">Destinasi Lainnya</h2>
                        <p class="text-[11px] text-gray-500">Tempat menarik lain yang mungkin kamu suka</p>
                    </div>
                </div>
                <Link href="/destinations" class="hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-full bg-gray-100 hover:bg-emerald-100 text-gray-700 hover:text-emerald-700 text-[11px] font-semibold transition-all group">
                    Lihat Semua
                    <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" />
                </Link>
            </div>

            <!-- Grid - IndexCard Style -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <Link v-for="related in relatedDestinations.slice(0, 4)" :key="related.id" :href="`/destinations/${related.slug}`" class="related-card group block">
                    <div class="relative h-[340px] rounded-2xl overflow-hidden bg-white shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100/80 hover:-translate-y-1">
                        <!-- Image Section -->
                        <div class="relative h-[55%] overflow-hidden">
                            <img :src="related.primary_image_url || '/images/placeholder-no-image.svg'" :alt="related.name" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                            
                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-transparent"></div>
                            
                            <!-- Top Badges -->
                            <div class="absolute top-2.5 left-2.5 flex flex-wrap items-center gap-1.5 z-10">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-black/40 backdrop-blur-md text-white text-[9px] font-semibold border border-white/10">
                                    <MapPin class="w-2.5 h-2.5" />
                                    {{ related.category?.name || 'Wisata' }}
                                </span>
                                <span v-if="related.is_featured" class="inline-flex items-center gap-0.5 px-2 py-1 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[8px] font-bold uppercase shadow-lg">
                                    <TrendingUp class="w-2.5 h-2.5" />
                                    Populer
                                </span>
                            </div>

                            <!-- Rating -->
                            <div class="absolute top-2.5 right-2.5 z-10">
                                <div class="flex items-center gap-1 px-2 py-1 rounded-lg bg-white/95 shadow-lg text-[10px] font-bold text-gray-900">
                                    <Star class="w-3 h-3 text-amber-500 fill-amber-500" />
                                    {{ related.rating || '4.8' }}
                                </div>
                            </div>

                            <!-- Title Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <h3 class="text-sm font-bold text-white line-clamp-1 mb-1 group-hover:text-emerald-200 transition-colors">{{ related.name }}</h3>
                                <div class="flex items-center gap-2 text-white/70 text-[9px]">
                                    <span class="flex items-center gap-1"><MapPin class="w-2.5 h-2.5" />{{ related.city }}</span>
                                    <span v-if="related.operating_hours" class="flex items-center gap-1"><Clock class="w-2.5 h-2.5" />{{ related.operating_hours }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-3 h-[45%] flex flex-col">
                            <p class="flex-1 text-gray-600 text-[10px] leading-relaxed line-clamp-3">{{ related.short_description || 'Destinasi wisata alam yang menakjubkan.' }}</p>
                            
                            <div class="flex items-center justify-between pt-2.5 mt-auto border-t border-gray-100">
                                <div>
                                    <span class="text-[8px] text-gray-400 uppercase tracking-wider font-medium block">Mulai dari</span>
                                    <p class="text-xs font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ formatPrice(related) }}</p>
                                </div>
                                <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[9px] font-semibold shadow-lg group-hover:shadow-emerald-500/30">
                                    <span>Jelajahi</span>
                                    <ArrowRight class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" />
                                </div>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Mobile CTA -->
            <div class="sm:hidden flex justify-center mt-6">
                <Link href="/destinations" class="flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[11px] font-bold shadow-lg group">
                    Lihat Semua Destinasi
                    <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" />
                </Link>
            </div>
        </div>
    </section>
</template>
