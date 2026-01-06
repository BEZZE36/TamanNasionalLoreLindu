<script setup>
/**
 * DetailMap.vue - Embedded Map with Actions
 * Design system: text-[11px] labels, w-3.5 icons
 */
import { ref, computed } from 'vue';
import { MapPin, Copy, Check, ExternalLink, Navigation } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

const copied = ref(false);
const lat = computed(() => parseFloat(props.destination.latitude) || -1.5);
const lng = computed(() => parseFloat(props.destination.longitude) || 120.5);
const mapsUrl = computed(() => `https://www.google.com/maps?q=${lat.value},${lng.value}`);
const directionsUrl = computed(() => `https://www.google.com/maps/dir/?api=1&destination=${lat.value},${lng.value}`);
const embedUrl = computed(() => `https://maps.google.com/maps?q=${lat.value},${lng.value}&t=&z=13&ie=UTF8&iwloc=&output=embed`);

const copyCoords = async () => { try { await navigator.clipboard.writeText(`${lat.value}, ${lng.value}`); copied.value = true; setTimeout(() => { copied.value = false; }, 2000); } catch (e) {} };
</script>

<template>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-100 to-cyan-100 flex items-center justify-center">
                <MapPin class="w-4 h-4 text-teal-600" />
            </div>
            <h2 class="text-sm font-bold text-gray-900">Lokasi</h2>
        </div>

        <!-- Map Embed -->
        <div class="relative aspect-[16/9] bg-gray-100">
            <iframe :src="embedUrl" class="w-full h-full border-0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <!-- Actions -->
        <div class="p-3">
            <!-- Coordinates -->
            <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 mb-2">
                <span class="text-[10px] font-mono text-gray-500">{{ lat.toFixed(5) }}, {{ lng.toFixed(5) }}</span>
                <button @click="copyCoords" class="flex items-center gap-1 text-[10px] font-medium text-emerald-600 hover:text-emerald-700">
                    <Check v-if="copied" class="w-3 h-3" />
                    <Copy v-else class="w-3 h-3" />
                    {{ copied ? 'Tersalin!' : 'Salin' }}
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2">
                <a :href="mapsUrl" target="_blank" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-semibold transition-colors">
                    <ExternalLink class="w-3.5 h-3.5" />
                    Buka Maps
                </a>
                <a :href="directionsUrl" target="_blank" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[11px] font-semibold shadow-md transition-all">
                    <Navigation class="w-3.5 h-3.5" />
                    Petunjuk Arah
                </a>
            </div>
        </div>
    </div>
</template>
