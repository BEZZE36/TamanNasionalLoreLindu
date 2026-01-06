<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, ChevronRight } from 'lucide-vue-next';

defineProps({
    stats: { type: Array, required: true }
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
        <div 
            v-for="(stat, index) in stats" 
            :key="index"
            class="group relative overflow-hidden rounded-xl bg-white p-4 border border-gray-100 shadow-lg hover:shadow-xl transition-all duration-500 hover:-translate-y-1"
        >
            <div :class="['absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl rounded-bl-full transition-all group-hover:scale-110', stat.bgGradient]"></div>
            
            <div class="relative z-10 flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-[11px] font-medium text-gray-500">{{ stat.title }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ stat.value }}</p>
                    <div v-if="stat.subValue" class="flex items-center gap-1">
                        <div class="w-5 h-5 rounded-md bg-gray-100 flex items-center justify-center">
                            <ArrowUpRight class="w-3 h-3 text-gray-600" />
                        </div>
                        <span class="text-[10px] font-medium text-gray-600">{{ stat.subLabel }}: {{ stat.subValue }}</span>
                    </div>
                    <Link v-if="stat.linkHref" :href="stat.linkHref" class="inline-flex items-center gap-1 text-[10px] font-medium text-amber-600 hover:text-amber-700 transition-colors">
                        {{ stat.linkText }}
                        <ChevronRight class="w-3 h-3" />
                    </Link>
                </div>
                
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform bg-gradient-to-br', stat.gradient]">
                    <component :is="stat.icon" class="w-5 h-5 text-white" />
                </div>
            </div>
        </div>
    </div>
</template>
