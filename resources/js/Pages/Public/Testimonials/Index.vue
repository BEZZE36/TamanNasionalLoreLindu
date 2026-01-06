<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import TestimonialHero from './Partials/TestimonialHero.vue';
import TestimonialGrid from './Partials/TestimonialGrid.vue';

defineOptions({ layout: PublicLayout });
defineProps({ feedbacks: Object, avgRating: Number, totalReviews: Number });
</script>

<template>
    <div>
        <TestimonialHero :avg-rating="avgRating" :total-reviews="totalReviews" />
        <TestimonialGrid :feedbacks="feedbacks" />
    </div>
</template>
