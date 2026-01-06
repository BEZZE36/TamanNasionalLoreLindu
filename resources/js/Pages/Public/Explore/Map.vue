<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Map, Star, MapPin, Navigation, X, ExternalLink } from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    destinations: { type: Array, default: () => [] }
});

const selectedDestination = ref(null);
const mapCenter = { lat: -1.5, lng: 120.25 }; // Central Sulawesi coordinates
const mapZoom = 10;

// Select destination
const selectDestination = (dest) => {
    selectedDestination.value = dest;
};

// Close sidebar
const closeDetails = () => {
    selectedDestination.value = null;
};
</script>

<template>
    <div>
        <Head><title>Explore Map - TNLL</title><meta name="description" content="Jelajahi peta interaktif Taman Nasional Lore Lindu"></Head>
        
        <!-- Hero -->
        <section class="relative pt-32 pb-12 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-teal-600 via-cyan-700 to-blue-900"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-teal-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4">
                        <Map class="w-4 h-4" />Explore Map
                    </span>
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">Peta Interaktif 🗺️</h1>
                    <p class="text-white/80 max-w-xl">Jelajahi destinasi wisata Taman Nasional Lore Lindu melalui peta interaktif</p>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="py-8 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Destinations List -->
                    <div class="lg:col-span-1 space-y-4 max-h-[600px] overflow-y-auto">
                        <h3 class="font-bold text-gray-900 text-lg mb-4">{{ destinations.length }} Destinasi</h3>
                        <button 
                            v-for="dest in destinations" 
                            :key="dest.id"
                            @click="selectDestination(dest)"
                            :class="[
                                'w-full text-left p-4 rounded-xl transition-all',
                                selectedDestination?.id === dest.id 
                                    ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/30' 
                                    : 'bg-white shadow hover:shadow-lg hover:-translate-y-1 border border-gray-100'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <img :src="dest.image || '/images/placeholder-no-image.svg'" :alt="dest.name" class="w-12 h-12 rounded-lg object-cover">
                                <div class="flex-1 min-w-0">
                                    <h4 :class="['font-bold truncate', selectedDestination?.id === dest.id ? 'text-white' : 'text-gray-900']">{{ dest.name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs" :class="selectedDestination?.id === dest.id ? 'text-white/70' : 'text-gray-500'">{{ dest.category }}</span>
                                        <div class="flex items-center gap-1">
                                            <Star :class="['w-3 h-3', selectedDestination?.id === dest.id ? 'text-yellow-300 fill-yellow-300' : 'text-yellow-400 fill-yellow-400']" />
                                            <span :class="['text-xs font-medium', selectedDestination?.id === dest.id ? 'text-white' : 'text-gray-600']">{{ (parseFloat(dest.rating) || 0).toFixed(1) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Map Container -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                            <!-- Embedded Google Map -->
                            <div class="relative h-[600px]">
                                <iframe 
                                    :src="`https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d255281.19889951!2d${selectedDestination?.lng || mapCenter.lng}!3d${selectedDestination?.lat || mapCenter.lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sid!4v1704000000000!5m2!1sen!2sid`"
                                    class="w-full h-full border-0"
                                    allowfullscreen
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                                
                                <!-- Selected Destination Overlay -->
                                <div 
                                    v-if="selectedDestination"
                                    class="absolute bottom-4 left-4 right-4 bg-white/95 backdrop-blur-xl rounded-2xl shadow-xl p-6 animate-slide-up"
                                >
                                    <button @click="closeDetails" class="absolute top-4 right-4 p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                                        <X class="w-4 h-4 text-gray-600" />
                                    </button>
                                    
                                    <div class="flex gap-4">
                                        <img :src="selectedDestination.image || '/images/placeholder-no-image.svg'" :alt="selectedDestination.name" class="w-24 h-24 rounded-xl object-cover">
                                        <div class="flex-1">
                                            <span class="text-xs font-bold text-teal-600 uppercase">{{ selectedDestination.category }}</span>
                                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ selectedDestination.name }}</h3>
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="flex items-center gap-1">
                                                    <MapPin class="w-4 h-4 text-gray-400" />
                                                    <span class="text-sm text-gray-600">{{ selectedDestination.city || 'Sulawesi Tengah' }}</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                                                    <span class="text-sm font-medium text-gray-700">{{ (parseFloat(selectedDestination.rating) || 0).toFixed(1) }}</span>
                                                </div>
                                            </div>
                                            <div class="flex gap-3">
                                                <Link :href="`/destinations/${selectedDestination.slug}`" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-600 text-white font-bold rounded-xl text-sm hover:shadow-lg transition-all flex items-center gap-2">
                                                    <ExternalLink class="w-4 h-4" />Lihat Detail
                                                </Link>
                                                <a :href="`https://www.google.com/maps/dir/?api=1&destination=${selectedDestination.lat},${selectedDestination.lng}`" target="_blank" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-200 transition-all flex items-center gap-2">
                                                    <Navigation class="w-4 h-4" />Petunjuk Arah
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes slide-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>
