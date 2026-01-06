<script setup>
/**
 * DetailAddress.vue - Full Address Card
 * Design system: text-[11px] address, w-3.5 icons
 */
import { ref, computed } from 'vue';
import { MapPin, Copy, Check, ExternalLink, Navigation, Building } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

const copied = ref(false);
const lat = computed(() => parseFloat(props.destination.latitude) || 0);
const lng = computed(() => parseFloat(props.destination.longitude) || 0);
const mapsUrl = computed(() => `https://www.google.com/maps?q=${lat.value},${lng.value}`);
const directionsUrl = computed(() => `https://www.google.com/maps/dir/?api=1&destination=${lat.value},${lng.value}`);

const fullAddress = computed(() => {
    const parts = [];
    if (props.destination.address) parts.push(props.destination.address);
    if (props.destination.city) parts.push(props.destination.city);
    if (props.destination.province) parts.push(props.destination.province);
    if (props.destination.postal_code) parts.push(props.destination.postal_code);
    return parts.length > 0 ? parts.join(', ') : 'Taman Nasional Lore Lindu, Sulawesi Tengah, Indonesia';
});

const copyAddress = async () => { try { await navigator.clipboard.writeText(fullAddress.value); copied.value = true; setTimeout(() => { copied.value = false; }, 2000); } catch (e) {} };
</script>

<template>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-md">
                <MapPin class="w-4 h-4 text-white" />
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Alamat Lengkap</h3>
                <p class="text-[9px] text-gray-500">Lokasi destinasi</p>
            </div>
        </div>

        <!-- Address -->
        <div class="p-4">
            <div class="bg-gray-50 rounded-lg p-3 mb-3 border border-gray-100">
                <div class="flex items-start gap-2">
                    <Building class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                    <div>
                        <p class="text-[11px] text-gray-700 font-medium leading-relaxed">{{ fullAddress }}</p>
                        <p class="text-[9px] text-gray-400 mt-1 font-mono">{{ lat.toFixed(6) }}, {{ lng.toFixed(6) }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-3 gap-2">
                <button @click="copyAddress" class="flex flex-col items-center gap-1.5 p-2.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-100 transition-all">
                    <Check v-if="copied" class="w-4 h-4 text-emerald-500" />
                    <Copy v-else class="w-4 h-4 text-gray-500" />
                    <span class="text-[9px] font-semibold text-gray-600">{{ copied ? 'Tersalin!' : 'Salin' }}</span>
                </button>
                <a :href="mapsUrl" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-lg bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 transition-all">
                    <ExternalLink class="w-4 h-4 text-blue-500" />
                    <span class="text-[9px] font-semibold text-gray-600">Buka Maps</span>
                </a>
                <a :href="directionsUrl" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-lg bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 transition-all">
                    <Navigation class="w-4 h-4 text-emerald-600" />
                    <span class="text-[9px] font-semibold text-emerald-700">Arahkan</span>
                </a>
            </div>
        </div>
    </div>
</template>
