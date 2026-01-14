<script setup>
/**
 * GalleryCard.vue - Premium Card with Hover Effects
 * Design: Modern card with animations, likes, views, wishlist
 * Sizing: text-[11px] titles, text-[9px] badges
 */
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Play, Heart, Eye, ZoomIn, MapPin, Images, Bookmark } from 'lucide-vue-next';

const props = defineProps({
    gallery: { type: Object, required: true },
    index: { type: Number, default: 0 }
});

const page = usePage();
const user = page.props.auth?.user;
const isLiking = ref(false);
const liked = ref(props.gallery.is_liked || false);
const likesCount = ref(props.gallery.likes_count || 0);
const isWishlisted = ref(props.gallery.is_wishlisted || false);

const isVideo = computed(() => props.gallery.type === 'video' || props.gallery.media?.some(m => m.type === 'video'));
const thumbnail = computed(() => props.gallery.thumbnail_url || props.gallery.cover_image_url || props.gallery.media?.[0]?.url || '/images/placeholder-no-image.svg');
const mediaCount = computed(() => props.gallery.media_count || props.gallery.mediaCount || props.gallery.media?.length || 1);
const animationDelay = computed(() => `${props.index * 0.05}s`);

const toggleLike = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!user) { window.location.href = '/login'; return; }
    if (isLiking.value) return;
    
    isLiking.value = true;
    try {
        await fetch(`/gallery/${props.gallery.slug}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        });
        liked.value = !liked.value;
        likesCount.value += liked.value ? 1 : -1;
    } catch (e) {
        console.error('Like failed:', e);
    } finally {
        isLiking.value = false;
    }
};

const toggleWishlist = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!user) { window.location.href = '/login'; return; }
    
    try {
        await fetch(`/gallery/${props.gallery.slug}/wishlist`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        });
        isWishlisted.value = !isWishlisted.value;
    } catch (e) {
        console.error('Wishlist failed:', e);
    }
};

const formatCount = (num) => {
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num?.toString() || '0';
};
</script>

<template>
    <Link :href="`/gallery/${gallery.slug}`" class="gallery-card group block" :style="{ animationDelay }">
        <div class="relative aspect-[4/3] rounded-xl sm:rounded-2xl overflow-hidden shadow-md group-hover:shadow-2xl border border-gray-100 group-hover:border-violet-200 transition-all duration-500 group-hover:-translate-y-1.5">
            <!-- Image -->
            <img 
                :src="thumbnail" 
                :alt="gallery.title" 
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                loading="lazy" 
                @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
            >
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
            
            <!-- Top Actions (Like & Wishlist) -->
            <div class="absolute top-2 sm:top-3 left-2 sm:left-3 right-2 sm:right-3 flex items-start justify-between">
                <!-- Left Badges -->
                <div class="flex items-center gap-1.5">
                    <!-- Video Badge -->
                    <span v-if="isVideo" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-violet-500/90 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <Play class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white fill-white" />
                    </span>
                    
                    <!-- Media Count -->
                    <span v-if="mediaCount > 1" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-black/40 backdrop-blur-sm text-white text-[9px] sm:text-[10px] font-medium">
                        <Images class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                        {{ mediaCount }}
                    </span>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <!-- Wishlist Button -->
                    <button 
                        @click="toggleWishlist"
                        :class="['w-7 h-7 sm:w-8 sm:h-8 rounded-lg backdrop-blur-sm flex items-center justify-center transition-all shadow-lg', isWishlisted ? 'bg-amber-500 text-white' : 'bg-white/20 text-white hover:bg-amber-500']"
                    >
                        <Bookmark :class="['w-3.5 h-3.5 sm:w-4 sm:h-4', isWishlisted ? 'fill-current' : '']" />
                    </button>
                    
                    <!-- Like Button -->
                    <button 
                        @click="toggleLike"
                        :class="['w-7 h-7 sm:w-8 sm:h-8 rounded-lg backdrop-blur-sm flex items-center justify-center transition-all shadow-lg', liked ? 'bg-red-500 text-white' : 'bg-white/20 text-white hover:bg-red-500', isLiking ? 'animate-pulse' : '']"
                    >
                        <Heart :class="['w-3.5 h-3.5 sm:w-4 sm:h-4', liked ? 'fill-current' : '']" />
                    </button>
                </div>
            </div>

            <!-- Category Badge -->
            <div v-if="gallery.category" class="absolute top-10 sm:top-12 left-2 sm:left-3">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/15 backdrop-blur-sm border border-white/20 text-white text-[8px] sm:text-[9px] font-medium">
                    {{ gallery.category.name }}
                </span>
            </div>

            <!-- Content -->
            <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                <!-- Title -->
                <h3 class="text-[11px] sm:text-sm font-bold text-white mb-1.5 line-clamp-2 group-hover:text-violet-200 transition-colors leading-tight">
                    {{ gallery.title }}
                </h3>
                
                <!-- Meta Row -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-white/70 text-[9px] sm:text-[10px]">
                        <!-- Location -->
                        <span v-if="gallery.destination" class="flex items-center gap-0.5">
                            <MapPin class="w-2.5 h-2.5" />
                            <span class="truncate max-w-[80px] sm:max-w-[100px]">{{ gallery.destination.name }}</span>
                        </span>
                        <!-- Views -->
                        <span class="flex items-center gap-0.5">
                            <Eye class="w-2.5 h-2.5" />
                            {{ formatCount(gallery.view_count || 0) }}
                        </span>
                        <!-- Likes -->
                        <span class="flex items-center gap-0.5">
                            <Heart class="w-2.5 h-2.5" />
                            {{ formatCount(likesCount) }}
                        </span>
                    </div>

                    <!-- Zoom Indicator -->
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300">
                        <ZoomIn class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white" />
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.gallery-card {
    animation: fade-in-up 0.5s ease-out forwards;
    opacity: 0;
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
