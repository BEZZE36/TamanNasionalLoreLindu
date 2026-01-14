<script setup>
/**
 * Gallery Show Page - Premium Detail View
 * Features: GSAP animations, swipe scroll thumbnails, lightbox, like/wishlist, related galleries
 * Design: Glassmorphism, responsive, premium aesthetics
 */
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { usePage, Link, Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import CommentSection from '@/Components/CommentSection.vue';
import { 
    ChevronRight, ChevronLeft, ArrowLeft, Heart, Eye, Share2, Play, Download, 
    MapPin, Calendar, Camera, X, ZoomIn, ZoomOut, Maximize2, Images, 
    Facebook, MessageCircle, Copy, Check, Bookmark, ExternalLink
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    gallery: Object,
    related: Array,
    isLiked: Boolean,
    isWishlisted: { type: Boolean, default: false },
    comments: { type: Array, default: () => [] }
});

const page = usePage();
const user = page.props.auth?.user;
const heroRef = ref(null);
const thumbnailContainerRef = ref(null);
const relatedContainerRef = ref(null);
const thumbnailProgress = ref(0);
const relatedProgress = ref(0);
let ctx;
let rafId = null;

// State
const liked = ref(props.isLiked);
const wishlisted = ref(props.isWishlisted);
const likesCount = ref(props.gallery.likes_count || 0);
const currentIndex = ref(0);
const lightboxOpen = ref(false);
const copied = ref(false);
const isLiking = ref(false);

// Computed
const allMedia = computed(() => {
    const media = [];
    if (props.gallery.cover_image_url) {
        media.push({ type: 'image', url: props.gallery.cover_image_url });
    }
    if (props.gallery.media) {
        props.gallery.media.forEach(m => media.push(m));
    }
    return media.length ? media : [{ type: 'image', url: '/images/placeholder-no-image.svg' }];
});

const currentMedia = computed(() => allMedia.value[currentIndex.value]);
const shareUrl = computed(() => typeof window !== 'undefined' ? window.location.href : '');

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('id-ID', { 
        day: 'numeric', month: 'long', year: 'numeric' 
    });
};

const formatCount = (num) => {
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num?.toString() || '0';
};

// Scroll handlers
const handleThumbnailScroll = () => {
    if (rafId) return;
    rafId = requestAnimationFrame(() => {
        if (!thumbnailContainerRef.value) return;
        const container = thumbnailContainerRef.value;
        const scrollWidth = container.scrollWidth - container.clientWidth;
        if (scrollWidth > 0) {
            thumbnailProgress.value = (container.scrollLeft / scrollWidth) * 100;
        }
        rafId = null;
    });
};

const handleRelatedScroll = () => {
    if (!relatedContainerRef.value) return;
    const container = relatedContainerRef.value;
    const scrollWidth = container.scrollWidth - container.clientWidth;
    if (scrollWidth > 0) {
        relatedProgress.value = (container.scrollLeft / scrollWidth) * 100;
    }
};

// Actions
const toggleLike = async () => {
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
        console.error('Failed to like', e); 
    } finally {
        isLiking.value = false;
    }
};

const toggleWishlist = async () => {
    if (!user) { window.location.href = '/login'; return; }
    
    try {
        await fetch(`/gallery/${props.gallery.slug}/wishlist`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 
                'Content-Type': 'application/json' 
            }
        });
        wishlisted.value = !wishlisted.value;
    } catch (e) { 
        console.error('Failed to wishlist', e); 
    }
};

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(shareUrl.value);
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    } catch (e) {
        console.error('Copy failed:', e);
    }
};

const shareToWhatsApp = () => {
    window.open(`https://wa.me/?text=${encodeURIComponent(props.gallery.title + ' - ' + shareUrl.value)}`, '_blank');
};

