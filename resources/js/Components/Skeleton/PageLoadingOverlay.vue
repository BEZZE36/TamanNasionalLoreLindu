<script setup>
/**
 * PageLoadingOverlay.vue
 * Design 2 Enhanced: Ultra Premium Glassmorphism Card
 * FIXED: Uses window-level state to persist across page transitions
 */
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

// Use window-level state to persist across component remounts
if (!window.__loadingState) {
    window.__loadingState = {
        isLoading: false,
        progress: 0,
        startTime: 0,
        isPageReady: false,
        progressInterval: null,
        hideTimeout: null
    };
}

const state = window.__loadingState;
const MIN_LOADING_TIME = 600;
const HOLD_AT_99_TIME = 400;

// Reactive refs that sync with window state
const isLoading = ref(state.isLoading);
const loadingProgress = ref(state.progress);

// Sync window state to refs
const syncFromWindow = () => {
    isLoading.value = state.isLoading;
    loadingProgress.value = state.progress;
};

// Complete the loading sequence
const completeLoading = () => {
    const elapsed = Date.now() - state.startTime;
    const remaining = Math.max(0, MIN_LOADING_TIME - elapsed);
    
    state.progress = 100;
    loadingProgress.value = 100;
    
    state.hideTimeout = setTimeout(() => {
        state.isLoading = false;
        state.progress = 0;
        state.isPageReady = false;
        isLoading.value = false;
        loadingProgress.value = 0;
    }, remaining + 300);
};

// Speed up progress to 99%
const speedUpTo99 = () => {
    const speedInterval = setInterval(() => {
        if (state.progress < 99) {
            state.progress = Math.min(99, state.progress + 8);
            loadingProgress.value = state.progress;
        } else {
            clearInterval(speedInterval);
            checkCompletion();
        }
    }, 30);
};

// Check if we can complete
const checkCompletion = () => {
    const elapsed = Date.now() - state.startTime;
    if (state.progress >= 99 && state.isPageReady && elapsed >= HOLD_AT_99_TIME) {
        if (state.progressInterval) {
            clearInterval(state.progressInterval);
            state.progressInterval = null;
        }
        completeLoading();
    }
};

