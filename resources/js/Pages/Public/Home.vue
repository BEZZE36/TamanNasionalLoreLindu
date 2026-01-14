<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';

// Import all home page partials
import HeroSection from './Home/HeroSection.vue';
import VideoInfoSection from './Home/VideoInfoSection.vue';
import DestinationsSection from './Home/DestinationsSection.vue';
import GallerySection from './Home/GallerySection.vue';
import WhyTnllSection from './Home/WhyTnllSection.vue';
import BlogSection from './Home/BlogSection.vue';
import NewsSection from './Home/NewsSection.vue';
import TestimonialSection from './Home/TestimonialSection.vue';
import FAQSection from './Home/FAQSection.vue';

defineProps({
    destinations: Array,
    galleries: Array,
    faqItems: Array,
    articles: Array,
    news: Array,
    testimonials: Array,
    avgRating: [Number, String],
    totalReviews: Number,
    stats: Object,
});
</script>

<template>
    <PublicLayout>
        <!-- Hero Section with Video Background -->
        <HeroSection :stats="stats" />

        <!-- Video & Info Section -->
        <VideoInfoSection />

        <!-- Featured Destinations -->
        <DestinationsSection :destinations="destinations" />

        <!-- Gallery Section -->
        <GallerySection :galleries="galleries" />

        <!-- Why TNLL Explore Section -->
        <WhyTnllSection />

        <!-- Blog/Articles Section -->
        <BlogSection :articles="articles" />

        <!-- News Section -->
        <NewsSection :news="news" />

        <!-- Testimonial Section -->
        <TestimonialSection :testimonials="testimonials" :avgRating="avgRating" :totalReviews="totalReviews" />

        <!-- FAQ Section -->
        <FAQSection :faqItems="faqItems" />
    </PublicLayout>
</template>

