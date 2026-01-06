<script setup>
import { usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import FloraHero from './Partials/FloraHero.vue';
import FloraFilter from './Partials/FloraFilter.vue';
import FloraGrid from './Partials/FloraGrid.vue';
import FloraFeatured from './Partials/FloraFeatured.vue';

defineOptions({
    layout: PublicLayout
});

const props = defineProps({
    flora: Object,
    categories: Object,
    featuredFlora: Array,
    filters: Object
});
</script>

<template>
    <div>
        <!-- Hero -->
        <FloraHero />

        <!-- Filter -->
        <FloraFilter 
            :categories="categories"
            :filters="filters"
        />

        <!-- Grid -->
        <FloraGrid :flora="flora" />

        <!-- Featured -->
        <FloraFeatured :featured-flora="featuredFlora" />
    </div>
</template>
