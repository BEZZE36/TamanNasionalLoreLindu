<script setup>
/**
 * DetailBookingSidebar.vue - Unified Sticky Booking Sidebar
 * Combines ticket pricing, parking fees, and quick stats
 * Sticky position for always visible booking CTA
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Ticket, Car, Bike, Bus, Truck, Star, Users, Check, AlertTriangle, Info, Sparkles } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

const sidebarRef = ref(null);
let ctx;

const formatPrice = (p) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p || 0);

// Icons for parking
const parkingIcons = { motor: Bike, mobil: Car, bus: Bus, truk: Truck, default: Car };
const getParkingIcon = (name) => {
    const lower = name?.toLowerCase() || '';
    for (const [key, icon] of Object.entries(parkingIcons)) { if (lower.includes(key)) return icon; }
    return parkingIcons.default;
};

// Get starting price
const startingPrice = computed(() => {
    if (props.destination.prices?.length) {
        const prices = props.destination.prices.map(p => p.price || 0);
        return Math.min(...prices);
    }
    return props.destination.adult_price || 0;
});

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo(sidebarRef.value, { opacity: 0, y: 20, scale: 0.98 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, ease: 'power3.out', delay: 0.3 });
    }, sidebarRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div ref="sidebarRef" class="lg:sticky lg:top-24 space-y-4">
        <!-- Main Booking Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header with Starting Price -->
            <div class="px-5 py-4 bg-gradient-to-r from-emerald-500 to-teal-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                            <Ticket class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <p class="text-white/70 text-[9px] uppercase tracking-wider font-medium">Mulai dari</p>
                            <p class="text-white font-black text-xl">{{ formatPrice(startingPrice) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/20 backdrop-blur-sm">
                        <Star class="w-4 h-4 text-amber-300 fill-amber-300" />
                        <span class="text-white font-bold text-sm">{{ (parseFloat(destination.rating) || 4.8).toFixed(1) }}</span>
                    </div>
                </div>
            </div>

            <!-- Ticket Prices -->
            <div class="p-4 border-b border-gray-100">
                <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <Ticket class="w-3.5 h-3.5 text-emerald-500" />
                    Harga Tiket Masuk
                </h4>
                <div class="space-y-2">
                    <div v-for="(price, i) in destination.prices" :key="price.id || i" class="flex items-center justify-between py-2.5 px-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center">
                                <Check class="w-3.5 h-3.5 text-emerald-500" />
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold text-gray-700">{{ price.ticket_type || price.label || price.type }}</p>
                                <p v-if="price.description" class="text-[9px] text-gray-400">{{ price.description }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-black text-emerald-600">{{ formatPrice(price.price) }}</span>
                    </div>
                    <!-- Fallback if no prices -->
                    <div v-if="!destination.prices?.length" class="text-center py-4 text-gray-400 text-[11px]">
                        <Info class="w-5 h-5 mx-auto mb-1 text-gray-200" />
                        Harga tiket belum tersedia
                    </div>
                </div>
            </div>

            <!-- Parking Fees -->
            <div v-if="destination.parking_fees?.length" class="p-4 border-b border-gray-100">
                <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <Car class="w-3.5 h-3.5 text-blue-500" />
                    Biaya Parkir
                </h4>
                <div class="space-y-2">
                    <div v-for="(fee, i) in destination.parking_fees" :key="i" class="flex items-center justify-between py-2.5 px-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 hover:border-blue-200 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center">
                                <component :is="getParkingIcon(fee.vehicle_type)" class="w-3.5 h-3.5 text-blue-500" />
                            </div>
                            <p class="text-[11px] font-semibold text-gray-700">{{ fee.vehicle_type }}</p>
                        </div>
                        <span class="text-sm font-black text-blue-600">{{ formatPrice(fee.price) }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-3 py-2 mt-3 rounded-lg bg-blue-50 border border-blue-100">
                    <Info class="w-3 h-3 text-blue-500 flex-shrink-0" />
                    <p class="text-[9px] text-blue-700">Bayar langsung di lokasi</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="p-4 border-b border-gray-100">
                <div class="grid grid-cols-3 gap-2">
                    <div class="text-center p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center mx-auto mb-1.5">
                            <Star class="w-3.5 h-3.5 text-amber-500" />
                        </div>
                        <p class="text-[11px] font-bold text-gray-900">{{ (parseFloat(destination.rating) || 4.8).toFixed(1) }}</p>
                        <p class="text-[8px] text-gray-400 uppercase tracking-wider">Rating</p>
                    </div>
                    <div class="text-center p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center mx-auto mb-1.5">
                            <Users class="w-3.5 h-3.5 text-blue-500" />
                        </div>
                        <p class="text-[11px] font-bold text-gray-900">{{ destination.review_count || 0 }}</p>
                        <p class="text-[8px] text-gray-400 uppercase tracking-wider">Ulasan</p>
                    </div>
                    <div class="text-center p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center mx-auto mb-1.5">
                            <Check class="w-3.5 h-3.5 text-emerald-500" />
                        </div>
                        <p class="text-[11px] font-bold text-gray-900">{{ destination.facilities?.length || 0 }}</p>
                        <p class="text-[8px] text-gray-400 uppercase tracking-wider">Fasilitas</p>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="p-4">
                <Link :href="`/book/${destination.slug}`" class="w-full flex items-center justify-center gap-2.5 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-500/25 transition-all duration-300 group">
                    <Sparkles class="w-5 h-5 group-hover:rotate-12 transition-transform" />
                    Pesan Tiket Sekarang
                </Link>
                <p class="text-center text-[9px] text-gray-400 mt-2.5">Pemesanan online cepat & mudah</p>
            </div>
        </div>

        <!-- Rules Quick Note -->
        <div v-if="destination.rules?.length" class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-lg border border-amber-200/50 p-4">
            <div class="flex items-center gap-2.5 mb-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-md">
                    <AlertTriangle class="w-4 h-4 text-white" />
                </div>
                <h4 class="text-[11px] font-bold text-gray-900">Perhatian</h4>
            </div>
            <p class="text-[10px] text-amber-800 leading-relaxed">
                Terdapat {{ destination.rules.length }} peraturan yang berlaku. Harap patuhi untuk kenyamanan bersama.
            </p>
        </div>
    </div>
</template>
