<script setup>
/**
 * FloraCard.vue - Premium Card with Hover Effects
 * Design: Modern card with animations, category badges, hover effects
 * Sizing: text-[11px] titles, text-[9px] badges
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Star, ArrowRight, Leaf, Sparkles, Flower2, Eye, MapPin } from 'lucide-vue-next';

const props = defineProps({
    flora: { type: Object, required: true },
    index: { type: Number, default: 0 }
});

// Category badge styling
const categoryConfig = computed(() => {
    switch (props.flora.category) {
        case 'endemik':
            return { 
                bg: 'bg-gradient-to-r from-purple-500 to-violet-500', 
                text: 'text-white',
                icon: Sparkles,
                label: props.flora.category_label || 'Endemik'
            };
        case 'langka':
            return { 
                bg: 'bg-gradient-to-r from-amber-500 to-orange-500', 
                text: 'text-white',
                icon: Flower2,
                label: props.flora.category_label || 'Langka'
            };
        default:
            return { 
                bg: 'bg-gradient-to-r from-emerald-500 to-green-500', 
                text: 'text-white',
                icon: Leaf,
                label: props.flora.category_label || 'Umum'
            };
    }
});

const animationDelay = computed(() => `${props.index * 0.05}s`);
</script>

<template>
    <Link 
        :href="`/flora/${flora.slug || flora.id}`"
        class="flora-card group block"
        :style="{ animationDelay }"
    >
        <div class="relative bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-md group-hover:shadow-2xl border border-gray-100 group-hover:border-emerald-200 transition-all duration-500 group-hover:-translate-y-1.5">
            <!-- Image -->
            <div class="relative h-44 sm:h-52 overflow-hidden">
                <img 
                    :src="flora.image_url || '/images/placeholder-no-image.svg'"
                    :alt="flora.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                    loading="lazy"
                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                <!-- Category Badge -->
                <div class="absolute top-2 sm:top-3 left-2 sm:left-3">
                    <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[9px] sm:text-[10px] font-bold shadow-lg', categoryConfig.bg, categoryConfig.text]">
                        <component :is="categoryConfig.icon" class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                        {{ categoryConfig.label }}
                    </span>
                </div>

                <!-- Featured Badge -->
                <div v-if="flora.is_featured" class="absolute top-2 sm:top-3 right-2 sm:right-3">
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <Star class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-yellow-400 fill-yellow-400" />
                    </span>
                </div>

                <!-- Bottom Gradient Content -->
                <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                    <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-emerald-200 transition-colors line-clamp-1 mb-0.5">
                        {{ flora.name }}
                    </h3>
                    <p v-if="flora.scientific_name" class="text-[10px] sm:text-[11px] text-white/70 italic line-clamp-1">
                        {{ flora.scientific_name }}
                    </p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-3 sm:p-4">
                <!-- Local Name -->
                <p v-if="flora.local_name" class="text-[10px] sm:text-[11px] text-gray-500 mb-2 flex items-center gap-1">
                    <MapPin class="w-3 h-3" />
                    {{ flora.local_name }}
                </p>

                <!-- Description -->
                <p class="text-[11px] sm:text-xs text-gray-600 line-clamp-2 mb-3 leading-relaxed">
                    {{ flora.description?.substring(0, 100) }}...
                </p>

                <!-- Footer -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-[9px] sm:text-[10px] text-gray-400">
                        <span v-if="flora.view_count" class="flex items-center gap-0.5">
                            <Eye class="w-3 h-3" />
                            {{ flora.view_count }}
                        </span>
                    </div>
                    
                    <span class="inline-flex items-center gap-1 text-emerald-600 text-[10px] sm:text-[11px] font-semibold group-hover:gap-2 transition-all">
                        Detail
                        <ArrowRight class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.flora-card {
    animation: fade-in-up 0.5s ease-out forwards;
    opacity: 0;
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
