<script setup>
/**
 * Blog/Index.vue - Blog Listing Page
 * Composes all premium Blog partials with proper props
 */
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BlogHero from './Partials/BlogHero.vue';
import BlogFeatured from './Partials/BlogFeatured.vue';
import BlogFilter from './Partials/BlogFilter.vue';
import BlogGrid from './Partials/BlogGrid.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    articles: Object,
    featuredArticles: Array,
    categories: Object,
    recentArticles: Array,
    filters: Object,
    stats: { type: Object, default: () => ({}) }
});

// Calculate stats from props or defaults
const totalArticles = computed(() => props.articles?.total || props.articles?.data?.length || 0);
const totalViews = computed(() => props.stats?.totalViews || 0);
const totalComments = computed(() => props.stats?.totalComments || 0);
</script>

<template>
    <Head>
        <title>Blog & Artikel - TNLL Explore</title>
        <meta name="description" content="Temukan cerita, tips, dan informasi menarik seputar Taman Nasional Lore Lindu. Eksplorasi keindahan alam dan kekayaan budaya Sulawesi Tengah.">
        <meta name="keywords" content="blog, artikel, taman nasional, lore lindu, sulawesi tengah, wisata alam, konservasi">
        <meta property="og:title" content="Blog & Artikel - TNLL Explore">
        <meta property="og:description" content="Temukan cerita, tips, dan informasi menarik seputar Taman Nasional Lore Lindu.">
    </Head>

    <div class="min-h-screen bg-gray-50">
        <!-- Premium Hero Section -->
        <BlogHero 
            :total-articles="totalArticles"
            :total-views="totalViews"
            :total-comments="totalComments"
        />

        <!-- Featured Articles -->
        <BlogFeatured :featured-articles="featuredArticles" />

        <!-- Sticky Filter -->
        <BlogFilter 
            :categories="categories" 
            :filters="filters"
            :total-articles="totalArticles"
        />

        <!-- Articles Grid -->
        <BlogGrid :articles="articles" />
    </div>
</template>
