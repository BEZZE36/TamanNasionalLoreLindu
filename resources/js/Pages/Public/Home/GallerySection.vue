<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Camera, MapPin, ArrowRight, Images, Sparkles, ZoomIn } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ galleries: { type: Array, default: () => [] } });
const sectionRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        // Header entrance - stays visible until section leaves
        gsap.fromTo('.gallery-header > *', 
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
        
        // Gallery items
        gsap.fromTo('.gallery-item', 
            { opacity: 0, scale: 0.9, y: 30 }, 
            { opacity: 1, scale: 1, y: 0, duration: 0.6, stagger: 0.06, ease: 'back.out(1.2)', 
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
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-gray-50 via-white to-gray-50 overflow-hidden">
        <!-- Background - Responsive -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 left-0 w-40 sm:w-52 lg:w-64 h-40 sm:h-52 lg:h-64 bg-gradient-to-br from-emerald-200/25 to-teal-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute bottom-1/4 right-0 w-36 sm:w-44 lg:w-56 h-36 sm:h-44 lg:h-56 bg-gradient-to-tl from-cyan-200/20 to-blue-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header - Responsive -->
            <div class="gallery-header flex flex-col md:flex-row justify-between items-start md:items-end mb-6 sm:mb-8 lg:mb-10 gap-3 sm:gap-4">
                <div class="max-w-md">
                    <div class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-2 sm:mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                        <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                    </span>
                        <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Galeri Foto</span>
                        <Camera class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                        Momen Indah di 
                        <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                            Taman Nasional
                        </span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500">Abadikan kenangan dari keindahan alam Lore Lindu</p>
                </div>
                <Link href="/gallery" class="hidden md:inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-emerald-600 font-semibold text-xs sm:text-sm hover:bg-emerald-50 hover:shadow-sm transition-all duration-300 group">
                    Lihat Semua
                    <ArrowRight class="w-3.5 h-3.5 sm:w-4 sm:h-4 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>

            <!-- Gallery Grid - Responsive -->
            <div v-if="galleries && galleries.length > 0" class="gallery-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
                <Link 
                    v-for="(gallery, i) in galleries.slice(0, 8)" 
                    :key="gallery.id" 
                    :href="`/gallery/${gallery.slug}`" 
                    :class="[
                        'gallery-item group relative rounded-xl sm:rounded-2xl overflow-hidden shadow-md sm:shadow-lg hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 transition-all duration-500', 
                        i === 0 ? 'col-span-2 row-span-2' : ''
                    ]"
                >
                    <div :class="['relative overflow-hidden', i === 0 ? 'aspect-square' : 'aspect-[4/3]']">
                        <!-- Image -->
                        <img 
                            :src="gallery.image" 
                            :alt="gallery.title" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                            loading="lazy"
                        >
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        
                        <!-- Category Badge - Responsive -->
                        <div class="absolute top-2 sm:top-3 left-2 sm:left-3">
                            <span class="inline-flex items-center gap-0.5 sm:gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md sm:rounded-lg bg-white/15 backdrop-blur-sm border border-white/20 text-white text-[8px] sm:text-[9px] font-medium">
                                <Sparkles class="w-2 h-2 sm:w-2.5 sm:h-2.5" />
                                {{ gallery.category }}
                            </span>
                        </div>
                        
                        <!-- Media Count Badge - Responsive -->
                        <div v-if="gallery.mediaCount > 1" class="absolute top-2 sm:top-3 right-2 sm:right-3">
                            <span class="inline-flex items-center gap-0.5 sm:gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-md sm:rounded-lg bg-black/30 backdrop-blur-sm text-white text-[8px] sm:text-[9px] font-medium">
                                <Images class="w-2 h-2 sm:w-2.5 sm:h-2.5" />
                                {{ gallery.mediaCount }}
                            </span>
                        </div>
                        
                        <!-- Content - Responsive -->
                        <div class="absolute bottom-0 left-0 right-0 p-2 sm:p-3">
                            <h3 :class="[
                                'font-bold text-white line-clamp-2 group-hover:text-emerald-200 transition-colors', 
                                i === 0 ? 'text-xs sm:text-sm md:text-base' : 'text-[10px] sm:text-[11px]'
                            ]">
                                {{ gallery.title }}
                            </h3>
                            
                            <!-- Location (Featured only) - Responsive -->
                            <p v-if="gallery.location && i === 0" class="flex items-center gap-0.5 sm:gap-1 text-gray-300 text-[9px] sm:text-[10px] mt-1 sm:mt-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <MapPin class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                <span class="truncate">{{ gallery.location }}</span>
                            </p>
                        </div>
                        
                        <!-- Zoom Icon - Responsive -->
                        <div class="absolute bottom-2 sm:bottom-3 right-2 sm:right-3 w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300">
                            <ZoomIn class="w-3 h-3 sm:w-4 sm:h-4 text-white" />
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State - Responsive -->
            <div v-else class="text-center py-8 sm:py-12">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <Camera class="w-5 h-5 sm:w-7 sm:h-7 text-gray-400" />
                </div>
                <p class="text-gray-500 text-xs sm:text-sm">Belum ada galeri tersedia</p>
            </div>

            <!-- Mobile CTA - Responsive -->
            <div class="mt-6 sm:mt-8 text-center md:hidden">
                <Link href="/gallery" class="inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-xs sm:text-sm shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                    Lihat Semua Galeri
                    <ArrowRight class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </Link>
            </div>
        </div>
    </section>
</template>
