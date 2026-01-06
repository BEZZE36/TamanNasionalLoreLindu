<script setup>
import { ref, computed, onMounted } from 'vue';
import { MapPin, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
    siteInfo: Object
});

const mapLoaded = ref(false);
const mapContainer = ref(null);

// Check if we have a valid Google Maps embed
const hasMapEmbed = computed(() => {
    return props.siteInfo?.google_maps_embed && props.siteInfo.google_maps_embed.includes('iframe');
});

// Extract iframe src for lazy loading
const mapSrc = computed(() => {
    if (!hasMapEmbed.value) return null;
    let src = props.siteInfo.google_maps_embed.match(/src="([^"]+)"/)?.[1];
    return src;
});

// Default Google Maps embed (fallback)
const defaultMapSrc = computed(() => {
    const lat = props.siteInfo?.contact_latitude || '-1.4293';
    const lng = props.siteInfo?.contact_longitude || '120.4356';
    return `https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d50000!2d${lng}!3d${lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2sid!4v1704000000000!5m2!1sen!2sid`;
});

// Google Maps link
const googleMapsLink = computed(() => {
    const lat = props.siteInfo?.contact_latitude || '-1.4293';
    const lng = props.siteInfo?.contact_longitude || '120.4356';
    return `https://www.google.com/maps?q=${lat},${lng}`;
});

// Lazy load
onMounted(() => {
    if (!mapContainer.value) return;
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    mapLoaded.value = true;
                    observer.disconnect();
                }
            });
        },
        { rootMargin: '100px' }
    );
    observer.observe(mapContainer.value);
});
</script>

<template>
    <div ref="mapContainer" class="footer-map">
        <h3 class="text-white font-medium text-sm mb-3 flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
            </span>
            Lokasi
        </h3>

        <!-- Map Container -->
        <div class="relative rounded-xl overflow-hidden bg-slate-800/50 group hover:ring-2 hover:ring-emerald-500/30 transition-all duration-300">
            <!-- Loaded iframe -->
            <div v-if="mapLoaded" class="relative">
                <iframe 
                    v-if="hasMapEmbed"
                    :src="mapSrc"
                    class="w-full h-32 sm:h-36 lg:h-40 border-0"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi TNLL"
                ></iframe>
                <iframe 
                    v-else
                    :src="defaultMapSrc"
                    class="w-full h-32 sm:h-36 lg:h-40 border-0"
                    loading="lazy"
                    title="Lokasi TNLL"
                ></iframe>
            </div>

            <!-- Placeholder -->
            <div v-else class="h-32 sm:h-36 lg:h-40 flex items-center justify-center bg-slate-800/30">
                <MapPin class="w-6 h-6 text-slate-600 animate-pulse" />
            </div>

            <!-- Open in Maps button -->
            <a 
                :href="googleMapsLink"
                target="_blank"
                rel="noopener noreferrer"
                class="absolute bottom-2 right-2 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-black/70 backdrop-blur-sm text-white text-xs font-medium hover:bg-emerald-500 transition-all duration-200"
            >
                <ExternalLink class="w-3.5 h-3.5" />
                <span class="hidden sm:inline">Buka Maps</span>
                <span class="sm:hidden">Maps</span>
            </a>
        </div>

        <!-- Address -->
        <p class="mt-2 text-slate-500 text-xs line-clamp-2 leading-relaxed">
            {{ siteInfo?.contact_address || 'Taman Nasional Lore Lindu, Sulawesi Tengah' }}
        </p>
    </div>
</template>
