<script setup>
/**
 * Flora Show Page - Premium Detail View
 * Features: GSAP animations, swipe scroll gallery, premium sidebar
 */
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import CommentSection from '@/Components/CommentSection.vue';
import { 
    ChevronRight, ChevronLeft, ArrowLeft, Share2, Leaf, Sparkles,
    MapPin, Eye, ZoomIn, X, Flower2, TreePine, Copy, Check,
    Facebook, MessageCircle, ArrowRight
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    flora: Object,
    relatedFlora: Array,
    comments: { type: Array, default: () => [] }
});

const page = usePage();
const heroRef = ref(null);
const galleryContainerRef = ref(null);
const relatedContainerRef = ref(null);
const galleryProgress = ref(0);
const relatedProgress = ref(0);
let ctx;

// State
const currentIndex = ref(0);
const lightboxOpen = ref(false);
const copied = ref(false);

// Computed
const allImages = computed(() => {
    const images = [];
    if (props.flora.image_url) {
        images.push({ url: props.flora.image_url, alt: props.flora.name });
    }
    if (props.flora.images) {
        props.flora.images.forEach(img => {
            images.push({ url: img.image_url || img.url, alt: props.flora.name });
        });
    }
    return images.length ? images : [{ url: '/images/placeholder-no-image.svg', alt: 'No image' }];
});

const currentImage = computed(() => allImages.value[currentIndex.value]);
const shareUrl = computed(() => typeof window !== 'undefined' ? window.location.href : '');

// Category config
const categoryConfig = computed(() => {
    switch (props.flora.category) {
        case 'endemik':
            return { bg: 'from-purple-500 to-violet-500', icon: Sparkles, label: 'Endemik' };
        case 'langka':
            return { bg: 'from-amber-500 to-orange-500', icon: Flower2, label: 'Langka' };
        default:
            return { bg: 'from-emerald-500 to-green-500', icon: Leaf, label: 'Umum' };
    }
});

// Scroll handlers
const handleGalleryScroll = () => {
    if (!galleryContainerRef.value) return;
    const container = galleryContainerRef.value;
    const scrollWidth = container.scrollWidth - container.clientWidth;
    if (scrollWidth > 0) {
        galleryProgress.value = (container.scrollLeft / scrollWidth) * 100;
    }
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
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(shareUrl.value);
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    } catch (e) {}
};

const shareToWhatsApp = () => {
    window.open(`https://wa.me/?text=${encodeURIComponent(props.flora.name + ' - ' + shareUrl.value)}`, '_blank');
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

const nextImage = () => {
    currentIndex.value = (currentIndex.value + 1) % allImages.value.length;
};

const prevImage = () => {
    currentIndex.value = (currentIndex.value - 1 + allImages.value.length) % allImages.value.length;
};

const handleKeydown = (e) => {
    if (!lightboxOpen.value) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'ArrowLeft') prevImage();
};

