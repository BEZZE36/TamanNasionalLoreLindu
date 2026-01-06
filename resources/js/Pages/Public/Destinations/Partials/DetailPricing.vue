<script setup>
/**
 * DetailPricing.vue - Separate Ticket & Parking Fees Cards
 * Two cards: one for tickets, one for parking
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Ticket, Car, Bike, Truck, Bus, Check, Info } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ destination: { type: Object, required: true } });

const sectionRef = ref(null);
let ctx;

const formatPrice = (p) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p || 0);

// Icons for parking
const parkingIcons = { motor: Bike, mobil: Car, bus: Bus, truk: Truck, default: Car };
const getParkingIcon = (name) => {
    const lower = name?.toLowerCase() || '';
    for (const [key, icon] of Object.entries(parkingIcons)) { if (lower.includes(key)) return icon; }
    return parkingIcons.default;
};

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.price-card', { opacity: 0, y: 20, scale: 0.97 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'power3.out', scrollTrigger: { trigger: sectionRef.value, start: 'top 85%' } });
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="sectionRef" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- TICKET PRICES Card -->
        <div class="price-card bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-emerald-500 to-teal-500">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-sm">Harga Tiket Masuk</h3>
                        <p class="text-white/70 text-[10px]">Harga tiket pengunjung</p>
                    </div>
                </div>
            </div>
            
            <!-- Prices List -->
            <div class="p-4 space-y-2.5">
                <div v-for="(price, i) in destination.prices" :key="price.id || i" class="group flex items-center justify-between py-3 px-4 rounded-xl bg-gradient-to-r from-gray-50 to-gray-100/50 hover:from-emerald-50 hover:to-teal-50/50 border border-gray-100 hover:border-emerald-200 transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                            <Check class="w-4 h-4 text-emerald-500" />
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-800 group-hover:text-emerald-700 transition-colors">{{ price.ticket_type || price.label || price.type }}</p>
                            <p v-if="price.description" class="text-[9px] text-gray-400">{{ price.description }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ formatPrice(price.price) }}</span>
                </div>
                
                <!-- Fallback if no prices -->
                <div v-if="!destination.prices?.length" class="text-center py-6 text-gray-400 text-[11px]">
                    <Info class="w-6 h-6 mx-auto mb-2 text-gray-200" />
                    Harga tiket belum tersedia
                </div>
            </div>
            
            <!-- Book CTA -->
            <div class="p-4 pt-0">
                <Link :href="`/book/${destination.slug}`" class="w-full flex items-center justify-center gap-2 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/25 transition-all duration-300 group">
                    <Ticket class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                    Pesan Tiket Sekarang
                </Link>
            </div>
        </div>

        <!-- PARKING FEES Card -->
        <div class="price-card bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-blue-500 to-cyan-500">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <Car class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-sm">Biaya Parkir</h3>
                        <p class="text-white/70 text-[10px]">Tarif parkir kendaraan</p>
                    </div>
                </div>
            </div>
            
            <!-- Parking List -->
            <div class="p-4 space-y-2.5">
                <div v-for="(fee, i) in destination.parking_fees" :key="i" class="group flex items-center justify-between py-3 px-4 rounded-xl bg-gradient-to-r from-gray-50 to-gray-100/50 hover:from-blue-50 hover:to-cyan-50/50 border border-gray-100 hover:border-blue-200 transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                            <component :is="getParkingIcon(fee.vehicle_type)" class="w-4 h-4 text-blue-500" />
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">{{ fee.vehicle_type }}</p>
                            <p v-if="fee.description" class="text-[9px] text-gray-400">{{ fee.description }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ formatPrice(fee.price) }}</span>
                </div>
                
                <!-- Fallback if no parking fees -->
                <div v-if="!destination.parking_fees?.length" class="text-center py-6 text-gray-400 text-[11px]">
                    <Car class="w-6 h-6 mx-auto mb-2 text-gray-200" />
                    Biaya parkir belum tersedia
                </div>

                <!-- Info Note -->
                <div v-if="destination.parking_fees?.length" class="flex items-center gap-2 px-3 py-2 mt-2 rounded-lg bg-blue-50 border border-blue-100">
                    <Info class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" />
                    <p class="text-[9px] text-blue-700">Pembayaran parkir langsung di lokasi</p>
                </div>
            </div>
        </div>
    </section>
</template>
