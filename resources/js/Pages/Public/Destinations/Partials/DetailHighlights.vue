<script setup>
/**
 * DetailHighlights.vue - Destination Highlights Grid
 * Design system: text-[11px] labels, w-4/w-5 icons
 */
import { Mountain, Camera, Footprints, Bird, Tent, Waves, Users, Accessibility } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

// Default highlights if not available
const highlights = [
    { icon: Mountain, label: 'Pemandangan Alam', color: 'from-emerald-100 to-teal-100', iconColor: 'text-emerald-600' },
    { icon: Camera, label: 'Spot Foto', color: 'from-purple-100 to-pink-100', iconColor: 'text-purple-600' },
    { icon: Footprints, label: 'Trekking', color: 'from-amber-100 to-orange-100', iconColor: 'text-amber-600' },
    { icon: Bird, label: 'Wildlife', color: 'from-cyan-100 to-blue-100', iconColor: 'text-cyan-600' }
];
</script>

<template>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                <Mountain class="w-4 h-4 text-amber-600" />
            </div>
            <h2 class="text-sm font-bold text-gray-900">Keunggulan</h2>
        </div>

        <!-- Grid -->
        <div class="p-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <div v-for="(highlight, idx) in highlights" :key="idx" class="group flex flex-col items-center gap-2 p-3 rounded-xl bg-gray-50 hover:bg-white hover:shadow-md border border-gray-100 transition-all cursor-default text-center">
                    <div :class="['w-10 h-10 rounded-lg bg-gradient-to-br flex items-center justify-center group-hover:scale-110 transition-transform', highlight.color]">
                        <component :is="highlight.icon" :class="['w-5 h-5', highlight.iconColor]" />
                    </div>
                    <span class="text-[11px] font-semibold text-gray-700">{{ highlight.label }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