onMounted(async () => {
    await nextTick();
    
    if (galleryContainerRef.value) {
        galleryContainerRef.value.addEventListener('scroll', handleGalleryScroll, { passive: true });
    }
    if (relatedContainerRef.value) {
        relatedContainerRef.value.addEventListener('scroll', handleRelatedScroll, { passive: true });
    }
    
    window.addEventListener('keydown', handleKeydown);
    
    if (heroRef.value) {
        ctx = gsap.context(() => {
            const tl = gsap.timeline({ delay: 0.2 });
            
            tl.fromTo('.hero-breadcrumb', { opacity: 0, y: -15 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' });
            tl.fromTo('.hero-badges > *', { opacity: 0, scale: 0.7 }, { opacity: 1, scale: 1, duration: 0.4, stagger: 0.08, ease: 'back.out(3)' }, '-=0.2');
            tl.fromTo('.hero-title', { opacity: 0, y: 25, filter: 'blur(8px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.5, ease: 'power3.out' }, '-=0.2');
            tl.fromTo('.hero-subtitle', { opacity: 0, y: 15 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }, '-=0.2');
            
            gsap.to('.hero-bg-image', { scale: 1.1, duration: 15, ease: 'none', repeat: -1, yoyo: true });
        }, heroRef.value);
    }
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
    if (galleryContainerRef.value) galleryContainerRef.value.removeEventListener('scroll', handleGalleryScroll);
    if (relatedContainerRef.value) relatedContainerRef.value.removeEventListener('scroll', handleRelatedScroll);
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div>
        <Head>
            <title>{{ flora.name }} - Flora TNLL</title>
            <meta name="description" :content="flora.description?.substring(0, 160)">
        </Head>

        <!-- Premium Hero -->
        <section ref="heroRef" class="relative min-h-[50vh] sm:min-h-[55vh] overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img 
                    :src="flora.image_url || '/images/placeholder-no-image.svg'" 
                    :alt="flora.name"
                    class="hero-bg-image w-full h-full object-cover"
                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/30"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/30 to-transparent"></div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-[15%] right-[10%] w-20 h-20 border border-white/5 rounded-full animate-pulse-slow"></div>
                <div class="absolute bottom-[25%] left-[5%] w-14 h-14 border border-emerald-500/10 rounded-2xl rotate-12 animate-float"></div>
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
                    <Link href="/flora" class="hover:text-white transition-colors">Flora</Link>
                    <ChevronRight class="w-3 h-3" />
                    <span class="text-white/90 truncate max-w-[200px]">{{ flora.name }}</span>
                </nav>

                <div class="max-w-2xl">
                    <!-- Badges -->
                    <div class="hero-badges flex flex-wrap items-center gap-2 mb-3 sm:mb-4">
                        <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-white text-[10px] font-bold shadow-lg bg-gradient-to-r', categoryConfig.bg]">
                            <component :is="categoryConfig.icon" class="w-3 h-3" />
                            {{ flora.category_label || categoryConfig.label }}
                        </span>
                        <span v-if="flora.conservation_status" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white text-[10px] font-semibold">
                            {{ flora.conservation_status_label || flora.conservation_status }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="hero-title text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-2 sm:mb-3">
                        {{ flora.name }} 🌿
                    </h1>

                    <!-- Scientific Name -->
                    <p v-if="flora.scientific_name" class="hero-subtitle text-white/70 text-sm sm:text-base italic">
                        {{ flora.scientific_name }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section class="py-8 sm:py-12 md:py-16 bg-gradient-to-b from-white to-gray-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6 sm:space-y-8">
                        <!-- Gallery with Swipe Scroll -->
                        <div v-if="allImages.length > 0" class="bg-white rounded-xl sm:rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                            <!-- Main Image -->
                            <div class="aspect-video bg-gray-900 relative group cursor-pointer" @click="openLightbox(currentIndex)">
                                <img 
                                    :src="currentImage.url" 
                                    :alt="currentImage.alt" 
                                    class="w-full h-full object-contain"
                                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                >
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all">
                                        <ZoomIn class="w-6 h-6 text-white" />
                                    </div>
                                </div>
                                <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-black/50 backdrop-blur-sm text-white text-[10px] font-medium">
                                    {{ currentIndex + 1 }} / {{ allImages.length }}
                                </div>
                            </div>

                            <!-- Thumbnails with Swipe -->
                            <div v-if="allImages.length > 1" class="p-3 sm:p-4 border-t border-gray-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-semibold text-gray-700">Galeri ({{ allImages.length }})</span>
                                    <span class="text-[10px] text-gray-400 flex items-center gap-1">
                                        Swipe untuk melihat <ChevronRight class="w-3 h-3" />
                                    </span>
                                </div>
                                
                                <div 
                                    ref="galleryContainerRef"
                                    class="flex gap-2 overflow-x-auto pb-2 snap-x snap-mandatory scrollbar-hide"
                                    style="-webkit-overflow-scrolling: touch;"
                                >
                                    <button 
                                        v-for="(img, i) in allImages" 
                                        :key="i" 
                                        @click="currentIndex = i"
                                        :class="['flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden border-2 transition-all snap-start', i === currentIndex ? 'border-emerald-500 ring-2 ring-emerald-500/30' : 'border-transparent hover:border-emerald-300']"
                                    >
                                        <img :src="img.url" class="w-full h-full object-cover" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                                    </button>
                                </div>

                                <div class="mt-2 relative h-1 bg-gray-200 rounded-full overflow-hidden">
                                    <div 
                                        class="absolute top-0 left-0 h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full transition-[width] duration-100"
                                        :style="{ width: `${Math.max(galleryProgress, 10)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="flora.description" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 p-5 sm:p-6">
                            <h2 class="text-sm sm:text-base font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center">
                                    <Leaf class="w-3.5 h-3.5 text-white" />
                                </div>
                                Tentang {{ flora.name }}
                            </h2>
                            <div class="prose prose-sm max-w-none text-gray-600 text-xs sm:text-sm leading-relaxed" v-html="flora.description?.replace(/\n/g, '<br>')"></div>
                        </div>

                        <!-- Comments Section -->
                        <CommentSection 
                            :item-id="flora.id" 
                            item-type="flora" 
                            :comments="comments"
                            color-scheme="emerald"
                        />
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-5">
                        <!-- Info Card -->
                        <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-24">
                            <div class="px-4 py-4 bg-gradient-to-r from-emerald-500 to-green-600">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                        <TreePine class="w-4 h-4 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-white font-bold text-xs sm:text-sm">Informasi</h3>
                                        <p class="text-white/70 text-[9px]">Detail spesies flora</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 space-y-3">
                                <div v-if="flora.local_name" class="flex items-start gap-3 p-3 rounded-xl bg-gray-50">
                                    <MapPin class="w-4 h-4 text-emerald-500 mt-0.5" />
                                    <div>
                                        <p class="text-[10px] text-gray-500">Nama Lokal</p>
                                        <p class="text-xs font-semibold text-gray-900">{{ flora.local_name }}</p>
                                    </div>
                                </div>

                                <div v-if="flora.scientific_name" class="flex items-start gap-3 p-3 rounded-xl bg-gray-50">
                                    <Leaf class="w-4 h-4 text-emerald-500 mt-0.5" />
                                    <div>
                                        <p class="text-[10px] text-gray-500">Nama Ilmiah</p>
                                        <p class="text-xs font-semibold text-gray-900 italic">{{ flora.scientific_name }}</p>
                                    </div>
                                </div>

                                <div v-if="flora.category" class="flex items-start gap-3 p-3 rounded-xl bg-gray-50">
                                    <component :is="categoryConfig.icon" class="w-4 h-4 text-emerald-500 mt-0.5" />
                                    <div>
                                        <p class="text-[10px] text-gray-500">Kategori</p>
                                        <span :class="['inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold text-white bg-gradient-to-r', categoryConfig.bg]">
                                            {{ flora.category_label || categoryConfig.label }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Share -->
                            <div class="p-4 border-t border-gray-100">
                                <h4 class="font-bold text-gray-900 text-[11px] mb-2.5 flex items-center gap-1.5">
                                    <Share2 class="w-3.5 h-3.5 text-emerald-500" />
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

                            <div class="p-4 border-t border-gray-100">
                                <Link href="/flora" class="w-full py-2.5 flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all">
                                    <ArrowLeft class="w-4 h-4" />
                                    Kembali ke Flora
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Flora with Swipe -->
                <div v-if="relatedFlora?.length > 0" class="mt-10 sm:mt-14">
                    <div class="flex items-center justify-between mb-4 sm:mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                <Leaf class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-gray-900">Flora Sejenis</h2>
                                <p class="text-[10px] sm:text-[11px] text-gray-500">Spesies lain yang mungkin Anda suka</p>
                            </div>
                        </div>
                        <span class="text-[10px] text-gray-400 flex items-center gap-1">
                            Swipe untuk melihat <ChevronRight class="w-3 h-3" />
                        </span>
                    </div>

                    <div 
                        ref="relatedContainerRef"
                        class="flex gap-3 sm:gap-4 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-hide"
                        style="-webkit-overflow-scrolling: touch;"
                    >
                        <Link 
                            v-for="item in relatedFlora" 
                            :key="item.id" 
                            :href="`/flora/${item.slug || item.id}`" 
                            class="flex-shrink-0 snap-start group"
                        >
                            <div class="relative w-[200px] sm:w-[240px] aspect-[4/3] rounded-xl sm:rounded-2xl overflow-hidden shadow-md group-hover:shadow-xl border border-gray-100 group-hover:border-emerald-200 transition-all duration-300 group-hover:-translate-y-1">
                                <img 
                                    :src="item.image_url || '/images/placeholder-no-image.svg'" 
                                    :alt="item.name" 
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                
                                <div class="absolute bottom-0 left-0 right-0 p-3">
                                    <h3 class="text-[11px] sm:text-xs font-bold text-white group-hover:text-emerald-200 transition-colors line-clamp-2 leading-tight mb-1">
                                        {{ item.name }}
                                    </h3>
                                    <p v-if="item.scientific_name" class="text-[9px] text-gray-300 italic line-clamp-1">{{ item.scientific_name }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div class="flex items-center justify-between mt-3">
                        <div class="relative w-24 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="absolute top-0 left-0 h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full transition-[width] duration-100"
                                :style="{ width: `${Math.max(relatedProgress, 10)}%` }"
                            ></div>
                        </div>
                        <Link href="/flora" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium text-[10px] sm:text-[11px] shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            Lihat Semua <ChevronRight class="w-3 h-3" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lightbox -->
        <Teleport to="body">
            <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center" @click="closeLightbox">
                <button @click="closeLightbox" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm text-white flex items-center justify-center hover:bg-white/20 transition-all z-10">
                    <X class="w-5 h-5" />
                </button>
                <div class="absolute top-4 left-4 px-3 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm text-white text-xs font-medium">
                    {{ currentIndex + 1 }} / {{ allImages.length }}
                </div>
                <div class="max-w-5xl max-h-[85vh] mx-4" @click.stop>
                    <img :src="currentImage.url" :alt="currentImage.alt" class="max-h-[85vh] mx-auto rounded-lg object-contain" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                </div>
                <button v-if="allImages.length > 1" @click.stop="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 backdrop-blur-sm text-white flex items-center justify-center hover:bg-white/20 transition-all">
                    <ChevronLeft class="w-6 h-6" />
                </button>
                <button v-if="allImages.length > 1" @click.stop="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 backdrop-blur-sm text-white flex items-center justify-center hover:bg-white/20 transition-all">
                    <ChevronRight class="w-6 h-6" />
                </button>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }

@keyframes pulse-slow { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 0.6; transform: scale(1.1); } }
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@keyframes float { 0%, 100% { transform: translateY(0) rotate(12deg); } 50% { transform: translateY(-15px) rotate(12deg); } }
.animate-float { animation: float 6s ease-in-out infinite; }
</style>
