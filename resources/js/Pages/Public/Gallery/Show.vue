<script setup>
import { computed, ref } from 'vue';
import { usePage, Link, Head, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ChevronRight, ArrowLeft, Heart, Eye, Share2, Play, Download } from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    gallery: Object,
    related: Array,
    isLiked: Boolean
});

const page = usePage();
const user = page.props.auth?.user;
const liked = ref(props.isLiked);
const likesCount = ref(props.gallery.likes_count || 0);

const allMedia = computed(() => {
    const media = [];
    if (props.gallery.cover_image_url) media.push({ type: 'image', url: props.gallery.cover_image_url });
    if (props.gallery.media) props.gallery.media.forEach(m => media.push(m));
    return media.length ? media : [{ type: 'image', url: '/images/placeholder-no-image.svg' }];
});

const currentIndex = ref(0);
const currentMedia = computed(() => allMedia.value[currentIndex.value]);

const toggleLike = async () => {
    if (!user) { window.location.href = '/login'; return; }
    try {
        await fetch(`/gallery/${props.gallery.slug}/like`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Content-Type': 'application/json' }
        });
        liked.value = !liked.value;
        likesCount.value += liked.value ? 1 : -1;
    } catch (e) { console.error('Failed to like', e); }
};

const shareUrl = computed(() => window.location.href);
</script>

<template>
    <div>
        <Head><title>{{ gallery.title }} - Galeri TNLL</title><meta name="description" :content="gallery.description?.substring(0, 160)"></Head>

        <!-- Hero -->
        <section class="relative pt-32 pb-20 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-violet-600 via-purple-700 to-indigo-900"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-violet-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <nav class="flex items-center gap-2 text-sm text-white/70 mb-6">
                    <Link href="/" class="hover:text-white">Beranda</Link><ChevronRight class="w-4 h-4" />
                    <Link href="/gallery" class="hover:text-white">Galeri</Link><ChevronRight class="w-4 h-4" />
                    <span class="text-white">{{ gallery.title }}</span>
                </nav>
                <div class="max-w-3xl">
                    <span v-if="gallery.category" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4">{{ gallery.category.name }}</span>
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">{{ gallery.title }}</h1>
                    <div class="flex items-center gap-4 text-white/70">
                        <span class="flex items-center gap-1"><Eye class="w-4 h-4" />{{ gallery.view_count || 0 }} views</span>
                        <span class="flex items-center gap-1"><Heart class="w-4 h-4" />{{ likesCount }} likes</span>
                        <span v-if="gallery.destination">📍 {{ gallery.destination.name }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content -->
        <section class="py-12 md:py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Main Media -->
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="aspect-video bg-black relative">
                                <template v-if="currentMedia.type === 'video'">
                                    <video :src="currentMedia.url" controls class="w-full h-full object-contain"></video>
                                </template>
                                <template v-else>
                                    <img :src="currentMedia.url" :alt="gallery.title" class="w-full h-full object-contain" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                                </template>
                            </div>
                            <div v-if="allMedia.length > 1" class="p-4 flex gap-2 overflow-x-auto">
                                <button v-for="(m, i) in allMedia" :key="i" @click="currentIndex = i"
                                    :class="['w-16 h-16 rounded-lg overflow-hidden border-2 transition-all flex-shrink-0', i === currentIndex ? 'border-violet-500' : 'border-transparent hover:border-violet-300']">
                                    <template v-if="m.type === 'video'"><div class="w-full h-full bg-gray-800 flex items-center justify-center"><Play class="w-6 h-6 text-white" /></div></template>
                                    <template v-else><img :src="m.url" class="w-full h-full object-cover"></template>
                                </button>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="gallery.description" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-8">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Deskripsi</h2>
                            <div class="prose max-w-none text-gray-600" v-html="gallery.description?.replace(/\n/g, '<br>')"></div>
                        </div>

                        <!-- Related -->
                        <div v-if="related?.length > 0" class="pt-8 border-t border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Galeri Terkait</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <Link v-for="item in related" :key="item.id" :href="`/gallery/${item.slug}`" class="group relative aspect-square rounded-xl overflow-hidden shadow-lg">
                                    <img :src="item.thumbnail_url || item.cover_image_url || '/images/placeholder-no-image.svg'" :alt="item.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                                    <div class="absolute bottom-0 left-0 right-0 p-3"><h3 class="text-sm font-bold text-white line-clamp-2">{{ item.title }}</h3></div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-6 sticky top-24">
                            <div class="flex gap-3 mb-6">
                                <button @click="toggleLike" :class="['flex-1 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all', liked ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
                                    <Heart :class="['w-5 h-5', liked ? 'fill-current' : '']" />{{ liked ? 'Disukai' : 'Suka' }}
                                </button>
                                <a :href="currentMedia.url" download target="_blank" class="py-3 px-4 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all"><Download class="w-5 h-5" /></a>
                            </div>
                            
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><Share2 class="w-5 h-5 text-violet-500" />Bagikan</h3>
                            <div class="flex gap-3">
                                <a :href="`https://wa.me/?text=${encodeURIComponent(gallery.title + ' - ' + shareUrl)}`" target="_blank" class="flex-1 py-2 bg-green-500 text-white rounded-xl text-center text-sm font-bold hover:bg-green-600 transition-colors">WhatsApp</a>
                                <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`" target="_blank" class="flex-1 py-2 bg-blue-600 text-white rounded-xl text-center text-sm font-bold hover:bg-blue-700 transition-colors">Facebook</a>
                            </div>

                            <hr class="my-6">
                            <Link href="/gallery" class="w-full py-3 flex items-center justify-center gap-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold rounded-xl shadow-lg shadow-violet-500/30 hover:shadow-violet-500/50 transition-all">
                                <ArrowLeft class="w-4 h-4" />Kembali
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
