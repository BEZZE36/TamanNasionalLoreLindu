<script setup>
import { computed } from 'vue';
import { Star, MapPin } from 'lucide-vue-next';

const props = defineProps({ feedback: { type: Object, required: true }, index: { type: Number, default: 0 } });
const animationDelay = computed(() => `${props.index * 0.05}s`);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
</script>

<template>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1" :style="{ animationDelay }">
        <div class="flex items-start gap-4 mb-4">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-100 to-fuchsia-200 flex items-center justify-center text-fuchsia-600 font-bold text-lg flex-shrink-0">
                {{ feedback.is_anonymous ? '?' : (feedback.user?.name?.[0] || feedback.display_name?.[0] || 'A') }}
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-900">{{ feedback.is_anonymous ? 'Anonim' : (feedback.user?.name || feedback.display_name || 'Pengunjung') }}</h3>
                <p class="text-sm text-gray-500">{{ formatDate(feedback.created_at) }}</p>
            </div>
            <div class="flex">
                <Star v-for="i in 5" :key="i" :class="['w-4 h-4', i <= feedback.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200']" />
            </div>
        </div>
        
        <p class="text-gray-700 mb-4 line-clamp-4">{{ feedback.message }}</p>
        
        <div v-if="feedback.destination" class="flex items-center gap-2 text-sm text-gray-500">
            <MapPin class="w-4 h-4 text-fuchsia-500" />
            <span>{{ feedback.destination.name }}</span>
        </div>
    </div>
</template>

<style scoped>
div { animation: fade-in-up 0.6s ease-out forwards; opacity: 0; }
@keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); }}
</style>
