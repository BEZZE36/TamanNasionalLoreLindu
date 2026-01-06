<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import GalleryHero from './Partials/GalleryHero.vue';
import GalleryFilter from './Partials/GalleryFilter.vue';
import GalleryGrid from './Partials/GalleryGrid.vue';

defineOptions({ layout: PublicLayout });

defineProps({
    galleries: Object,
    categories: Array,
    destinations: Array,
    filters: Object
});
</script>

<template>
    <div>
        <GalleryHero />
        <GalleryFilter :categories="categories" :destinations="destinations" :filters="filters" />
        <GalleryGrid :galleries="galleries" />
    </div>
</template>
