<script setup>
/**
 * BlogCard.vue - Premium Article Card with GSAP animations
 * Features: Glassmorphism, hover effects, author info, read time, views, likes
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Calendar, Clock, Eye, Heart, ArrowRight, User } from 'lucide-vue-next';

const props = defineProps({
    article: { type: Object, required: true },
    index: { type: Number, default: 0 }
});

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
const animationDelay = computed(() => `${props.index * 0.08}s`);
</script>

<template>
    <Link :href="`/blog/${article.slug}`" 
          class="article-card group block bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-cyan-200 shadow-lg hover:shadow-2xl hover:shadow-cyan-500/10 transition-all duration-500 hover:-translate-y-2" 
          :style="{ animationDelay }">
        <!-- Image Container -->
        <div class="relative h-44 sm:h-48 overflow-hidden">
            <img :src="article.image_url || '/images/placeholder-no-image.svg'" 
                 :alt="article.title"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" 
                 loading="lazy"
                 @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
            
            <!-- Category Badge -->
            <div class="absolute top-3 left-3">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/30">
                    {{ article.category_label || article.category || 'Artikel' }}
                </span>
            </div>

            <!-- Featured Badge (if is_featured) -->
            <div v-if="article.is_featured" class="absolute top-3 right-3">
                <span class="px-2 py-1 rounded-full text-[9px] font-bold bg-amber-500 text-white flex items-center gap-1">
                    ⭐ Unggulan
                </span>
            </div>

            <!-- Bottom Stats Overlay -->
            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span v-if="article.views_count" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-black/40 backdrop-blur-sm text-white/90 text-[9px]">
                        <Eye class="w-3 h-3" />{{ article.views_count }}
                    </span>
                    <span v-if="article.likes_count" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-black/40 backdrop-blur-sm text-white/90 text-[9px]">
                        <Heart class="w-3 h-3" />{{ article.likes_count }}
                    </span>
                </div>
                <span v-if="article.reading_time" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-black/40 backdrop-blur-sm text-white/90 text-[9px]">
                    <Clock class="w-3 h-3" />{{ article.reading_time }} min
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 sm:p-5">
            <!-- Title -->
            <h3 class="text-sm sm:text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-cyan-600 transition-colors leading-tight">
                {{ article.title }}
            </h3>
            
            <!-- Excerpt -->
            <p v-if="article.excerpt" class="text-xs sm:text-sm text-gray-500 line-clamp-2 mb-3 leading-relaxed">
                {{ article.excerpt }}
            </p>
            
            <!-- Meta Info -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <!-- Author & Date -->
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-cyan-100 to-blue-100 flex items-center justify-center">
                        <User class="w-3 h-3 text-cyan-600" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-semibold text-gray-700 line-clamp-1">{{ article.author_name || 'Admin' }}</span>
                        <span class="flex items-center gap-1 text-[9px] text-gray-400">
                            <Calendar class="w-2.5 h-2.5" />{{ formatDate(article.published_at) }}
                        </span>
                    </div>
                </div>
                
                <!-- Read More -->
                <span class="flex items-center gap-1 text-[10px] font-semibold text-cyan-600 group-hover:text-cyan-700">
                    Baca <ArrowRight class="w-3 h-3 group-hover:translate-x-1 transition-transform" />
                </span>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.article-card { 
    animation: fade-in-up 0.6s ease-out forwards; 
    opacity: 0; 
}
@keyframes fade-in-up { 
    from { opacity: 0; transform: translateY(20px); } 
    to { opacity: 1; transform: translateY(0); }
}
</style>
