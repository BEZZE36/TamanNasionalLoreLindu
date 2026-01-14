<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { gsap } from 'gsap';
import { Sparkles, Calendar, Clock, RefreshCw, Filter, TrendingUp, Zap } from 'lucide-vue-next';

const props = defineProps({
    periodLabel: String,
    todayBookings: Number,
    pendingBookings: Number,
    startDate: String,
    endDate: String,
    isRefreshing: Boolean,
});

const emit = defineEmits(['refresh', 'filter']);

// Real-time clock
const currentTime = ref('');
const currentDate = ref('');

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

let timeInterval;
let ctx;

onMounted(() => {
    updateTime();
    timeInterval = setInterval(updateTime, 1000);
    
    // GSAP animations with nextTick
    nextTick(() => {
        ctx = gsap.context(() => {
            const welcomeContent = document.querySelector('.welcome-content');
            const welcomeStats = document.querySelector('.welcome-stats');
            const particles = document.querySelectorAll('.floating-particle');
            
            if (welcomeContent) {
                gsap.fromTo(welcomeContent, 
                    { opacity: 0, x: -30 }, 
                    { opacity: 1, x: 0, duration: 0.6, ease: 'power3.out' }
                );
            }
            if (welcomeStats) {
                gsap.fromTo(welcomeStats, 
                    { opacity: 0, x: 30 }, 
                    { opacity: 1, x: 0, duration: 0.6, delay: 0.2, ease: 'power3.out' }
                );
            }
            if (particles.length > 0) {
                gsap.fromTo(particles, 
                    { opacity: 0, scale: 0 }, 
                    { opacity: 1, scale: 1, duration: 0.8, stagger: 0.1, delay: 0.4, ease: 'back.out(1.7)' }
                );
            }
        });
    });
});

onBeforeUnmount(() => {
    if (timeInterval) clearInterval(timeInterval);
    if (ctx) ctx.revert();
});
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-5 shadow-2xl shadow-violet-500/30">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute top-10 right-40 w-40 h-40 bg-cyan-400/15 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-1/3 w-32 h-32 bg-pink-400/15 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
        </div>
        
        <!-- Floating Particles -->
        <div class="floating-particle absolute top-6 left-20 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        <div class="floating-particle absolute top-14 right-32 w-1.5 h-1.5 bg-cyan-300/60 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
        <div class="floating-particle absolute bottom-8 right-1/4 w-2.5 h-2.5 bg-white/50 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
        <div class="floating-particle absolute top-1/2 left-10 w-1.5 h-1.5 bg-pink-300/50 rounded-full animate-bounce" style="animation-delay: 0.8s"></div>
        <div class="floating-particle absolute bottom-12 left-1/2 w-2 h-2 bg-amber-300/40 rounded-full animate-bounce" style="animation-delay: 1s"></div>

        <!-- Content -->
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Left Side -->
            <div class="welcome-content space-y-3">
                <div class="flex items-center gap-3">
                    <!-- Icon with Glow -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/40 rounded-xl blur-lg animate-pulse"></div>
                        <div class="relative flex h-11 w-11 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Sparkles class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Selamat Datang!</h1>
                        <p class="text-purple-100/90 text-xs font-medium">Dashboard Admin TNLL Explore</p>
                    </div>
                </div>
                
                <!-- Date & Period Info -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 backdrop-blur-xl ring-1 ring-white/20">
                        <Calendar class="w-3.5 h-3.5 text-purple-200" />
                        <span class="text-purple-100 text-[11px] font-medium">{{ currentDate }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 backdrop-blur-xl ring-1 ring-white/20">
                        <Clock class="w-3.5 h-3.5 text-cyan-200" />
                        <span class="text-cyan-100 text-[11px] font-mono font-semibold">{{ currentTime }}</span>
                    </div>
                </div>

                <!-- Period Badge -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-amber-500/30 to-orange-500/30 ring-1 ring-amber-400/40">
                        <TrendingUp class="w-3.5 h-3.5 text-amber-200" />
                        <span class="text-amber-100 text-[11px] font-semibold">Periode: {{ periodLabel }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Stats & Actions -->
            <div class="welcome-stats flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <!-- Quick Stats -->
                <div class="flex items-center gap-4">
                    <div class="text-center px-4 py-2 rounded-xl bg-white/10 backdrop-blur-xl ring-1 ring-white/20">
                        <p class="text-purple-100/80 text-[10px] uppercase tracking-wide">Hari ini</p>
                        <p class="text-white font-bold text-lg">{{ todayBookings || 0 }}</p>
                        <p class="text-purple-200 text-[10px]">Booking</p>
                    </div>
                    <div class="w-px h-12 bg-gradient-to-b from-transparent via-white/30 to-transparent"></div>
                    <div class="text-center px-4 py-2 rounded-xl bg-amber-500/20 backdrop-blur-xl ring-1 ring-amber-400/30">
                        <p class="text-amber-100/80 text-[10px] uppercase tracking-wide">Pending</p>
                        <p class="text-amber-300 font-bold text-lg animate-pulse">{{ pendingBookings || 0 }}</p>
                        <p class="text-amber-200 text-[10px]">Menunggu</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <button 
                        @click="$emit('filter')"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/15 backdrop-blur-xl text-white text-xs font-medium hover:bg-white/25 ring-1 ring-white/20 hover:ring-white/40 transition-all duration-300 hover:scale-105"
                    >
                        <Filter class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Filter</span>
                    </button>
                    <button 
                        @click="$emit('refresh')"
                        :disabled="isRefreshing"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white text-violet-600 text-xs font-semibold hover:bg-violet-50 hover:shadow-xl transition-all duration-300 hover:scale-105 disabled:opacity-50"
                    >
                        <RefreshCw :class="['w-3.5 h-3.5', isRefreshing ? 'animate-spin' : '']" />
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

.animate-float {
    animation: float 4s ease-in-out infinite;
}
</style>
