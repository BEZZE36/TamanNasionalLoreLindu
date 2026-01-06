<script setup>
/**
 * DetailQuickInfo.vue - Horizontal Scroll Info Cards
 * Design system: text-lg/text-xl stats, text-[10px] labels
 */
import { Clock, Star, Ticket, Wifi } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

const formatPrice = (price) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price || 0);
const lowestPrice = props.destination.prices?.[0]?.price || props.destination.adult_price || 0;
</script>

<template>
    <section class="py-4 -mt-8 relative z-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="overflow-x-auto scrollbar-hide -mx-4 px-4 sm:mx-0 sm:px-0">
                <div class="flex gap-3 min-w-max sm:grid sm:grid-cols-4 sm:min-w-0">
                    <!-- Operating Hours -->
                    <div class="group flex-shrink-0 w-[160px] sm:w-auto p-4 rounded-xl bg-white shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-cyan-100 flex items-center justify-center">
                                <Clock class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-lg sm:text-xl font-black text-gray-900">{{ destination.operating_hours || '24 Jam' }}</p>
                                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wide">Jam Buka</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="group flex-shrink-0 w-[160px] sm:w-auto p-4 rounded-xl bg-white shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                                <Star class="w-5 h-5 text-amber-600 fill-amber-500" />
                            </div>
                            <div>
                                <p class="text-lg sm:text-xl font-black text-gray-900">{{ (parseFloat(destination.rating) || 4.8).toFixed(1) }}</p>
                                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wide">{{ destination.review_count || 0 }} Ulasan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="group flex-shrink-0 w-[160px] sm:w-auto p-4 rounded-xl bg-white shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                <Ticket class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-base sm:text-lg font-black text-emerald-600">{{ formatPrice(lowestPrice) }}</p>
                                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wide">Mulai dari</p>
                            </div>
                        </div>
                    </div>

                    <!-- Facilities Count -->
                    <div class="group flex-shrink-0 w-[160px] sm:w-auto p-4 rounded-xl bg-white shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center">
                                <Wifi class="w-5 h-5 text-purple-600" />
                            </div>
                            <div>
                                <p class="text-lg sm:text-xl font-black text-gray-900">{{ destination.facilities?.length || 0 }}</p>
                                <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wide">Fasilitas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
</style>
