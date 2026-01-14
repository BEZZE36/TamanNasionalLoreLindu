<script setup>
/**
 * Index.vue - Testimonials Index Page
 * Uses PublicLayout via defineOptions for Inertia persistent layout
 */
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import TestimonialHero from './Partials/TestimonialHero.vue';
import TestimonialGrid from './Partials/TestimonialGrid.vue';

defineOptions({ layout: PublicLayout });

defineProps({ 
    feedbacks: Object, 
    avgRating: [Number, String], 
    totalReviews: Number 
});
</script>

<template>
    <Head title="Testimoni - Taman Nasional Lore Lindu" />
    
    <div class="min-h-screen">
        <TestimonialHero :avg-rating="avgRating" :total-reviews="totalReviews" />
        <TestimonialGrid :feedbacks="feedbacks" />
    </div>
</template>
