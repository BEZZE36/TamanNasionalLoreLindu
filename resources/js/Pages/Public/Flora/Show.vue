<script setup>
import { computed } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ChevronRight, ArrowLeft, Share2 } from 'lucide-vue-next';

defineOptions({
    layout: PublicLayout
});

const props = defineProps({
    flora: Object,
    relatedFlora: Array
});

const page = usePage();
const user = page.props.auth?.user;

// Category badge class
const categoryBadgeClass = computed(() => {
    switch (props.flora.category) {
        case 'endemik':
            return 'bg-purple-100 text-purple-700';
        case 'langka':
            return 'bg-red-100 text-red-700';
        default:
            return 'bg-green-100 text-green-700';
    }
});

// Get all images including cover
const allImages = computed(() => {
    const images = [{ url: props.flora.image_url, alt: props.flora.name }];
    if (props.flora.images) {
        props.flora.images.forEach(img => {
            images.push({ url: img.image_url || img.url, alt: props.flora.name });
        });
    }
    return images;
});

// Share URL
const shareUrl = computed(() => window.location.href);
</script>

<template>
    <div>
        <Head>
            <title>{{ flora.name }} - Flora TNLL</title>
            <meta name="description" :content="flora.description?.substring(0, 160)">
        </Head>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-green-600 via-emerald-700 to-green-900"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-green-400/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 animate-pulse"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-white/70 mb-6">
                    <Link href="/" class="hover:text-white transition-colors">Beranda</Link>
                    <ChevronRight class="w-4 h-4" />
                    <Link href="/flora" class="hover:text-white transition-colors">Flora</Link>
                    <ChevronRight class="w-4 h-4" />
                    <span class="text-white">{{ flora.name }}</span>
                </nav>

                <div class="max-w-3xl">
                    <!-- Category Badge -->
                    <span 
                        v-if="flora.category"
                        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4"
                    >
                        {{ flora.category_label }}
                    </span>

                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                        {{ flora.name }} 🌿
                    </h1>

                    <!-- Scientific Name -->
                    <p v-if="flora.scientific_name" class="text-white/70 italic text-lg">
                        {{ flora.scientific_name }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section class="py-12 md:py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Gallery -->
                        <div v-if="allImages.length > 0" class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-1 overflow-hidden">
                            <div class="grid grid-cols-4 gap-1 auto-rows-[100px] md:auto-rows-[150px]">
                                <div 
                                    v-for="(image, index) in allImages.slice(0, 5)"
                                    :key="index"
                                    :class="[
                                        'relative overflow-hidden group cursor-pointer rounded-lg',
                                        index === 0 ? 'col-span-2 row-span-2' : 'col-span-1 row-span-1'
                                    ]"
                                >
                                    <img 
                                        :src="image.url"
                                        :alt="image.alt"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                        @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                    >
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-8">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Tentang {{ flora.name }}</h2>
                            <div class="prose max-w-none text-gray-600" v-html="flora.description?.replace(/\n/g, '<br>')"></div>
                        </div>

                        <!-- Share Buttons -->
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <Share2 class="w-5 h-5 text-green-500" />
                                Bagikan
                            </h3>
                            <div class="flex gap-3">
                                <a 
                                    :href="`https://wa.me/?text=${encodeURIComponent(flora.name + ' - ' + shareUrl)}`"
                                    target="_blank"
                                    class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                    WhatsApp
                                </a>
                                <a 
                                    :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`"
                                    target="_blank"
                                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Facebook
                                </a>
                                <a 
                                    :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(flora.name)}`"
                                    target="_blank"
                                    class="flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-xl hover:bg-gray-800 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    X
                                </a>
                            </div>
                        </div>

                        <!-- Related Flora -->
                        <div v-if="relatedFlora?.length > 0" class="pt-8 border-t border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Flora Sejenis</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <Link 
                                    v-for="item in relatedFlora"
                                    :key="item.id"
                                    :href="`/flora/${item.slug || item.id}`"
                                    class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
                                >
                                    <div class="relative h-48 overflow-hidden">
                                        <img 
                                            :src="item.image_url"
                                            :alt="item.name"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                            @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                                        >
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    </div>
                                    <div class="p-5">
                                        <h3 class="font-bold text-lg text-gray-900 group-hover:text-green-600 transition-colors line-clamp-1 mb-1">{{ item.name }}</h3>
                                        <p v-if="item.scientific_name" class="text-xs text-gray-500 italic">{{ item.scientific_name }}</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 p-6 sticky top-24">
                            <h3 class="font-bold text-gray-900 mb-4">Informasi</h3>
                            <div class="space-y-4">
                                <div v-if="flora.local_name">
                                    <p class="text-sm text-gray-500">Nama Lokal</p>
                                    <p class="font-medium text-gray-900">{{ flora.local_name }}</p>
                                </div>
                                <div v-if="flora.scientific_name">
                                    <p class="text-sm text-gray-500">Nama Ilmiah</p>
                                    <p class="font-medium text-gray-900 italic">{{ flora.scientific_name }}</p>
                                </div>
                                <div v-if="flora.category">
                                    <p class="text-sm text-gray-500">Kategori</p>
                                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium', categoryBadgeClass]">
                                        {{ flora.category_label }}
                                    </span>
                                </div>
                                <div v-if="flora.conservation_status">
                                    <p class="text-sm text-gray-500">Status Konservasi</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ flora.conservation_status_label || flora.conservation_status }}
                                    </span>
                                </div>
                            </div>

                            <hr class="my-6">

                            <Link 
                                href="/flora"
                                class="w-full py-3 flex items-center justify-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/30 hover:shadow-green-500/50 transition-all"
                            >
                                <ArrowLeft class="w-4 h-4" />
                                Kembali ke Flora
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
