<script setup>
/**
 * Gallery Index - Main Gallery Listing Page
 * Redesigned with premium components
 */
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import GalleryHero from './Partials/GalleryHero.vue';
import GalleryFilter from './Partials/GalleryFilter.vue';
import GalleryGrid from './Partials/GalleryGrid.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    galleries: Object,
    categories: Array,
    destinations: Array,
    filters: Object,
    stats: {
        type: Object,
        default: () => ({
            totalGalleries: 0,
            totalPhotos: 0,
            totalVideos: 0,
            totalViews: 0,
            totalLikes: 0
        })
    }
});
</script>

<template>
    <Head>
        <title>Galeri Media - Taman Nasional Lore Lindu</title>
        <meta name="description" content="Jelajahi koleksi foto dan video menakjubkan dari keindahan alam Taman Nasional Lore Lindu. Temukan momen-momen indah yang diabadikan pengunjung.">
    </Head>
    
    <div class="min-h-screen">
        <GalleryHero 
            :total-galleries="stats.totalGalleries"
            :total-photos="stats.totalPhotos"
            :total-videos="stats.totalVideos"
            :total-views="stats.totalViews"
            :total-likes="stats.totalLikes"
        />
        <GalleryFilter :categories="categories" :destinations="destinations" :filters="filters" />
        <GalleryGrid :galleries="galleries" />
    </div>
</template>
