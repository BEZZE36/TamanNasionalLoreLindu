<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Play, Heart, Eye } from 'lucide-vue-next';

const props = defineProps({
    gallery: { type: Object, required: true },
    index: { type: Number, default: 0 }
});

const isVideo = computed(() => props.gallery.type === 'video' || props.gallery.media?.some(m => m.type === 'video'));
const thumbnail = computed(() => props.gallery.thumbnail_url || props.gallery.cover_image_url || props.gallery.media?.[0]?.url || '/images/placeholder-no-image.svg');
const animationDelay = computed(() => `${props.index * 0.05}s`);
</script>

<template>
    <Link :href="`/gallery/${gallery.slug}`" class="group block" :style="{ animationDelay }">
        <div class="relative aspect-square rounded-2xl overflow-hidden shadow-lg group-hover:shadow-2xl transition-all duration-500 group-hover:-translate-y-2">
            <img :src="thumbnail" :alt="gallery.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                loading="lazy" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
            
            <!-- Video Badge -->
            <div v-if="isVideo" class="absolute top-4 left-4">
                <span class="w-10 h-10 rounded-full bg-violet-500/90 backdrop-blur flex items-center justify-center">
                    <Play class="w-5 h-5 text-white fill-white" />
                </span>
            </div>

            <!-- Category Badge -->
            <div v-if="gallery.category" class="absolute top-4 right-4">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur text-white">
                    {{ gallery.category.name }}
                </span>
            </div>

            <!-- Content -->
            <div class="absolute bottom-0 left-0 right-0 p-5">
                <h3 class="text-lg font-bold text-white mb-2 line-clamp-2 group-hover:text-violet-200 transition-colors">{{ gallery.title }}</h3>
                
                <div class="flex items-center justify-between">
                    <span v-if="gallery.destination" class="text-white/70 text-sm">{{ gallery.destination.name }}</span>
                    <div class="flex items-center gap-3 text-white/70 text-sm">
                        <span class="flex items-center gap-1"><Eye class="w-4 h-4" />{{ gallery.view_count || 0 }}</span>
                        <span class="flex items-center gap-1"><Heart class="w-4 h-4" />{{ gallery.likes_count || 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
a { animation: fade-in-up 0.6s ease-out forwards; opacity: 0; }
@keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); }}
</style>
