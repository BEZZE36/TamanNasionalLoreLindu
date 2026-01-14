<script setup>
/**
 * IndexGrid.vue - Premium Grid Section
 * Follows home-design-system.md: text-2xl to text-4xl section title, gap-3 lg:gap-4
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { MapPin, Home, ChevronLeft, ChevronRight, Compass } from 'lucide-vue-next';
import IndexCard from './IndexCard.vue';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    destinations: { type: Object, required: true },
    user: Object
});

const sectionRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        // Header animation with ScrollTrigger
        gsap.fromTo('.grid-header > *', 
            { opacity: 0, y: 25 },
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power3.out',
                scrollTrigger: { trigger: sectionRef.value, start: 'top 80%', toggleActions: 'play none none none' }
            }
        );

        // Cards stagger entrance with ScrollTrigger
        gsap.fromTo('.destination-card',
            { opacity: 0, y: 30, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'back.out(1.2)',
                scrollTrigger: { trigger: '.destinations-grid', start: 'top 85%', toggleActions: 'play none none none' }
            }
        );
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section id="destinations" ref="sectionRef" class="py-16 md:py-20 relative overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-b from-white via-gray-50/50 to-white"></div>
        
        <!-- Animated orbs: bg-emerald-100/40, bg-cyan-100/30 -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-20 left-[10%] w-80 h-80 bg-emerald-100/40 rounded-full blur-[80px] animate-float-slow"></div>
            <div class="absolute top-1/2 -right-20 w-64 h-64 bg-cyan-100/30 rounded-full blur-[60px] animate-float-medium"></div>
            <div class="absolute -bottom-20 left-1/3 w-56 h-56 bg-teal-100/25 rounded-full blur-[50px] animate-float-slow" style="animation-delay: 2s"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Section Header: mb-10 per design system -->
            <div class="grid-header text-center mb-10">
                <!-- Badge: text-[11px], px-3 py-1.5 -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-600"></span>
                    </span>
                    <Compass class="w-3.5 h-3.5" />
                    <span class="text-[11px] font-semibold tracking-wide">{{ destinations.total || 0 }} Destinasi Tersedia</span>
                </div>

                <!-- Title: text-2xl sm:text-3xl md:text-4xl per design system -->
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mb-3">
                    Temukan 
                    <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        Petualangan
                    </span>
                    Anda
                </h2>

                <!-- Desc: text-sm -->
                <p class="text-gray-500 text-sm max-w-xl mx-auto">
                    Pilih destinasi impian dan mulai petualangan tak terlupakan di Taman Nasional Lore Lindu
                </p>
            </div>

            <!-- Grid: gap-3 lg:gap-4 per design system -->
            <div v-if="destinations.data?.length > 0" class="destinations-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
                <IndexCard 
                    v-for="(destination, index) in destinations.data"
                    :key="destination.id"
                    :destination="destination"
                    :index="index"
                    :user="user"
                    class="destination-card"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center shadow-lg">
                    <MapPin class="w-8 h-8 text-emerald-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1.5">Belum Ada Destinasi</h3>
                <p class="text-gray-500 text-[11px] mb-5 max-w-sm mx-auto">
                    Destinasi wisata sedang dalam tahap kurasi. Kembali lagi nanti!
                </p>
                <Link href="/" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-medium text-xs rounded-full shadow-lg">
                    <Home class="w-3.5 h-3.5" />
                    Kembali ke Beranda
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="destinations.data?.length > 0 && destinations.last_page > 1" class="mt-12 flex justify-center">
                <nav class="inline-flex items-center gap-1.5 p-1.5 bg-white/80 backdrop-blur-xl rounded-xl shadow-lg border border-gray-100">
                    <Link v-if="destinations.prev_page_url" :href="destinations.prev_page_url" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-600 transition-colors">
                        <ChevronLeft class="w-4 h-4" />
                    </Link>
                    <div v-else class="w-8 h-8 rounded-lg bg-gray-50/50 flex items-center justify-center text-gray-300">
                        <ChevronLeft class="w-4 h-4" />
                    </div>

                    <div class="flex items-center gap-1 px-1.5">
                        <template v-for="page in destinations.last_page" :key="page">
                            <Link v-if="Math.abs(page - destinations.current_page) <= 2 || page === 1 || page === destinations.last_page" :href="`/destinations?page=${page}`" :class="['w-8 h-8 rounded-lg flex items-center justify-center text-[11px] font-bold transition-all', page === destinations.current_page ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100']">
                                {{ page }}
                            </Link>
                            <span v-else-if="page === destinations.current_page - 3 || page === destinations.current_page + 3" class="text-gray-400 text-[10px] px-0.5">...</span>
                        </template>
                    </div>

                    <Link v-if="destinations.next_page_url" :href="destinations.next_page_url" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-600 transition-colors">
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                    <div v-else class="w-8 h-8 rounded-lg bg-gray-50/50 flex items-center justify-center text-gray-300">
                        <ChevronRight class="w-4 h-4" />
                    </div>
                </nav>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes float-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}
.animate-float-slow { animation: float-slow 8s ease-in-out infinite; }

@keyframes float-medium {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(15px); }
}
.animate-float-medium { animation: float-medium 6s ease-in-out infinite; }
</style>