onMounted(() => {
    // Sync initial state
    syncFromWindow();
    
    // Polling to keep in sync
    const syncInterval = setInterval(syncFromWindow, 50);
    
    // START event - ONLY for page navigation (GET requests), NOT for form submissions
    const startCleanup = router.on('start', (event) => {
        // Check if this is a page navigation (GET) or a form submission (POST/PUT/DELETE)
        const visit = event.detail?.visit;
        const method = visit?.method?.toLowerCase() || 'get';
        
        // Only show loading overlay for GET requests (page navigation)
        // Skip for POST, PUT, DELETE, PATCH (form submissions)
        if (method !== 'get') {
            return; // Don't show loading overlay for form submissions
        }
        
        // Clear existing
        if (state.progressInterval) clearInterval(state.progressInterval);
        if (state.hideTimeout) clearTimeout(state.hideTimeout);
        if (state.fallbackTimeout) clearTimeout(state.fallbackTimeout);
        
        // Reset state
        state.isLoading = true;
        state.progress = 0;
        state.startTime = Date.now();
        state.isPageReady = false;
        
        isLoading.value = true;
        loadingProgress.value = 0;
        
        // Fallback: force hide after 5 seconds to prevent stuck state
        state.fallbackTimeout = setTimeout(() => {
            if (state.isLoading) {
                console.warn('Loading overlay stuck, forcing reset');
                state.isLoading = false;
                state.progress = 0;
                state.isPageReady = false;
                isLoading.value = false;
                loadingProgress.value = 0;
                if (state.progressInterval) {
                    clearInterval(state.progressInterval);
                    state.progressInterval = null;
                }
            }
        }, 5000);
        
        // Progress animation
        state.progressInterval = setInterval(() => {
            const elapsed = Date.now() - state.startTime;
            
            if (state.progress < 85) {
                state.progress = Math.min(85, state.progress + 3);
            } else if (state.progress < 99 && elapsed < HOLD_AT_99_TIME) {
                state.progress = Math.min(99, state.progress + 0.5);
            } else if (state.progress < 99) {
                state.progress = 99;
            }
            
            loadingProgress.value = state.progress;
            
            // Check completion
            if (state.progress >= 99 && state.isPageReady && elapsed >= HOLD_AT_99_TIME) {
                clearInterval(state.progressInterval);
                state.progressInterval = null;
                completeLoading();
            }
        }, 50);
    });
    
    // ERROR event - immediately hide loading overlay
    const errorCleanup = router.on('error', () => {
        if (state.progressInterval) clearInterval(state.progressInterval);
        if (state.hideTimeout) clearTimeout(state.hideTimeout);
        if (state.fallbackTimeout) clearTimeout(state.fallbackTimeout);
        
        state.isLoading = false;
        state.progress = 0;
        state.isPageReady = false;
        isLoading.value = false;
        loadingProgress.value = 0;
    });
    
    // PROGRESS event
    const progressCleanup = router.on('progress', (event) => {
        if (event.detail.progress) {
            const real = event.detail.progress.percentage;
            if (real > state.progress && real < 99) {
                state.progress = real;
                loadingProgress.value = real;
            }
        }
    });
    
    // FINISH event - page is ready but DON'T hide yet
    const finishCleanup = router.on('finish', () => {
        state.isPageReady = true;
        
        // Speed up to 99% if not there yet
        if (state.progress < 99) {
            speedUpTo99();
        } else {
            checkCompletion();
        }
        
        // Fallback: ensure completion after timeout
        const elapsed = Date.now() - state.startTime;
        const waitTime = Math.max(0, HOLD_AT_99_TIME - elapsed) + 500;
        
        setTimeout(() => {
            if (state.isLoading && state.isPageReady) {
                if (state.progressInterval) {
                    clearInterval(state.progressInterval);
                    state.progressInterval = null;
                }
                if (state.progress < 99) {
                    state.progress = 99;
                    loadingProgress.value = 99;
                }
                setTimeout(completeLoading, 200);
            }
        }, waitTime);
    });
    
    onUnmounted(() => {
        clearInterval(syncInterval);
        startCleanup();
        errorCleanup();
        progressCleanup();
        finishCleanup();
        // DON'T clear progressInterval or hideTimeout - they need to persist!
    });
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-600 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-800 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="isLoading" class="fixed inset-0 z-[9999] overflow-hidden">
            <!-- Premium Blurred Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-gray-50 backdrop-blur-xl"></div>
            
            <!-- Animated Gradient Orbs -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="orb orb-1"></div>
                <div class="orb orb-2"></div>
                <div class="orb orb-3"></div>
                <div class="orb orb-4"></div>
            </div>
            
            <!-- Subtle Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, #10b981 1px, transparent 1px); background-size: 40px 40px;"></div>

            <!-- Center Glassmorphism Card -->
            <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4">
                <div class="glass-card animate-card-enter">
                    <!-- Animated Border Glow -->
                    <div class="absolute -inset-[2px] bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 rounded-2xl sm:rounded-[28px] opacity-0 animate-border-glow"></div>
                    
                    <!-- Card Inner - Responsive -->
                    <div class="relative bg-white/80 backdrop-blur-2xl rounded-[22px] sm:rounded-[26px] p-6 sm:p-8 lg:p-10 shadow-2xl shadow-gray-200/50 border border-white/50">
                        
                        <!-- Logo with Animated Ring - Responsive -->
                        <div class="relative w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 mx-auto mb-5 sm:mb-6 lg:mb-8">
                            <!-- Outer Spinning Ring -->
                            <div class="absolute inset-0 rounded-full border-2 sm:border-[3px] border-dashed border-gray-200 animate-spin-very-slow"></div>
                            
                            <!-- Progress Ring -->
                            <svg class="absolute inset-0 w-full h-full -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" stroke="#e5e7eb" stroke-width="3" fill="none"/>
                                <circle 
                                    cx="50" cy="50" r="45" 
                                    stroke="url(#ringGradient)" 
                                    stroke-width="3" 
                                    fill="none"
                                    stroke-linecap="round"
                                    :stroke-dasharray="283"
                                    :stroke-dashoffset="283 - (loadingProgress / 100) * 283"
                                    class="transition-all duration-300"
                                />
                                <defs>
                                    <linearGradient id="ringGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#10b981"/>
                                        <stop offset="50%" stop-color="#14b8a6"/>
                                        <stop offset="100%" stop-color="#06b6d4"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                            
                            <!-- Center Logo - Responsive -->
                            <div class="absolute inset-3 sm:inset-4 rounded-full bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 flex items-center justify-center shadow-xl shadow-emerald-500/30 animate-logo-pulse">
                                <span class="text-white font-black text-base sm:text-lg lg:text-xl tracking-tight">TNLL</span>
                            </div>
                            
                            <!-- Floating Particles around logo -->
                            <div class="logo-particle logo-particle-1 hidden sm:block"></div>
                            <div class="logo-particle logo-particle-2 hidden sm:block"></div>
                            <div class="logo-particle logo-particle-3 hidden sm:block"></div>
                        </div>

                        <!-- Progress Bar with Shimmer - Responsive -->
                        <div class="relative w-full h-1.5 sm:h-2 bg-gray-100 rounded-full overflow-hidden mb-4 sm:mb-5 lg:mb-6">
                            <div class="absolute inset-0 bg-gray-100 rounded-full"></div>
                            <div class="h-full bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 rounded-full transition-all duration-300 relative overflow-hidden"
                                 :style="{ width: `${loadingProgress}%` }">
                                <div class="absolute inset-0 shimmer-effect"></div>
                            </div>
                        </div>

                        <!-- Percentage - Responsive -->
                        <div class="text-center mb-3 sm:mb-4">
                            <span class="text-2xl sm:text-3xl font-black bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                                {{ Math.round(loadingProgress) }}%
                            </span>
                        </div>

                        <!-- Brand Text - Responsive -->
                        <div class="text-center mb-4 sm:mb-5 lg:mb-6">
                            <h2 class="text-base sm:text-lg font-bold text-gray-800 mb-0.5 sm:mb-1">TNLL Explore</h2>
                            <p class="text-[10px] sm:text-xs text-gray-400">Taman Nasional Lore Lindu</p>
                        </div>

                        <!-- Bouncing Dots - Responsive -->
                        <div class="flex items-center justify-center gap-2 sm:gap-3">
                            <div class="bounce-dot w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 shadow-lg shadow-emerald-500/40" style="animation-delay: 0s"></div>
                            <div class="bounce-dot w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-gradient-to-br from-teal-400 to-teal-500 shadow-lg shadow-teal-500/40" style="animation-delay: 0.15s"></div>
                            <div class="bounce-dot w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-500 shadow-lg shadow-cyan-500/40" style="animation-delay: 0.3s"></div>
                        </div>

                        <!-- Loading Text with Typing Effect - Responsive -->
                        <p class="mt-4 sm:mt-5 text-center text-[10px] sm:text-[11px] text-gray-400 tracking-widest uppercase flex items-center justify-center gap-1">
                            <span>Memuat halaman</span>
                            <span class="typing-dots">
                                <span class="dot">.</span>
                                <span class="dot">.</span>
                                <span class="dot">.</span>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Floating Decorative Elements - Responsive -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="floating-shape shape-1 hidden sm:block"></div>
                <div class="floating-shape shape-2"></div>
                <div class="floating-shape shape-3 hidden sm:block"></div>
                <div class="floating-shape shape-4"></div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Gradient Orbs - Responsive */
.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.5;
}

@media (min-width: 640px) {
    .orb {
        filter: blur(80px);
    }
}

.orb-1 {
    width: 200px; height: 200px;
    background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
    top: -10%; left: -5%;
    animation: orb-float 15s ease-in-out infinite;
}
@media (min-width: 640px) {
    .orb-1 {
        width: 300px; height: 300px;
    }
}
@media (min-width: 1024px) {
    .orb-1 {
        width: 400px; height: 400px;
    }
}

.orb-2 {
    width: 150px; height: 150px;
    background: linear-gradient(135deg, #14b8a6 0%, #06b6d4 100%);
    top: 20%; right: -5%;
    animation: orb-float 12s ease-in-out infinite reverse;
}
@media (min-width: 640px) {
    .orb-2 {
        width: 200px; height: 200px;
    }
}
@media (min-width: 1024px) {
    .orb-2 {
        width: 300px; height: 300px;
    }
}

.orb-3 {
    width: 175px; height: 175px;
    background: linear-gradient(135deg, #06b6d4 0%, #10b981 100%);
    bottom: -10%; left: 20%;
    animation: orb-float 18s ease-in-out infinite;
    animation-delay: 3s;
}
@media (min-width: 640px) {
    .orb-3 {
        width: 250px; height: 250px;
    }
}
@media (min-width: 1024px) {
    .orb-3 {
        width: 350px; height: 350px;
    }
}

.orb-4 {
    width: 125px; height: 125px;
    background: linear-gradient(135deg, #34d399 0%, #2dd4bf 100%);
    bottom: 30%; right: 10%;
    animation: orb-float 14s ease-in-out infinite reverse;
    animation-delay: 5s;
}
@media (min-width: 640px) {
    .orb-4 {
        width: 175px; height: 175px;
    }
}
@media (min-width: 1024px) {
    .orb-4 {
        width: 250px; height: 250px;
    }
}

@keyframes orb-float {
    0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.4; }
    25% { transform: translate(30px, -20px) scale(1.05); opacity: 0.5; }
    50% { transform: translate(-20px, 30px) scale(0.95); opacity: 0.45; }
    75% { transform: translate(20px, 10px) scale(1.02); opacity: 0.5; }
}

/* Glass Card - Responsive */
.glass-card {
    position: relative;
    max-width: 320px;
    width: 100%;
}

@media (min-width: 640px) {
    .glass-card {
        max-width: 350px;
    }
}

@media (min-width: 1024px) {
    .glass-card {
        max-width: 380px;
    }
}

/* Card Enter Animation */
@keyframes card-enter {
    0% { opacity: 0; transform: translateY(30px) scale(0.9); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-card-enter { animation: card-enter 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

/* Border Glow Animation */
@keyframes border-glow {
    0%, 100% { opacity: 0; }
    50% { opacity: 0.5; }
}
.animate-border-glow { animation: border-glow 3s ease-in-out infinite; }

/* Very Slow Spin */
@keyframes spin-very-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-very-slow { animation: spin-very-slow 20s linear infinite; }

/* Logo Pulse */
@keyframes logo-pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 10px 40px -10px rgba(16, 185, 129, 0.4); }
    50% { transform: scale(1.02); box-shadow: 0 15px 50px -10px rgba(16, 185, 129, 0.5); }
}
.animate-logo-pulse { animation: logo-pulse 2s ease-in-out infinite; }

/* Logo Particles */
.logo-particle {
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #06b6d4);
}
.logo-particle-1 { top: 0; left: 50%; animation: orbit 4s linear infinite; }
.logo-particle-2 { top: 50%; right: 0; animation: orbit 4s linear infinite; animation-delay: 1.33s; }
.logo-particle-3 { bottom: 0; left: 50%; animation: orbit 4s linear infinite; animation-delay: 2.66s; }

@keyframes orbit {
    0% { transform: rotate(0deg) translateX(60px) rotate(0deg) scale(1); opacity: 1; }
    50% { transform: rotate(180deg) translateX(60px) rotate(-180deg) scale(0.6); opacity: 0.5; }
    100% { transform: rotate(360deg) translateX(60px) rotate(-360deg) scale(1); opacity: 1; }
}

/* Shimmer Effect */
.shimmer-effect {
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
    animation: shimmer 1.5s ease-in-out infinite;
}
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(200%); }
}

/* Bouncing Dots */
@keyframes bounce-dot {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-10px) scale(1.1); }
}
.bounce-dot { animation: bounce-dot 0.6s ease-in-out infinite; }

