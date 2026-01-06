<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Star, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    flora: {
        type: Object,
        required: true
    },
    index: {
        type: Number,
        default: 0
    }
});

// Category badge color
const categoryBadgeClass = computed(() => {
    switch (props.flora.category) {
        case 'endemik':
            return 'bg-purple-500 text-white';
        case 'langka':
            return 'bg-amber-500 text-white';
        default:
            return 'bg-green-500 text-white';
    }
});

// Animation delay
const animationDelay = computed(() => `${props.index * 0.05}s`);
</script>

<template>
    <Link 
        :href="`/flora/${flora.slug || flora.id}`"
        class="group block bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-500 hover:-translate-y-2 border border-gray-100"
        :style="{ animationDelay }"
    >
        <!-- Image -->
        <div class="relative h-56 overflow-hidden">
            <img 
                :src="flora.image_url || '/images/placeholder-no-image.svg'"
                :alt="flora.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                loading="lazy"
                @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

            <!-- Category Badge -->
            <div class="absolute top-4 left-4">
                <span :class="['px-3 py-1 rounded-full text-xs font-bold', categoryBadgeClass]">
                    {{ flora.category_label }}
                </span>
            </div>

            <!-- Featured Badge -->
            <div v-if="flora.is_featured" class="absolute top-4 right-4">
                <span class="w-8 h-8 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                    <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="p-5">
            <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-green-600 transition-colors">
                {{ flora.name }}
            </h3>
            <p v-if="flora.local_name" class="text-sm text-gray-500 mb-1">{{ flora.local_name }}</p>
            <p v-if="flora.scientific_name" class="text-xs text-gray-400 italic mb-3">{{ flora.scientific_name }}</p>
            <p class="text-sm text-gray-600 line-clamp-3">{{ flora.description?.substring(0, 100) }}...</p>
            
            <span class="inline-flex items-center gap-1 text-green-600 text-sm font-medium mt-3">
                Lihat Detail
                <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </span>
        </div>
    </Link>
</template>

<style scoped>
a {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
