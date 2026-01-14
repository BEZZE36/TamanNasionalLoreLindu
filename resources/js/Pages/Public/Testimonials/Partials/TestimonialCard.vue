<script setup>
/**
 * TestimonialCard.vue - Premium Glassmorphism Card Design
 * Features: Hover effects, micro-animations, star rating, avatar gradient
 */
import { computed, ref, onMounted } from 'vue';
import { gsap } from 'gsap';
import { Star, MapPin, Quote, Calendar, User } from 'lucide-vue-next';

const props = defineProps({ 
    feedback: { type: Object, required: true }, 
    index: { type: Number, default: 0 } 
});

const cardRef = ref(null);

const animationDelay = computed(() => `${props.index * 0.08}s`);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
};

const getInitial = () => {
    if (props.feedback.is_anonymous) return '?';
    const name = props.feedback.user?.name || props.feedback.display_name || 'A';
    return name.charAt(0).toUpperCase();
};

const getName = () => {
    if (props.feedback.is_anonymous) return 'Anonim';
    return props.feedback.user?.name || props.feedback.display_name || 'Pengunjung';
};

// Avatar gradient based on first letter
const avatarGradient = computed(() => {
    const initial = getInitial();
    const gradients = {
        'A': 'from-pink-400 to-rose-500',
        'B': 'from-purple-400 to-indigo-500',
        'C': 'from-cyan-400 to-blue-500',
        'D': 'from-emerald-400 to-green-500',
        'E': 'from-amber-400 to-orange-500',
        'F': 'from-fuchsia-400 to-pink-500',
        'G': 'from-violet-400 to-purple-500',
        'H': 'from-teal-400 to-cyan-500',
        'I': 'from-rose-400 to-red-500',
        'J': 'from-blue-400 to-indigo-500',
        'K': 'from-green-400 to-emerald-500',
        'L': 'from-yellow-400 to-amber-500',
        'M': 'from-indigo-400 to-violet-500',
        'N': 'from-red-400 to-rose-500',
        'O': 'from-orange-400 to-yellow-500',
        'P': 'from-pink-500 to-fuchsia-500',
        '?': 'from-gray-400 to-slate-500',
    };
    return gradients[initial] || 'from-pink-400 to-fuchsia-500';
});

onMounted(() => {
    if (cardRef.value) {
        gsap.fromTo(cardRef.value,
            { opacity: 0, y: 30, scale: 0.95 },
            { 
                opacity: 1, 
                y: 0, 
                scale: 1,
                duration: 0.6, 
                delay: props.index * 0.08, 
                ease: 'power3.out' 
            }
        );
    }
});
</script>

<template>
    <div 
        ref="cardRef"
        class="group relative bg-white rounded-2xl shadow-lg border border-gray-100/80 p-4 sm:p-5 lg:p-6 hover:shadow-2xl hover:-translate-y-2 hover:border-pink-200/50 transition-all duration-500 cursor-default opacity-0"
    >
        <!-- Decorative Quote Icon -->
        <div class="absolute -top-3 -right-3 w-10 h-10 bg-gradient-to-br from-pink-500 to-fuchsia-600 rounded-xl flex items-center justify-center shadow-lg shadow-pink-500/30 opacity-0 group-hover:opacity-100 transition-all duration-300 transform rotate-12 group-hover:rotate-0">
            <Quote class="w-5 h-5 text-white" />
        </div>

        <!-- Header: Avatar + Info + Rating -->
        <div class="flex items-start gap-3 sm:gap-4 mb-3 sm:mb-4">
            <!-- Avatar -->
            <div :class="['w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-bold text-sm sm:text-lg flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform duration-300', avatarGradient]">
                {{ getInitial() }}
            </div>
            
            <!-- User Info -->
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-gray-900 text-sm sm:text-base truncate group-hover:text-pink-600 transition-colors">
                    {{ getName() }}
                </h3>
                <div class="flex items-center gap-1.5 text-gray-500 text-[10px] sm:text-xs">
                    <Calendar class="w-3 h-3" />
                    <span>{{ formatDate(feedback.created_at) }}</span>
                </div>
            </div>
            
            <!-- Star Rating -->
            <div class="flex items-center gap-0.5 flex-shrink-0">
                <Star 
                    v-for="i in 5" 
                    :key="i" 
                    :class="['w-3.5 h-3.5 sm:w-4 sm:h-4 transition-all duration-300', 
                             i <= feedback.rating 
                                 ? 'text-yellow-400 fill-yellow-400 group-hover:scale-110' 
                                 : 'text-gray-200']" 
                    :style="{ transitionDelay: `${i * 50}ms` }"
                />
            </div>
        </div>
        
        <!-- Message -->
        <p class="text-gray-700 text-xs sm:text-sm leading-relaxed mb-3 sm:mb-4 line-clamp-4 group-hover:text-gray-800 transition-colors">
            "{{ feedback.message }}"
        </p>
        
        <!-- Footer: Destination Badge -->
        <div v-if="feedback.destination" class="flex items-center gap-2 pt-3 sm:pt-4 border-t border-gray-100">
            <div class="inline-flex items-center gap-1.5 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-pink-50 to-fuchsia-50 border border-pink-100/50 text-pink-600 text-[10px] sm:text-xs font-medium group-hover:from-pink-100 group-hover:to-fuchsia-100 transition-colors">
                <MapPin class="w-3 h-3" />
                <span class="truncate max-w-[120px] sm:max-w-none">{{ feedback.destination.name }}</span>
            </div>
        </div>

        <!-- Hover Gradient Border Effect -->
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-pink-500/0 via-fuchsia-500/0 to-purple-500/0 group-hover:from-pink-500/5 group-hover:via-fuchsia-500/5 group-hover:to-purple-500/5 transition-all duration-500 pointer-events-none"></div>
    </div>
</template>
