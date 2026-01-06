<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import FaunaHero from './Partials/FaunaHero.vue';
import FaunaFilter from './Partials/FaunaFilter.vue';
import FaunaGrid from './Partials/FaunaGrid.vue';
import FaunaFeatured from './Partials/FaunaFeatured.vue';

defineOptions({ layout: PublicLayout });

defineProps({
    fauna: Object,
    categories: Object,
    conservationStatuses: Object,
    featuredFauna: Array,
    filters: Object
});
</script>

<template>
    <div>
        <FaunaHero />
        <FaunaFilter :categories="categories" :conservation-statuses="conservationStatuses" :filters="filters" />
        <FaunaGrid :fauna="fauna" />
        <FaunaFeatured :featured-fauna="featuredFauna" />
    </div>
</template>
