<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BlogHero from './Partials/BlogHero.vue';
import BlogFilter from './Partials/BlogFilter.vue';
import BlogGrid from './Partials/BlogGrid.vue';
import BlogFeatured from './Partials/BlogFeatured.vue';

defineOptions({ layout: PublicLayout });

defineProps({
    articles: Object,
    featuredArticles: Array,
    categories: Object,
    recentArticles: Array,
    filters: Object
});
</script>

<template>
    <div>
        <BlogHero />
        <BlogFeatured :featured-articles="featuredArticles" />
        <BlogFilter :categories="categories" :filters="filters" />
        <BlogGrid :articles="articles" />
    </div>
</template>
