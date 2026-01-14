<script setup>
/**
 * DetailFullInfo.vue - Location & Contact Info Section
 * Cleaned up: Removed duplicate pricing and stats (now in sidebar)
 * Focuses only on address, coordinates, and operational info
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { MapPin, Clock, Phone, Mail, Globe, Calendar, Copy, Navigation, ExternalLink, Building, Map } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ destination: { type: Object, required: true } });

const sectionRef = ref(null);
const addressCopied = ref(false);
const coordsCopied = ref(false);
let ctx;

const lat = computed(() => parseFloat(props.destination.latitude) || 0);
const lng = computed(() => parseFloat(props.destination.longitude) || 0);
const fullAddress = computed(() => props.destination.address || `${props.destination.city || ''}, Sulawesi Tengah`);

const copyAddress = async () => { try { await navigator.clipboard.writeText(fullAddress.value); addressCopied.value = true; setTimeout(() => addressCopied.value = false, 2000); } catch(e){} };
const copyCoords = async () => { try { await navigator.clipboard.writeText(`${lat.value}, ${lng.value}`); coordsCopied.value = true; setTimeout(() => coordsCopied.value = false, 2000); } catch(e){} };

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.info-card', { opacity: 0, y: 25, scale: 0.98 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'power3.out', scrollTrigger: { trigger: sectionRef.value, start: 'top 85%' } });
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="sectionRef">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Location Card -->
            <div class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <MapPin class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Lokasi</h3>
                            <p class="text-[10px] text-gray-500">Alamat & koordinat</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <!-- Address -->
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <Building class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] text-gray-700 font-medium leading-relaxed">{{ fullAddress }}</p>
                        </div>
                        <button @click="copyAddress" class="px-2 py-1 rounded-lg bg-white border border-gray-200 text-[9px] font-semibold text-gray-600 hover:bg-gray-100 transition-colors flex-shrink-0">
                            {{ addressCopied ? '✓ Tersalin' : 'Salin' }}
                        </button>
                    </div>

                    <!-- Coordinates -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <Map class="w-4 h-4 text-gray-400 flex-shrink-0" />
                        <div class="flex-1">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Koordinat</p>
                            <p class="text-[11px] font-mono text-gray-600">{{ lat.toFixed(6) }}, {{ lng.toFixed(6) }}</p>
                        </div>
                        <button @click="copyCoords" class="px-2 py-1 rounded-lg bg-white border border-gray-200 text-[9px] font-semibold text-gray-600 hover:bg-gray-100 transition-colors flex-shrink-0">
                            {{ coordsCopied ? '✓ Tersalin' : 'Salin' }}
                        </button>
                    </div>

                    <!-- City -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <Globe class="w-4 h-4 text-gray-400 flex-shrink-0" />
                        <div>
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Kota</p>
                            <p class="text-[11px] font-semibold text-gray-700">{{ destination.city }}</p>
                        </div>
                    </div>

                    <!-- Map Actions -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a :href="`https://www.google.com/maps?q=${lat},${lng}`" target="_blank" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-semibold transition-colors">
                            <ExternalLink class="w-3.5 h-3.5" />
                            Buka di Maps
                        </a>
                        <a :href="`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`" target="_blank" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[10px] font-bold shadow-lg shadow-emerald-500/25 transition-all">
                            <Navigation class="w-3.5 h-3.5" />
                            Petunjuk Arah
                        </a>
                    </div>
                </div>
            </div>

            <!-- Operational Info Card -->
            <div class="info-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <Clock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Info Operasional</h3>
                        <p class="text-[10px] text-gray-500">Jam buka & kontak</p>
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <!-- Operating Hours -->
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center gap-2 mb-1">
                            <Clock class="w-4 h-4 text-blue-500" />
                            <span class="text-[9px] text-gray-400 uppercase tracking-wide font-medium">Jam Buka</span>
                        </div>
                        <p class="text-[11px] font-bold text-gray-900">{{ destination.operating_hours || '24 Jam' }}</p>
                        <span class="inline-flex items-center gap-1 mt-1 text-[9px] font-medium text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Buka Sekarang
                        </span>
                    </div>

                    <!-- Best Visit Time -->
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center gap-2 mb-1">
                            <Calendar class="w-4 h-4 text-amber-500" />
                            <span class="text-[9px] text-gray-400 uppercase tracking-wide font-medium">Waktu Terbaik</span>
                        </div>
                        <p class="text-[11px] font-bold text-gray-900">Sabtu - Minggu</p>
                        <span class="text-[9px] text-gray-500">Cuaca cerah</span>
                    </div>

                    <!-- Contact Grid -->
                    <div class="grid grid-cols-1 gap-2">
                        <!-- Phone -->
                        <div v-if="destination.contact_phone" class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <div class="flex items-center gap-2 mb-1">
                                <Phone class="w-4 h-4 text-emerald-500" />
                                <span class="text-[9px] text-gray-400 uppercase tracking-wide font-medium">Telepon</span>
                            </div>
                            <a :href="`tel:${destination.contact_phone}`" class="text-[11px] font-bold text-emerald-600 hover:underline">{{ destination.contact_phone }}</a>
                        </div>

                        <!-- Email -->
                        <div v-if="destination.contact_email" class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <div class="flex items-center gap-2 mb-1">
                                <Mail class="w-4 h-4 text-purple-500" />
                                <span class="text-[9px] text-gray-400 uppercase tracking-wide font-medium">Email</span>
                            </div>
                            <a :href="`mailto:${destination.contact_email}`" class="text-[11px] font-bold text-purple-600 hover:underline truncate block">{{ destination.contact_email }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

