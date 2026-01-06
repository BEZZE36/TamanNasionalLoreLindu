<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import NewsHero from './Partials/NewsHero.vue';
import NewsGrid from './Partials/NewsGrid.vue';

defineOptions({ layout: PublicLayout });
defineProps({ news: Object, featuredNews: Array, recentNews: Array, archives: Array, filters: Object });
</script>

<template>
    <div>
        <NewsHero />
        <NewsGrid :news="news" />
    </div>
</template>
