<script setup>
/**
 * DetailGallery.vue - Premium Gallery with Lightbox
 * Enhanced hover effects, modern grid, smooth transitions
 */
import { ref } from 'vue';
import { Images, ZoomIn, X, ChevronLeft, ChevronRight, Download } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

const lightboxOpen = ref(false);
const currentIndex = ref(0);

const openLightbox = (idx) => { currentIndex.value = idx; lightboxOpen.value = true; document.body.style.overflow = 'hidden'; };
const closeLightbox = () => { lightboxOpen.value = false; document.body.style.overflow = ''; };
const next = () => { currentIndex.value = currentIndex.value < props.destination.images.length - 1 ? currentIndex.value + 1 : 0; };
const prev = () => { currentIndex.value = currentIndex.value > 0 ? currentIndex.value - 1 : props.destination.images.length - 1; };

if (typeof window !== 'undefined') {
    window.addEventListener('keydown', (e) => {
        if (!lightboxOpen.value) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') next();
        if (e.key === 'ArrowLeft') prev();
    });
}
</script>

<template>
    <div v-if="destination.images?.length > 0" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <Images class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Galeri Foto</h2>
                    <p class="text-[10px] text-gray-500">{{ destination.images.length }} foto tersedia</p>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="p-3">
            <div class="grid grid-cols-4 gap-2 auto-rows-[80px] md:auto-rows-[110px]">
                <div v-for="(img, idx) in destination.images.slice(0, 8)" :key="img.id || idx" :class="['relative overflow-hidden group cursor-pointer rounded-xl transition-all duration-300 hover:shadow-lg', idx === 0 ? 'col-span-2 row-span-2' : '']" @click="openLightbox(idx)">
                    <img :src="img.url || img.image_url" :alt="destination.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                    
                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform duration-300">
                            <ZoomIn class="w-5 h-5 text-white" />
                        </div>
                    </div>
                    
                    <!-- More Photos Badge -->
                    <div v-if="idx === 7 && destination.images.length > 8" class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center">
                        <span class="text-white text-lg font-bold">+{{ destination.images.length - 8 }}</span>
                        <span class="text-white/70 text-[10px]">foto lainnya</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Lightbox -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-xl flex items-center justify-center" @click.self="closeLightbox">
                    <!-- Controls -->
                    <button @click="closeLightbox" class="absolute top-4 right-4 z-10 w-11 h-11 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md flex items-center justify-center text-white transition-all">
                        <X class="w-5 h-5" />
                    </button>
                    
                    <button @click="prev" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md flex items-center justify-center text-white transition-all">
                        <ChevronLeft class="w-6 h-6" />
                    </button>
                    
                    <!-- Image -->
                    <div class="max-w-5xl max-h-[85vh] px-16">
                        <img :src="destination.images[currentIndex]?.url || destination.images[currentIndex]?.image_url" :alt="destination.name" class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl">
                    </div>
                    
                    <button @click="next" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md flex items-center justify-center text-white transition-all">
                        <ChevronRight class="w-6 h-6" />
                    </button>
                    
                    <!-- Counter -->
                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md text-white text-sm font-medium">
                        {{ currentIndex + 1 }} / {{ destination.images.length }}
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
