<script setup>
/**
 * Show.vue - Destination Detail Page
 * Reorganized 2-column layout with sticky booking sidebar
 */
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import DetailHero from './Partials/DetailHero.vue';
import DetailFullInfo from './Partials/DetailFullInfo.vue';
import DetailBookingSidebar from './Partials/DetailBookingSidebar.vue';
import DetailGallery from './Partials/DetailGallery.vue';
import DetailDescription from './Partials/DetailDescription.vue';
import DetailFacilities from './Partials/DetailFacilities.vue';
import DetailRules from './Partials/DetailRules.vue';
import DetailComments from './Partials/DetailComments.vue';
import DetailRelated from './Partials/DetailRelated.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    destination: { type: Object, required: true },
    relatedDestinations: { type: Array, default: () => [] },
    comments: { type: Array, default: () => [] }
});

const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <Head>
        <title>{{ destination.name }} - TNLL Explore</title>
        <meta name="description" :content="destination.short_description || destination.name">
        <meta name="keywords" :content="destination.keywords || `${destination.name}, ${destination.city}, wisata alam`">
    </Head>

    <div class="min-h-screen bg-gray-50">
        <!-- Hero with Cover Image -->
        <DetailHero :destination="destination" />

        <!-- Main Content - 2 Column Layout -->
        <section class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Left: Main Content -->
                    <div class="flex-1 min-w-0 space-y-6">
                        <!-- Gallery (Most Visual First) -->
                        <DetailGallery :destination="destination" />

                        <!-- Description -->
                        <DetailDescription :destination="destination" />

                        <!-- Location & Contact Info -->
                        <DetailFullInfo :destination="destination" />

                        <!-- Facilities & Rules Side by Side -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <DetailFacilities :destination="destination" />
                            <DetailRules :destination="destination" />
                        </div>

                        <!-- Comments -->
                        <DetailComments :destination="destination" :comments="comments" />
                    </div>

                    <!-- Right: Sticky Booking Sidebar -->
                    <div class="w-full lg:w-[340px] lg:flex-shrink-0">
                        <!-- Mobile: Show sidebar at top -->
                        <div class="lg:hidden mb-6">
                            <DetailBookingSidebar :destination="destination" />
                        </div>
                        <!-- Desktop: Sticky sidebar -->
                        <div class="hidden lg:block">
                            <DetailBookingSidebar :destination="destination" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Destinations -->
        <DetailRelated :destination="destination" :related-destinations="relatedDestinations" />
    </div>
</template>
