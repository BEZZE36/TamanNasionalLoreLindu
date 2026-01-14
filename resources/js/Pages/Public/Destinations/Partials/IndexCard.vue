<script setup>
/**
 * IndexCard.vue - Ultra Premium Destination Card
 * Enhanced 3D effects, magnetic hover, premium animations
 */
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { MapPin, Star, Heart, ArrowRight, TrendingUp, Clock } from 'lucide-vue-next';

const props = defineProps({
    destination: { type: Object, required: true },
    index: { type: Number, default: 0 },
    user: Object
});

const cardRef = ref(null);
const isHovered = ref(false);
const tiltStyle = ref({});
const glowPosition = ref({ x: 50, y: 50 });

const isWishlisted = computed(() => props.user?.wishlisted_destination_ids?.includes(props.destination.id));
const formattedPrice = computed(() => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(props.destination.prices?.[0]?.price || props.destination.adult_price || 0));

// Enhanced 3D Tilt with Glow Effect
const handleMouseMove = (e) => {
    if (!cardRef.value) return;
    const rect = cardRef.value.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const rotateX = ((y - rect.height / 2) / rect.height) * 12;
    const rotateY = ((rect.width / 2 - x) / rect.width) * 12;
    tiltStyle.value = { transform: `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)` };
    glowPosition.value = { x: (x / rect.width) * 100, y: (y / rect.height) * 100 };
};

const handleMouseLeave = () => {
    isHovered.value = false;
    tiltStyle.value = { transform: 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)' };
};

const toggleWishlist = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!props.user) { window.location.href = '/login'; return; }
    try {
        await fetch(`/wishlist/${props.destination.slug}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Content-Type': 'application/json' }
        });
        window.location.reload();
    } catch (e) {}
};
</script>

<template>
    <Link 
        :href="`/destinations/${destination.slug}`"
        ref="cardRef"
        class="group block"
        @mouseenter="isHovered = true"
        @mousemove="handleMouseMove"
        @mouseleave="handleMouseLeave"
    >
        <div class="relative h-[360px] sm:h-[380px] rounded-2xl overflow-hidden bg-white shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100/80" :style="tiltStyle">
            <!-- Dynamic Glow Effect -->
            <div v-if="isHovered" class="absolute inset-0 pointer-events-none z-10 opacity-50" :style="`background: radial-gradient(circle at ${glowPosition.x}% ${glowPosition.y}%, rgba(16,185,129,0.15), transparent 50%);`"></div>
            
            <!-- Image Section -->
            <div class="relative h-[55%] overflow-hidden">
                <img 
                    :src="destination.primary_image_url || '/images/placeholder-no-image.svg'" 
                    :alt="destination.name"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    loading="lazy"
                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                >
                
                <!-- Premium Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                
                <!-- Animated Corner Accents -->
                <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-500 delay-100">
                    <div class="w-8 h-px bg-gradient-to-l from-white/60 to-transparent transform origin-right group-hover:scale-x-100 scale-x-0 transition-transform duration-500"></div>
                    <div class="w-px h-8 bg-gradient-to-b from-white/60 to-transparent absolute top-0 right-0 transform origin-top group-hover:scale-y-100 scale-y-0 transition-transform duration-500 delay-75"></div>
                </div>

                <!-- Top Badges -->
                <div class="absolute top-3 left-3 flex flex-wrap items-center gap-1.5 z-20">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-black/40 backdrop-blur-md text-white text-[10px] font-semibold border border-white/10">
                        <MapPin class="w-2.5 h-2.5" />
                        {{ destination.category?.name || 'Wisata' }}
                    </span>
                    <span v-if="destination.is_featured" class="inline-flex items-center gap-0.5 px-2 py-1 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[9px] font-bold uppercase shadow-lg shadow-amber-500/30">
                        <TrendingUp class="w-2.5 h-2.5" />
                        Populer
                    </span>
                </div>

                <!-- Rating Badge -->
                <div class="absolute top-3 right-3 z-20">
                    <div class="flex items-center gap-1 px-2 py-1 rounded-lg bg-white/95 shadow-lg text-[10px] font-bold text-gray-900">
                        <Star class="w-3 h-3 text-amber-500 fill-amber-500" />
                        {{ destination.rating || '4.8' }}
                    </div>
                </div>

                <!-- Wishlist Button -->
                <button v-if="user" @click="toggleWishlist" class="absolute bottom-3 right-3 z-20 w-9 h-9 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white hover:bg-red-500/80 hover:border-red-400 transition-all duration-300 group/heart">
                    <Heart :class="['w-4 h-4 transition-all group-hover/heart:scale-110', isWishlisted ? 'fill-red-500 text-red-500' : 'fill-none']" />
                </button>

                <!-- Title Overlay -->
                <div class="absolute bottom-0 left-0 right-0 p-4">
                    <h3 class="text-base font-bold text-white line-clamp-1 mb-1 group-hover:text-emerald-200 transition-colors duration-300">
                        {{ destination.name }}
                    </h3>
                    <div class="flex items-center gap-2 text-white/70 text-[10px]">
                        <span class="flex items-center gap-1">
                            <MapPin class="w-3 h-3" />
                            {{ destination.city }}
                        </span>
                        <span v-if="destination.operating_hours" class="flex items-center gap-1">
                            <Clock class="w-3 h-3" />
                            {{ destination.operating_hours }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="p-4 h-[45%] flex flex-col">
                <!-- Description -->
                <p class="flex-1 text-gray-600 text-[11px] leading-relaxed line-clamp-4">
                    {{ destination.short_description || 'Destinasi wisata alam yang menakjubkan dengan pemandangan indah dan pengalaman tak terlupakan.' }}
                </p>

                <!-- Bottom Row -->
                <div class="flex items-center justify-between pt-3 mt-auto border-t border-gray-100">
                    <div>
                        <span class="text-[9px] text-gray-400 uppercase tracking-wider font-medium block">Mulai dari</span>
                        <p class="text-sm font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ formattedPrice }}</p>
                    </div>
                    
                    <!-- CTA Button with Arrow Animation -->
                    <div class="relative overflow-hidden flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[10px] font-semibold shadow-lg shadow-emerald-500/25 group-hover:shadow-emerald-500/40 transition-all duration-300">
                        <span>Jelajahi</span>
                        <ArrowRight class="w-3 h-3 transform group-hover:translate-x-1 transition-transform duration-300" />
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>
