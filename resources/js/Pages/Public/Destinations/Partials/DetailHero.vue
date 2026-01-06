<script setup>
/**
 * DetailHero.vue - Premium Hero with COVER IMAGE Background
 * Fixed: Uses destination cover image, not satellite map
 * Enhanced animations, glassmorphism, modern aesthetics
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { MapPin, Star, Heart, Share2, ChevronRight, Ticket, Clock, Navigation, Phone, Mail, ExternalLink, Calendar, Users } from 'lucide-vue-next';

const props = defineProps({ destination: { type: Object, required: true } });

const page = usePage();
const user = page.props.auth?.user;
const heroRef = ref(null);
let ctx;

const isWishlisted = computed(() => user?.wishlisted_destination_ids?.includes(props.destination.id));
const lat = computed(() => parseFloat(props.destination.latitude) || -1.5);
const lng = computed(() => parseFloat(props.destination.longitude) || 120.5);

const toggleWishlist = async () => {
    if (!user) { window.location.href = '/login'; return; }
    try {
        await fetch(`/wishlist/${props.destination.slug}/toggle`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Content-Type': 'application/json' } });
        window.location.reload();
    } catch (e) {}
};

const shareDestination = async () => {
    if (navigator.share) { try { await navigator.share({ title: props.destination.name, text: props.destination.short_description, url: window.location.href }); } catch (e) {} }
    else { navigator.clipboard.writeText(window.location.href); }
};

onMounted(() => {
    ctx = gsap.context(() => {
        const tl = gsap.timeline({ delay: 0.2 });
        tl.fromTo('.hero-breadcrumb', { opacity: 0, y: -15 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' })
          .fromTo('.hero-badges > *', { opacity: 0, scale: 0.7, y: 10 }, { opacity: 1, scale: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'back.out(3)' }, '-=0.2')
          .fromTo('.hero-title', { opacity: 0, y: 25, filter: 'blur(8px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.5, ease: 'power3.out' }, '-=0.2')
          .fromTo('.hero-meta > *', { opacity: 0, x: -10 }, { opacity: 1, x: 0, duration: 0.3, stagger: 0.06 }, '-=0.2')
          .fromTo('.hero-actions > *', { opacity: 0, scale: 0.8 }, { opacity: 1, scale: 1, duration: 0.3, stagger: 0.06, ease: 'back.out(2)' }, '-=0.1');

        // Ken Burns effect on background
        gsap.to('.hero-bg-image', {
            scale: 1.1,
            duration: 15,
            ease: 'none',
            repeat: -1,
            yoyo: true
        });
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="heroRef" class="relative min-h-[55vh] overflow-hidden">
        <!-- COVER IMAGE Background (Not Maps) -->
        <div class="absolute inset-0">
            <img 
                :src="destination.primary_image_url || '/images/placeholder-no-image.svg'" 
                :alt="destination.name"
                class="hero-bg-image w-full h-full object-cover"
                @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
            >
            <!-- Premium Gradient Overlays -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/30"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-slate-900/50 to-transparent"></div>
        </div>

        <!-- Floating Decorative Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-[15%] right-[10%] w-24 h-24 border border-white/5 rounded-full animate-pulse-slow"></div>
            <div class="absolute bottom-[25%] left-[5%] w-16 h-16 border border-emerald-500/10 rounded-2xl rotate-12 animate-float"></div>
        </div>

        <!-- Wave Transition -->
        <svg class="absolute bottom-0 left-0 right-0 w-full h-24" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
            <path d="M0,50L60,45C120,40,240,30,360,35C480,40,600,60,720,65C840,70,960,60,1080,50C1200,40,1320,30,1380,25L1440,20L1440,100L0,100Z" fill="white"/>
        </svg>

        <!-- Content -->
        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-28">
            <!-- Breadcrumb -->
            <nav class="hero-breadcrumb flex items-center gap-1.5 text-[10px] text-white/60 mb-5 font-medium flex-wrap">
                <Link href="/" class="hover:text-white transition-colors">Beranda</Link>
                <ChevronRight class="w-3 h-3" />
                <Link href="/destinations" class="hover:text-white transition-colors">Destinasi</Link>
                <ChevronRight class="w-3 h-3" />
                <template v-if="destination.category">
                    <Link :href="`/destinations?category=${destination.category.slug}`" class="hover:text-white transition-colors">{{ destination.category.name }}</Link>
                    <ChevronRight class="w-3 h-3" />
                </template>
                <span class="text-white/90 truncate max-w-[200px]">{{ destination.name }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row gap-6 lg:items-end lg:justify-between">
                <div class="flex-1">
                    <!-- Badges -->
                    <div class="hero-badges flex flex-wrap items-center gap-2 mb-4">
                        <span v-if="destination.category" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white text-[10px] font-semibold">
                            {{ destination.category.name }}
                        </span>
                        <span v-if="destination.is_featured" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[9px] font-bold uppercase shadow-lg shadow-amber-500/30">
                            <Star class="w-3 h-3 fill-current" />
                            Unggulan
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/95 text-gray-900 text-[10px] font-bold shadow-lg">
                            <Star class="w-3.5 h-3.5 text-amber-500 fill-amber-500" />
                            {{ (parseFloat(destination.rating) || 4.8).toFixed(1) }}
                            <span class="text-gray-500 font-normal">({{ destination.review_count || 0 }})</span>
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="hero-title text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                        {{ destination.name }}
                    </h1>

                    <!-- Meta Info -->
                    <div class="hero-meta flex flex-wrap items-center gap-2 text-white/70 text-[11px]">
                        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                            <MapPin class="w-3.5 h-3.5 text-emerald-400" />
                            {{ destination.city }}
                        </span>
                        <span v-if="destination.operating_hours" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                            <Clock class="w-3.5 h-3.5 text-cyan-400" />
                            {{ destination.operating_hours }}
                        </span>
                        <span v-if="destination.contact_phone" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                            <Phone class="w-3.5 h-3.5 text-blue-400" />
                            {{ destination.contact_phone }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="hero-actions flex flex-wrap items-center gap-2">
                    <button @click="shareDestination" class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-xl border border-white/20 hover:bg-white/25 flex items-center justify-center text-white transition-all duration-300 group">
                        <Share2 class="w-4.5 h-4.5 group-hover:scale-110 transition-transform" />
                    </button>
                    <button v-if="user" @click="toggleWishlist" class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-xl border border-white/20 hover:bg-white/25 flex items-center justify-center transition-all duration-300 group">
                        <Heart :class="['w-4.5 h-4.5 group-hover:scale-110 transition-all', isWishlisted ? 'fill-red-500 text-red-500' : 'text-white fill-none']" />
                    </button>
                    <a :href="`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`" target="_blank" class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-xl border border-white/20 hover:bg-white/25 flex items-center justify-center text-white transition-all duration-300 group">
                        <Navigation class="w-4.5 h-4.5 group-hover:scale-110 transition-transform" />
                    </a>
                    <Link :href="`/book/${destination.slug}`" class="h-11 px-6 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-xs flex items-center gap-2.5 shadow-xl shadow-emerald-900/30 hover:shadow-emerald-500/40 transition-all duration-300 group">
                        <Ticket class="w-4.5 h-4.5 group-hover:rotate-12 transition-transform" />
                        <span>Pesan Tiket</span>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(12deg); }
    50% { transform: translateY(-15px) rotate(12deg); }
}
.animate-float { animation: float 6s ease-in-out infinite; }
</style>
