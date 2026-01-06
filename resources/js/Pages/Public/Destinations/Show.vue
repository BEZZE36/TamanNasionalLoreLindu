<script setup>
/**
 * Show.vue - Destination Detail Page
 * Composes all Detail partials, displays ALL admin fields
 */
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import DetailHero from './Partials/DetailHero.vue';
import DetailFullInfo from './Partials/DetailFullInfo.vue';
import DetailPricing from './Partials/DetailPricing.vue';
import DetailGallery from './Partials/DetailGallery.vue';
import DetailDescription from './Partials/DetailDescription.vue';
import DetailFacilities from './Partials/DetailFacilities.vue';
import DetailRules from './Partials/DetailRules.vue';
import DetailReviews from './Partials/DetailReviews.vue';
import DetailRelated from './Partials/DetailRelated.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    destination: { type: Object, required: true },
    relatedDestinations: { type: Array, default: () => [] }
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

        <!-- Main Content -->
        <section class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Full Info (Address, Contact, Coordinates, Stats) -->
                <DetailFullInfo :destination="destination" />

                <!-- Pricing: Tickets & Parking (Separate Cards) -->
                <DetailPricing :destination="destination" />

                <!-- Gallery -->
                <DetailGallery :destination="destination" />

                <!-- Description -->
                <DetailDescription :destination="destination" />

                <!-- Facilities & Rules Side by Side -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <DetailFacilities :destination="destination" />
                    <DetailRules :destination="destination" />
                </div>

                <!-- Reviews -->
                <DetailReviews :destination="destination" />
            </div>
        </section>

        <!-- Related Destinations (IndexCard Style) -->
        <DetailRelated :destination="destination" :related-destinations="relatedDestinations" />
    </div>
</template>
