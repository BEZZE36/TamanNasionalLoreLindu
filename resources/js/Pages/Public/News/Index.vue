<script setup>
/**
 * News/Index.vue - News Listing Page
 * Composes all premium News partials with proper props
 */
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import NewsHero from './Partials/NewsHero.vue';
import NewsFeatured from './Partials/NewsFeatured.vue';
import NewsFilter from './Partials/NewsFilter.vue';
import NewsGrid from './Partials/NewsGrid.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    news: Object,
    featuredNews: Array,
    recentNews: Array,
    archives: Array,
    filters: Object,
    stats: { type: Object, default: () => ({}) }
});

// Calculate stats from props or defaults
const totalNews = computed(() => props.news?.total || props.news?.data?.length || 0);
const totalViews = computed(() => props.stats?.totalViews || 0);
const totalComments = computed(() => props.stats?.totalComments || 0);
</script>

<template>
    <Head>
        <title>Berita Terkini - TNLL Explore</title>
        <meta name="description" content="Informasi dan berita terbaru seputar Taman Nasional Lore Lindu. Update kegiatan, event, dan pengumuman penting.">
        <meta name="keywords" content="berita, news, taman nasional, lore lindu, sulawesi tengah, wisata alam, konservasi, event">
        <meta property="og:title" content="Berita Terkini - TNLL Explore">
        <meta property="og:description" content="Informasi dan berita terbaru seputar Taman Nasional Lore Lindu.">
    </Head>

    <div class="min-h-screen bg-gray-50">
        <!-- Premium Hero Section -->
        <NewsHero 
            :total-news="totalNews"
            :total-views="totalViews"
            :total-comments="totalComments"
        />

        <!-- Featured News -->
        <NewsFeatured :featured-news="featuredNews" />

        <!-- Sticky Filter -->
        <NewsFilter 
            :archives="archives" 
            :filters="filters"
            :total-news="totalNews"
        />

        <!-- News Grid -->
        <NewsGrid :news="news" />
    </div>
</template>
