<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Star, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    fauna: { type: Object, required: true },
    index: { type: Number, default: 0 }
});

const categoryBadgeClass = computed(() => {
    switch (props.fauna.category) {
        case 'mamalia': return 'bg-amber-500 text-white';
        case 'burung': return 'bg-sky-500 text-white';
        case 'reptil': return 'bg-green-600 text-white';
        case 'amfibi': return 'bg-teal-500 text-white';
        case 'ikan': return 'bg-blue-500 text-white';
        case 'serangga': return 'bg-purple-500 text-white';
        default: return 'bg-gray-500 text-white';
    }
});

const animationDelay = computed(() => `${props.index * 0.05}s`);
</script>

<template>
    <Link :href="`/fauna/${fauna.slug || fauna.id}`"
        class="group block bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-500 hover:-translate-y-2 border border-gray-100"
        :style="{ animationDelay }">
        <div class="relative h-56 overflow-hidden">
            <img :src="fauna.image_url || '/images/placeholder-no-image.svg'" :alt="fauna.name"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" loading="lazy"
                @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

            <div class="absolute top-4 left-4">
                <span :class="['px-3 py-1 rounded-full text-xs font-bold', categoryBadgeClass]">{{ fauna.category_label }}</span>
            </div>

            <div v-if="fauna.is_featured" class="absolute top-4 right-4">
                <span class="w-8 h-8 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                    <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                </span>
            </div>

            <div v-if="fauna.conservation_status" class="absolute bottom-4 left-4">
                <span class="px-2 py-1 rounded-lg text-xs font-medium bg-red-500/80 text-white backdrop-blur">
                    {{ fauna.conservation_status_label || fauna.conservation_status }}
                </span>
            </div>
        </div>

        <div class="p-5">
            <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-amber-600 transition-colors">{{ fauna.name }}</h3>
            <p v-if="fauna.local_name" class="text-sm text-gray-500 mb-1">{{ fauna.local_name }}</p>
            <p v-if="fauna.scientific_name" class="text-xs text-gray-400 italic mb-3">{{ fauna.scientific_name }}</p>
            <p class="text-sm text-gray-600 line-clamp-3">{{ fauna.description?.substring(0, 100) }}...</p>
            <span class="inline-flex items-center gap-1 text-amber-600 text-sm font-medium mt-3">
                Lihat Detail <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </span>
        </div>
    </Link>
</template>

<style scoped>
a { animation: fade-in-up 0.6s ease-out forwards; opacity: 0; }
@keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); }}
</style>
