<script setup>
/**
 * Fauna Index - Main Fauna Listing Page
 * Redesigned with premium components
 */
import { Head } from '@inertiajs/vue3';
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
    filters: Object,
    stats: {
        type: Object,
        default: () => ({
            totalFauna: 0,
            totalEndemic: 0,
            totalEndangered: 0,
            totalViews: 0
        })
    }
});
</script>

<template>
    <Head>
        <title>Fauna - Taman Nasional Lore Lindu</title>
        <meta name="description" content="Temukan keajaiban fauna endemik dan langka di Taman Nasional Lore Lindu, habitat bagi ratusan spesies satwa unik Sulawesi.">
    </Head>
    
    <div class="min-h-screen">
        <FaunaHero 
            :total-fauna="stats.totalFauna"
            :total-endemic="stats.totalEndemic"
            :total-endangered="stats.totalEndangered"
            :total-views="stats.totalViews"
        />
        <FaunaFilter :categories="categories" :conservation-statuses="conservationStatuses" :filters="filters" />
        <FaunaGrid :fauna="fauna" />
        <FaunaFeatured :featured-fauna="featuredFauna" />
    </div>
</template>