const shareToFacebook = () => {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`, '_blank');
};

const openLightbox = (index) => {
    currentIndex.value = index;
    lightboxOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    document.body.style.overflow = '';
};

const nextMedia = () => {
    currentIndex.value = (currentIndex.value + 1) % allMedia.value.length;
};

const prevMedia = () => {
    currentIndex.value = (currentIndex.value - 1 + allMedia.value.length) % allMedia.value.length;
};

const handleKeydown = (e) => {
    if (!lightboxOpen.value) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextMedia();
    if (e.key === 'ArrowLeft') prevMedia();
};

onMounted(async () => {
    await nextTick();
    
    if (thumbnailContainerRef.value) {
        thumbnailContainerRef.value.addEventListener('scroll', handleThumbnailScroll, { passive: true });
    }
    if (relatedContainerRef.value) {
        relatedContainerRef.value.addEventListener('scroll', handleRelatedScroll, { passive: true });
    }
    
    window.addEventListener('keydown', handleKeydown);
    
    if (heroRef.value) {
        ctx = gsap.context(() => {
            const tl = gsap.timeline({ delay: 0.2 });
            
            tl.fromTo('.hero-breadcrumb', 
                { opacity: 0, y: -15 }, 
                { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }
            );
            
            tl.fromTo('.hero-badges > *', 
                { opacity: 0, scale: 0.7, y: 10 }, 
                { opacity: 1, scale: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'back.out(3)' }, 
                '-=0.2'
            );
            
            tl.fromTo('.hero-title', 
                { opacity: 0, y: 25, filter: 'blur(8px)' }, 
                { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.5, ease: 'power3.out' }, 
                '-=0.2'
            );
            
            tl.fromTo('.hero-meta > *', 
                { opacity: 0, x: -10 }, 
                { opacity: 1, x: 0, duration: 0.3, stagger: 0.06 }, 
                '-=0.2'
            );
            
            tl.fromTo('.hero-actions > *', 
                { opacity: 0, scale: 0.8 }, 
                { opacity: 1, scale: 1, duration: 0.3, stagger: 0.06, ease: 'back.out(2)' }, 
                '-=0.1'
            );
            
            // Ken Burns effect on background
            gsap.to('.hero-bg-image', {
                scale: 1.1,
                duration: 15,
                ease: 'none',
                repeat: -1,
                yoyo: true
            });
        }, heroRef.value);
    }
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
    if (rafId) cancelAnimationFrame(rafId);
    if (thumbnailContainerRef.value) {
        thumbnailContainerRef.value.removeEventListener('scroll', handleThumbnailScroll);
    }
    if (relatedContainerRef.value) {
        relatedContainerRef.value.removeEventListener('scroll', handleRelatedScroll);
    }
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div>
        <Head>
            <title>{{ gallery.title }} - Galeri TNLL</title>
            <meta name="description" :content="gallery.description?.substring(0, 160)">
        </Head>

        <!-- Premium Hero -->
        <section ref="heroRef" class="relative min-h-[50vh] sm:min-h-[55vh] overflow-hidden">
            <!-- Background Image with Ken Burns -->
            <div class="absolute inset-0">
                <img 
                    :src="gallery.cover_image_url || '/images/placeholder-no-image.svg'" 
                    :alt="gallery.title"
                    class="hero-bg-image w-full h-full object-cover"
                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                >
                <!-- Gradient Overlays -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/30"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-violet-900/30 to-transparent"></div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-[15%] right-[10%] w-20 h-20 border border-white/5 rounded-full animate-pulse-slow"></div>
                <div class="absolute bottom-[25%] left-[5%] w-14 h-14 border border-violet-500/10 rounded-2xl rotate-12 animate-float"></div>
            </div>

            <!-- Wave Transition -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-16 sm:h-20" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
                <path d="M0,50L60,45C120,40,240,30,360,35C480,40,600,60,720,65C840,70,960,60,1080,50C1200,40,1320,30,1380,25L1440,20L1440,100L0,100Z" fill="white"/>
            </svg>

            <!-- Content -->
            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 sm:pt-28 pb-20 sm:pb-24">
                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb flex items-center gap-1.5 text-[10px] sm:text-[11px] text-white/60 mb-4 sm:mb-5 font-medium flex-wrap">
                    <Link href="/" class="hover:text-white transition-colors">Beranda</Link>
                    <ChevronRight class="w-3 h-3" />
                    <Link href="/gallery" class="hover:text-white transition-colors">Galeri</Link>
                    <ChevronRight class="w-3 h-3" />
                    <span class="text-white/90 truncate max-w-[200px]">{{ gallery.title }}</span>
                </nav>

                <div class="flex flex-col lg:flex-row gap-5 lg:items-end lg:justify-between">
                    <div class="flex-1 max-w-2xl">
                        <!-- Badges -->
                        <div class="hero-badges flex flex-wrap items-center gap-2 mb-3 sm:mb-4">
                            <span v-if="gallery.category" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white text-[10px] font-semibold">
                                <Camera class="w-3 h-3" />
                                {{ gallery.category.name }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/95 text-gray-900 text-[10px] font-bold shadow-lg">
                                <Images class="w-3.5 h-3.5 text-violet-500" />
                                {{ allMedia.length }} Media
                            </span>
                        </div>

                        <!-- Title -->
                        <h1 class="hero-title text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-3 sm:mb-4">
                            {{ gallery.title }}
                        </h1>

                        <!-- Meta Info -->
                        <div class="hero-meta flex flex-wrap items-center gap-2 text-white/70 text-[10px] sm:text-[11px]">
                            <span class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                                <Eye class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-400" />
                                {{ formatCount(gallery.view_count || 0) }} views
                            </span>
                            <span class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                                <Heart class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-red-400" />
                                {{ formatCount(likesCount) }} likes
                            </span>
                            <span v-if="gallery.destination" class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                                <MapPin class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-cyan-400" />
                                {{ gallery.destination.name }}
                            </span>
                            <span v-if="gallery.created_at" class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                                <Calendar class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-400" />
                                {{ formatDate(gallery.created_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="hero-actions flex flex-wrap items-center gap-2">
                        <button 
                            @click="toggleWishlist" 
                            :class="['w-10 h-10 sm:w-11 sm:h-11 rounded-xl backdrop-blur-xl border flex items-center justify-center transition-all duration-300 group', wishlisted ? 'bg-amber-500 border-amber-500 text-white' : 'bg-white/10 border-white/20 hover:bg-white/25 text-white']"
                        >
                            <Bookmark :class="['w-4 h-4 sm:w-4.5 sm:h-4.5 group-hover:scale-110 transition-transform', wishlisted ? 'fill-current' : '']" />
                        </button>
                        <button 
                            @click="toggleLike" 
                            :class="['w-10 h-10 sm:w-11 sm:h-11 rounded-xl backdrop-blur-xl border flex items-center justify-center transition-all duration-300 group', liked ? 'bg-red-500 border-red-500 text-white' : 'bg-white/10 border-white/20 hover:bg-white/25 text-white', isLiking ? 'animate-pulse' : '']"
                        >
                            <Heart :class="['w-4 h-4 sm:w-4.5 sm:h-4.5 group-hover:scale-110 transition-transform', liked ? 'fill-current' : '']" />
                        </button>
                        <a 
                            v-if="currentMedia?.url" 
                            :href="currentMedia.url" 
                            download 
                            target="_blank" 
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/10 backdrop-blur-xl border border-white/20 hover:bg-white/25 flex items-center justify-center text-white transition-all duration-300 group"
                        >
                            <Download class="w-4 h-4 sm:w-4.5 sm:h-4.5 group-hover:scale-110 transition-transform" />
                        </a>
                        <Link href="/gallery" class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-400 hover:to-purple-500 text-white font-bold text-[11px] sm:text-xs flex items-center gap-2 shadow-xl shadow-violet-900/30 hover:shadow-violet-500/40 transition-all duration-300">
                            <ArrowLeft class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                            <span>Kembali</span>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section class="py-8 sm:py-12 md:py-16 bg-gradient-to-b from-white to-gray-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6 sm:space-y-8">
                        <!-- Main Media Viewer -->
                        <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                            <!-- Main Display -->
                            <div class="aspect-video bg-gray-900 relative group cursor-pointer" @click="openLightbox(currentIndex)">
                                <template v-if="currentMedia.type === 'video'">
                                    <video :src="currentMedia.url" controls class="w-full h-full object-contain"></video>
                                </template>
                                <template v-else>
                                    <img 
                                        :src="currentMedia.url" 
                                        :alt="gallery.title" 
                                        class="w-full h-full object-contain"
                                        @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                    >
                                </template>
                                
                                <!-- Zoom Overlay -->
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all">
                                        <ZoomIn class="w-6 h-6 text-white" />
                                    </div>
                                </div>

                                <!-- Navigation Arrows -->
                                <button 
                                    v-if="allMedia.length > 1"
                                    @click.stop="prevMedia" 
                                    class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/40 backdrop-blur-sm text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black/60"
                                >
                                    <ChevronLeft class="w-5 h-5" />
                                </button>
                                <button 
                                    v-if="allMedia.length > 1"
                                    @click.stop="nextMedia" 
                                    class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/40 backdrop-blur-sm text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black/60"
                                >
                                    <ChevronRight class="w-5 h-5" />
                                </button>

                                <!-- Media Counter -->
                                <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-black/50 backdrop-blur-sm text-white text-[10px] font-medium">
                                    {{ currentIndex + 1 }} / {{ allMedia.length }}
                                </div>
                            </div>

                            <!-- Thumbnails with Swipe Scroll -->
                            <div v-if="allMedia.length > 1" class="p-3 sm:p-4 border-t border-gray-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-semibold text-gray-700">Media ({{ allMedia.length }})</span>
                                    <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                        Swipe untuk melihat
                                        <ChevronRight class="w-3 h-3" />
                                    </span>
                                </div>
                                
                                <div 
                                    ref="thumbnailContainerRef"
                                    class="flex gap-2 overflow-x-auto pb-2 snap-x snap-mandatory scrollbar-hide"
                                    style="-webkit-overflow-scrolling: touch;"
                                >
                                    <button 
                                        v-for="(m, i) in allMedia" 
                                        :key="i" 
                                        @click="currentIndex = i"
                                        :class="['flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden border-2 transition-all snap-start', i === currentIndex ? 'border-violet-500 ring-2 ring-violet-500/30' : 'border-transparent hover:border-violet-300']"
                                    >
                                        <template v-if="m.type === 'video'">
                                            <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                                <Play class="w-5 h-5 text-white" />
                                            </div>
                                        </template>
                                        <template v-else>
                                            <img :src="m.url" class="w-full h-full object-cover" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                                        </template>
                                    </button>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-2 relative h-1 bg-gray-200 rounded-full overflow-hidden">
                                    <div 
                                        class="absolute top-0 left-0 h-full bg-gradient-to-r from-violet-500 to-purple-500 rounded-full transition-[width] duration-100"
                                        :style="{ width: `${Math.max(thumbnailProgress, 10)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="gallery.description" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 p-5 sm:p-6">
                            <h2 class="text-sm sm:text-base font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                    <Camera class="w-3.5 h-3.5 text-white" />
                                </div>
                                Deskripsi
                            </h2>
                            <div class="prose prose-sm max-w-none text-gray-600 text-xs sm:text-sm leading-relaxed" v-html="gallery.description?.replace(/\n/g, '<br>')"></div>
                        </div>

                        <!-- Comments Section -->
                        <CommentSection 
                            :item-id="gallery.id" 
                            item-type="gallery" 
                            :comments="comments"
                            color-scheme="purple"
                        />
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-5">
                        <!-- Action Card -->
                        <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-24">
                            <!-- Header -->
                            <div class="px-4 py-4 bg-gradient-to-r from-violet-500 to-purple-600">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                        <Heart class="w-4 h-4 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-white font-bold text-xs sm:text-sm">Aksi</h3>
                                        <p class="text-white/70 text-[9px]">Suka, simpan, atau bagikan</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="p-4 space-y-3">
                                <!-- Like & Wishlist -->
                                <div class="flex gap-2">
                                    <button 
                                        @click="toggleLike" 
                                        :class="['flex-1 py-2.5 rounded-xl font-bold text-[11px] flex items-center justify-center gap-2 transition-all', liked ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                                    >
                                        <Heart :class="['w-4 h-4', liked ? 'fill-current' : '']" />
                                        {{ liked ? 'Disukai' : 'Suka' }}
                                    </button>
                                    <button 
                                        @click="toggleWishlist" 
                                        :class="['flex-1 py-2.5 rounded-xl font-bold text-[11px] flex items-center justify-center gap-2 transition-all', wishlisted ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                                    >
                                        <Bookmark :class="['w-4 h-4', wishlisted ? 'fill-current' : '']" />
                                        {{ wishlisted ? 'Disimpan' : 'Simpan' }}
                                    </button>
                                </div>

                                <!-- Download -->
                                <a 
                                    v-if="currentMedia?.url"
                                    :href="currentMedia.url" 
                                    download 
                                    target="_blank" 
                                    class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 font-bold text-[11px] flex items-center justify-center gap-2 transition-all"
                                >
                                    <Download class="w-4 h-4" />
                                    Download Media
                                </a>

                                <!-- Share Section -->
                                <div class="pt-3 border-t border-gray-100">
                                    <h4 class="font-bold text-gray-900 text-[11px] mb-2.5 flex items-center gap-1.5">
                                        <Share2 class="w-3.5 h-3.5 text-violet-500" />
                                        Bagikan
                                    </h4>
                                    <div class="flex gap-2">
                                        <button @click="shareToWhatsApp" class="flex-1 py-2 bg-green-500 text-white rounded-xl text-[10px] font-bold hover:bg-green-600 transition-colors flex items-center justify-center gap-1.5">
                                            <MessageCircle class="w-3.5 h-3.5" />
                                            WhatsApp
                                        </button>
                                        <button @click="shareToFacebook" class="flex-1 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-bold hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5">
                                            <Facebook class="w-3.5 h-3.5" />
                                            Facebook
                                        </button>
                                    </div>
                                    <button @click="copyLink" class="w-full mt-2 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-[10px] font-medium flex items-center justify-center gap-1.5 transition-all">
                                        <component :is="copied ? Check : Copy" class="w-3.5 h-3.5" />
                                        {{ copied ? 'Link Disalin!' : 'Salin Link' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Info Card -->
                        <div v-if="gallery.destination" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 p-4">
                            <h4 class="font-bold text-gray-900 text-[11px] mb-3 flex items-center gap-1.5">
                                <MapPin class="w-3.5 h-3.5 text-violet-500" />
                                Lokasi
                            </h4>
                            <Link :href="`/destinations/${gallery.destination.slug}`" class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-violet-50 border border-gray-100 hover:border-violet-200 transition-all group">
                                <img 
                                    :src="gallery.destination.primary_image_url || '/images/placeholder-no-image.svg'" 
                                    :alt="gallery.destination.name"
                                    class="w-12 h-12 rounded-lg object-cover"
                                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                >
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-900 group-hover:text-violet-600 transition-colors truncate">{{ gallery.destination.name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ gallery.destination.city }}</p>
                                </div>
                                <ExternalLink class="w-4 h-4 text-gray-400 group-hover:text-violet-500 transition-colors" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Related Galleries with Swipe Scroll -->
                <div v-if="related?.length > 0" class="mt-10 sm:mt-14">
                    <div class="flex items-center justify-between mb-4 sm:mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/25">
                                <Images class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-gray-900">Galeri Terkait</h2>
                                <p class="text-[10px] sm:text-[11px] text-gray-500">Koleksi lainnya yang mungkin Anda suka</p>
                            </div>
                        </div>
                        <span class="text-[10px] text-gray-400 flex items-center gap-1">
                            Swipe untuk melihat
                            <ChevronRight class="w-3 h-3" />
                        </span>
                    </div>

                    <div 
                        ref="relatedContainerRef"
                        class="flex gap-3 sm:gap-4 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide"
                        style="-webkit-overflow-scrolling: touch;"
                    >
                        <Link 
                            v-for="item in related" 
                            :key="item.id" 
                            :href="`/gallery/${item.slug}`" 
                            class="flex-shrink-0 snap-start group"
                        >
                            <div class="relative w-[200px] sm:w-[240px] aspect-[4/3] rounded-xl sm:rounded-2xl overflow-hidden shadow-md group-hover:shadow-xl border border-gray-100 group-hover:border-violet-200 transition-all duration-300 group-hover:-translate-y-1">
                                <img 
                                    :src="item.thumbnail_url || item.cover_image_url || '/images/placeholder-no-image.svg'" 
                                    :alt="item.title" 
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                
                                <!-- Category Badge -->
                                <div v-if="item.category" class="absolute top-2 left-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/15 backdrop-blur-sm border border-white/20 text-white text-[8px] font-medium">
                                        {{ item.category.name }}
                                    </span>
                                </div>

                                <!-- Content -->
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <h3 class="text-[11px] sm:text-xs font-bold text-white group-hover:text-violet-200 transition-colors line-clamp-2 leading-tight mb-1.5">
                                        {{ item.title }}
                                    </h3>
                                    <div class="flex items-center gap-2 text-white/70 text-[9px]">
                                        <span class="flex items-center gap-0.5">
                                            <Eye class="w-2.5 h-2.5" />
                                            {{ formatCount(item.view_count || 0) }}
                                        </span>
                                        <span class="flex items-center gap-0.5">
                                            <Heart class="w-2.5 h-2.5" />
                                            {{ formatCount(item.likes_count || 0) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Zoom Icon -->
                                <div class="absolute bottom-2.5 right-2.5 w-6 h-6 rounded-full bg-white/15 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300">
                                    <ZoomIn class="w-3 h-3 text-white" />
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Progress Bar -->
                    <div class="flex items-center justify-between mt-3">
                        <div class="relative w-24 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="absolute top-0 left-0 h-full bg-gradient-to-r from-violet-500 to-purple-500 rounded-full transition-[width] duration-100"
                                :style="{ width: `${Math.max(relatedProgress, 10)}%` }"
                            ></div>
                        </div>
                        <Link href="/gallery" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gradient-to-r from-violet-500 to-purple-600 text-white font-medium text-[10px] sm:text-[11px] shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            Lihat Semua
                            <ChevronRight class="w-3 h-3" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lightbox -->
        <Teleport to="body">
            <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center" @click="closeLightbox">
                <!-- Close Button -->
                <button @click="closeLightbox" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm text-white flex items-center justify-center hover:bg-white/20 transition-all z-10">
                    <X class="w-5 h-5" />
                </button>

                <!-- Counter -->
                <div class="absolute top-4 left-4 px-3 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm text-white text-xs font-medium">
                    {{ currentIndex + 1 }} / {{ allMedia.length }}
                </div>

                <!-- Main Media -->
                <div class="max-w-5xl max-h-[85vh] mx-4" @click.stop>
                    <template v-if="currentMedia.type === 'video'">
                        <video :src="currentMedia.url" controls class="max-h-[85vh] mx-auto rounded-lg"></video>
                    </template>
                    <template v-else>
                        <img 
                            :src="currentMedia.url" 
                            :alt="gallery.title" 
                            class="max-h-[85vh] mx-auto rounded-lg object-contain"
                            @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                        >
                    </template>
                </div>

                <!-- Navigation -->
                <button 
                    v-if="allMedia.length > 1"
                    @click.stop="prevMedia" 
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 backdrop-blur-sm text-white flex items-center justify-center hover:bg-white/20 transition-all"
                >
                    <ChevronLeft class="w-6 h-6" />
                </button>
                <button 
                    v-if="allMedia.length > 1"
                    @click.stop="nextMedia" 
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 backdrop-blur-sm text-white flex items-center justify-center hover:bg-white/20 transition-all"
                >
                    <ChevronRight class="w-6 h-6" />
                </button>

                <!-- Thumbnails -->
                <div v-if="allMedia.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 px-4 py-2 rounded-xl bg-black/50 backdrop-blur-sm max-w-[90vw] overflow-x-auto scrollbar-hide">
                    <button 
                        v-for="(m, i) in allMedia" 
                        :key="i" 
                        @click.stop="currentIndex = i"
                        :class="['flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden border-2 transition-all', i === currentIndex ? 'border-white' : 'border-transparent opacity-60 hover:opacity-100']"
                    >
                        <template v-if="m.type === 'video'">
                            <div class="w-full h-full bg-gray-700 flex items-center justify-center">
                                <Play class="w-4 h-4 text-white" />
                            </div>
                        </template>
                        <template v-else>
                            <img :src="m.url" class="w-full h-full object-cover">
                        </template>
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

@keyframes pulse-slow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(12deg); }
    50% { transform: translateY(-15px) rotate(12deg); }
}
.animate-float { animation: float 6s ease-in-out infinite; }
</style>
