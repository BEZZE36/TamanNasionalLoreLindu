<script setup>
import { Link } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';

defineProps({
    featuredFlora: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <section v-if="featuredFlora.length > 0" class="py-12 bg-green-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Flora Unggulan 🌸</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Link 
                    v-for="item in featuredFlora"
                    :key="item.id"
                    :href="`/flora/${item.slug || item.id}`"
                    class="bg-white rounded-2xl p-6 shadow-lg border border-green-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group"
                >
                    <div class="flex items-center gap-4">
                        <img 
                            :src="item.image_url || '/images/placeholder-no-image.svg'"
                            :alt="item.name"
                            class="w-20 h-20 rounded-xl object-cover group-hover:scale-105 transition-transform"
                            @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                        >
                        <div>
                            <h3 class="font-bold text-gray-900 group-hover:text-green-600 transition-colors">{{ item.name }}</h3>
                            <p v-if="item.scientific_name" class="text-sm text-gray-500 italic">{{ item.scientific_name }}</p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">
                                {{ item.category_label }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
