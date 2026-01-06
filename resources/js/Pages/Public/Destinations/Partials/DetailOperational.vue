<script setup>
/**
 * DetailOperational.vue - Operational Info Grid
 * Design system: text-sm labels, text-[11px] values
 */
import { Clock, Calendar, Phone, Globe, Mail, MapPin } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

// Determine if currently open (simplified)
const isOpen = true; // In real app, check against operating hours
</script>

<template>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-100 to-blue-100 flex items-center justify-center">
                <Clock class="w-4 h-4 text-cyan-600" />
            </div>
            <h2 class="text-sm font-bold text-gray-900">Informasi Operasional</h2>
        </div>

        <!-- Grid -->
        <div class="p-4 grid grid-cols-2 gap-3">
            <!-- Operating Hours -->
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                    <Clock class="w-4 h-4 text-blue-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Jam Buka</p>
                    <p class="text-[11px] font-semibold text-gray-900 truncate">{{ destination.operating_hours || '24 Jam' }}</p>
                    <span :class="['inline-flex items-center gap-1 mt-1 text-[9px] font-medium', isOpen ? 'text-emerald-600' : 'text-red-600']">
                        <span :class="['w-1.5 h-1.5 rounded-full', isOpen ? 'bg-emerald-500' : 'bg-red-500']"></span>
                        {{ isOpen ? 'Buka Sekarang' : 'Tutup' }}
                    </span>
                </div>
            </div>

            <!-- Best Days -->
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                    <Calendar class="w-4 h-4 text-amber-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Hari Terbaik</p>
                    <p class="text-[11px] font-semibold text-gray-900">Sabtu - Minggu</p>
                    <span class="text-[9px] text-gray-500">Cuaca cerah</span>
                </div>
            </div>

            <!-- Contact Phone -->
            <div v-if="destination.contact_phone" class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                    <Phone class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Kontak</p>
                    <a :href="`tel:${destination.contact_phone}`" class="text-[11px] font-semibold text-emerald-600 hover:underline truncate block">{{ destination.contact_phone }}</a>
                </div>
            </div>

            <!-- Website -->
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                    <Globe class="w-4 h-4 text-purple-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] text-gray-400 uppercase tracking-wide font-medium mb-0.5">Website</p>
                    <a href="https://tnll.menlhk.go.id" target="_blank" class="text-[11px] font-semibold text-purple-600 hover:underline truncate block">tnll.menlhk.go.id</a>
                </div>
            </div>
        </div>
    </div>
</template>
