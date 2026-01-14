<script setup>
/**
 * DetailBooking.vue - Booking Widget (Sidebar)
 * Design system: text-xs CTA, text-[11px] info
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Ticket, Clock, Users, Check, Shield, Award } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

const formatPrice = (price) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price || 0);
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-24">
        <!-- Header -->
        <div class="px-4 py-4 bg-gradient-to-r from-emerald-500 to-teal-500">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-sm">Tiket Masuk</h3>
                        <p class="text-white/70 text-[9px]">Pesan online, lebih mudah</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prices -->
        <div class="p-4 border-b border-gray-100">
            <div class="space-y-2">
                <div v-for="(price, index) in destination.prices?.slice(0, 4)" :key="price.id || index" class="flex items-center justify-between py-2 px-3 rounded-lg bg-gray-50 border border-gray-100">
                    <span class="text-[11px] text-gray-700 font-medium">{{ price.type || price.name }}</span>
                    <span class="text-sm font-bold text-emerald-600">{{ formatPrice(price.price) }}</span>
                </div>
                <div v-if="!destination.prices?.length" class="flex items-center justify-between py-2 px-3 rounded-lg bg-gray-50 border border-gray-100">
                    <span class="text-[11px] text-gray-700 font-medium">Tiket Dewasa</span>
                    <span class="text-sm font-bold text-emerald-600">{{ formatPrice(destination.adult_price || 10000) }}</span>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="p-4 border-b border-gray-100">
            <Link :href="`/book/${destination.slug}`" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/25 transition-all">
                <Ticket class="w-4 h-4" />
                Pesan Tiket Sekarang
            </Link>
        </div>

        <!-- Quick Info -->
        <div class="p-4 border-b border-gray-100">
            <div class="space-y-2.5">
                <div class="flex items-center gap-2">
                    <Clock class="w-3.5 h-3.5 text-blue-500" />
                    <span class="text-[11px] text-gray-600">Buka {{ destination.operating_hours || '24 Jam' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <Users class="w-3.5 h-3.5 text-purple-500" />
                    <span class="text-[11px] text-gray-600">Cocok untuk keluarga</span>
                </div>
                <div class="flex items-center gap-2">
                    <Check class="w-3.5 h-3.5 text-emerald-500" />
                    <span class="text-[11px] text-gray-600">Konfirmasi instan</span>
                </div>
            </div>
        </div>

        <!-- Trust Badges -->
        <div class="p-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100">
                    <Shield class="w-3.5 h-3.5 text-emerald-600" />
                    <span class="text-[9px] font-semibold text-emerald-700">Aman</span>
                </div>
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-amber-50 border border-amber-100">
                    <Award class="w-3.5 h-3.5 text-amber-600" />
                    <span class="text-[9px] font-semibold text-amber-700">Resmi</span>
                </div>
            </div>
        </div>
    </div>
</template>
