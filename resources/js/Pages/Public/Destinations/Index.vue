<script setup>
/**
 * Index.vue - Destination Listing Page
 * Composes all enhanced Index partials
 */
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import IndexHero from './Partials/IndexHero.vue';
import IndexFilters from './Partials/IndexFilters.vue';
import IndexGrid from './Partials/IndexGrid.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    destinations: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    minPrice: { type: Number, default: 0 },
    maxPrice: { type: Number, default: 1000000 },
    allFacilities: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    totalBookings: { type: Number, default: 0 }
});

const page = usePage();
const user = page.props.auth?.user;

// Calculate stats
const totalDestinations = props.destinations.total || 0;
const averageRating = 4.8;
</script>

<template>
    <Head>
        <title>Destinasi Wisata - TNLL Explore</title>
        <meta name="description" content="Jelajahi destinasi wisata alam menakjubkan di Taman Nasional Lore Lindu, Sulawesi Tengah">
    </Head>

    <div class="min-h-screen bg-gray-50">
        <!-- Premium Hero -->
        <IndexHero 
            :total-destinations="totalDestinations" 
            :average-rating="averageRating" 
            :total-bookings="totalBookings"
        />

        <!-- Sticky Filters -->
        <IndexFilters 
            :categories="categories" 
            :filters="filters"
            :min-price="minPrice"
            :max-price="maxPrice"
            :all-facilities="allFacilities"
        />

        <!-- Destination Grid -->
        <IndexGrid 
            :destinations="destinations" 
            :user="user" 
        />
    </div>
</template>