/* Typing Dots */
.typing-dots .dot {
    animation: typing-dot 1.4s ease-in-out infinite;
    opacity: 0;
}
.typing-dots .dot:nth-child(1) { animation-delay: 0s; }
.typing-dots .dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dots .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing-dot {
    0%, 60%, 100% { opacity: 0; }
    30% { opacity: 1; }
}

/* Floating Shapes */
.floating-shape {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 182, 212, 0.15));
    animation: float-shape 8s ease-in-out infinite;
}
.shape-1 { width: 20px; height: 20px; top: 15%; left: 10%; animation-delay: 0s; }
.shape-2 { width: 15px; height: 15px; top: 25%; right: 15%; animation-delay: 2s; }
.shape-3 { width: 25px; height: 25px; bottom: 20%; left: 8%; animation-delay: 4s; }
.shape-4 { width: 18px; height: 18px; bottom: 30%; right: 12%; animation-delay: 6s; }

@keyframes float-shape {
    0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); opacity: 0.4; }
    25% { transform: translateY(-30px) translateX(15px) rotate(90deg); opacity: 0.7; }
    50% { transform: translateY(-15px) translateX(-10px) rotate(180deg); opacity: 0.5; }
    75% { transform: translateY(-40px) translateX(5px) rotate(270deg); opacity: 0.6; }
}
</style>
