<script setup>
/**
 * FaunaCard.vue - Premium Card with Hover Effects
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Star, ArrowRight, Bird, Rabbit, Fish, Eye, Shield } from 'lucide-vue-next';

const props = defineProps({
    fauna: { type: Object, required: true },
    index: { type: Number, default: 0 }
});

const categoryConfig = computed(() => {
    switch (props.fauna.category) {
        case 'mamalia':
            return { bg: 'bg-gradient-to-r from-amber-500 to-orange-500', text: 'text-white', icon: Rabbit, label: props.fauna.category_label || 'Mamalia' };
        case 'burung':
            return { bg: 'bg-gradient-to-r from-sky-500 to-blue-500', text: 'text-white', icon: Bird, label: props.fauna.category_label || 'Burung' };
        case 'reptil':
            return { bg: 'bg-gradient-to-r from-green-500 to-emerald-500', text: 'text-white', icon: Fish, label: props.fauna.category_label || 'Reptil' };
        default:
            return { bg: 'bg-gradient-to-r from-gray-500 to-slate-500', text: 'text-white', icon: Bird, label: props.fauna.category_label || 'Lainnya' };
    }
});

const conservationBadgeClass = computed(() => {
    const status = props.fauna.conservation_status?.toLowerCase();
    if (status?.includes('kritis') || status?.includes('critically')) return 'bg-red-500 text-white';
    if (status?.includes('genting') || status?.includes('endangered')) return 'bg-orange-500 text-white';
    if (status?.includes('rentan') || status?.includes('vulnerable')) return 'bg-amber-500 text-white';
    return 'bg-gray-100 text-gray-600';
});

const animationDelay = computed(() => `${props.index * 0.05}s`);
</script>

<template>
    <Link :href="`/fauna/${fauna.slug || fauna.id}`" class="fauna-card group block" :style="{ animationDelay }">
        <div class="relative bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-md group-hover:shadow-2xl border border-gray-100 group-hover:border-amber-200 transition-all duration-500 group-hover:-translate-y-1.5">
            <div class="relative h-44 sm:h-52 overflow-hidden">
                <img :src="fauna.image_url || '/images/placeholder-no-image.svg'" :alt="fauna.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" loading="lazy" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                <div class="absolute top-2 sm:top-3 left-2 sm:left-3 flex flex-wrap gap-1">
                    <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[9px] sm:text-[10px] font-bold shadow-lg', categoryConfig.bg, categoryConfig.text]">
                        <component :is="categoryConfig.icon" class="w-2.5 h-2.5 sm:w-3 sm:h-3" />{{ categoryConfig.label }}
                    </span>
                    <span v-if="fauna.conservation_status" :class="['inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[9px] sm:text-[10px] font-bold shadow-lg', conservationBadgeClass]">
                        <Shield class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                    </span>
                </div>

                <div v-if="fauna.is_featured" class="absolute top-2 sm:top-3 right-2 sm:right-3">
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <Star class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-yellow-400 fill-yellow-400" />
                    </span>
                </div>

                <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                    <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-amber-200 transition-colors line-clamp-1 mb-0.5">{{ fauna.name }}</h3>
                    <p v-if="fauna.scientific_name" class="text-[10px] sm:text-[11px] text-white/70 italic line-clamp-1">{{ fauna.scientific_name }}</p>
                </div>
            </div>

            <div class="p-3 sm:p-4">
                <p v-if="fauna.local_name" class="text-[10px] sm:text-[11px] text-gray-500 mb-2">{{ fauna.local_name }}</p>
                <p class="text-[11px] sm:text-xs text-gray-600 line-clamp-2 mb-3 leading-relaxed">{{ fauna.description?.substring(0, 100) }}...</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-[9px] sm:text-[10px] text-gray-400">
                        <span v-if="fauna.view_count" class="flex items-center gap-0.5"><Eye class="w-3 h-3" />{{ fauna.view_count }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1 text-amber-600 text-[10px] sm:text-[11px] font-semibold group-hover:gap-2 transition-all">Detail<ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5" /></span>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.fauna-card { animation: fade-in-up 0.5s ease-out forwards; opacity: 0; }
@keyframes fade-in-up { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
