<script setup>
/**
 * Flora Index - Main Flora Listing Page
 * Redesigned with premium components
 */
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import FloraHero from './Partials/FloraHero.vue';
import FloraFilter from './Partials/FloraFilter.vue';
import FloraGrid from './Partials/FloraGrid.vue';
import FloraFeatured from './Partials/FloraFeatured.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    flora: Object,
    categories: Object,
    featuredFlora: Array,
    filters: Object,
    stats: {
        type: Object,
        default: () => ({
            totalFlora: 0,
            totalEndemik: 0,
            totalLangka: 0,
            totalViews: 0
        })
    }
});
</script>

<template>
    <Head>
        <title>Flora - Taman Nasional Lore Lindu</title>
        <meta name="description" content="Jelajahi keragaman tumbuhan endemik dan langka di Taman Nasional Lore Lindu, rumah bagi lebih dari 2.290 spesies flora yang menakjubkan.">
    </Head>
    
    <div class="min-h-screen">
        <FloraHero 
            :total-flora="stats.totalFlora"
            :total-endemik="stats.totalEndemik"
            :total-langka="stats.totalLangka"
            :total-views="stats.totalViews"
        />
        <FloraFilter :categories="categories" :filters="filters" />
        <FloraGrid :flora="flora" />
        <FloraFeatured :featured-flora="featuredFlora" />
    </div>
</template>
