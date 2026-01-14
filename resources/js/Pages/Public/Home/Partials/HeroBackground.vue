<script setup>
/**
 * HeroBackground.vue - With Smooth Animations
 * Decorative background with floating orbs and subtle animations
 */
</script>

<template>
    <div class="absolute inset-0 z-0 overflow-hidden">
        <!-- Base Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-900 via-teal-900 to-emerald-950"></div>
        
        <!-- Radial Gradient Overlays -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(16,185,129,0.18),transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(20,184,166,0.15),transparent_50%)]"></div>
        
        <!-- Mountain Silhouette Background -->
        <svg class="absolute bottom-0 left-0 w-full h-[40%] opacity-[0.08]" viewBox="0 0 1440 400" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="mountain-gradient-1" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#10b981;stop-opacity:0.8" />
                    <stop offset="100%" style="stop-color:#064e3b;stop-opacity:0.4" />
                </linearGradient>
                <linearGradient id="mountain-gradient-2" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#14b8a6;stop-opacity:0.6" />
                    <stop offset="100%" style="stop-color:#042f2e;stop-opacity:0.3" />
                </linearGradient>
            </defs>
            <path d="M0,400 L0,280 Q150,180 300,250 Q450,100 600,200 Q750,80 900,180 Q1050,60 1200,160 Q1350,100 1440,200 L1440,400 Z" fill="url(#mountain-gradient-2)" />
            <path d="M0,400 L0,320 Q200,220 400,300 Q550,180 700,280 Q900,150 1100,260 Q1300,200 1440,280 L1440,400 Z" fill="url(#mountain-gradient-1)" />
        </svg>

        <!-- Tree Silhouettes with Subtle Sway Animation - Responsive -->
        <svg class="absolute bottom-0 left-[2%] w-20 h-36 sm:w-24 sm:h-44 lg:w-32 lg:h-56 opacity-[0.12] animate-tree-sway" viewBox="0 0 100 200" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="tree-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#34d399;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#059669;stop-opacity:0.8" />
                </linearGradient>
            </defs>
            <path d="M50,10 L70,60 L60,60 L75,100 L55,100 L80,150 L20,150 L45,100 L25,100 L40,60 L30,60 Z" fill="url(#tree-gradient)" />
            <rect x="45" y="150" width="10" height="50" fill="#065f46" />
        </svg>

        <svg class="absolute bottom-0 right-[5%] w-24 h-40 sm:w-32 sm:h-52 lg:w-40 lg:h-64 opacity-[0.1] animate-tree-sway-delayed" viewBox="0 0 100 200" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="50" cy="50" rx="35" ry="45" fill="url(#tree-gradient)" />
            <ellipse cx="30" cy="70" rx="25" ry="35" fill="url(#tree-gradient)" />
            <ellipse cx="70" cy="70" rx="25" ry="35" fill="url(#tree-gradient)" />
            <rect x="45" y="100" width="10" height="100" fill="#065f46" />
        </svg>

        <!-- Flying Birds with Animation - Hidden on small screens -->
        <svg class="absolute top-[15%] right-[20%] w-20 h-10 sm:w-24 sm:h-12 lg:w-32 lg:h-16 opacity-[0.15] animate-birds-fly hidden sm:block" viewBox="0 0 100 50" xmlns="http://www.w3.org/2000/svg">
            <path d="M10,25 Q20,15 25,25 Q30,15 40,25" stroke="#a7f3d0" stroke-width="2" fill="none" />
            <path d="M50,20 Q58,12 62,20 Q66,12 74,20" stroke="#a7f3d0" stroke-width="1.5" fill="none" />
            <path d="M70,30 Q76,24 79,30 Q82,24 88,30" stroke="#a7f3d0" stroke-width="1" fill="none" />
        </svg>

        <!-- Animated Floating Orbs - Responsive -->
        <div class="absolute top-20 right-[15%] w-40 h-40 sm:w-56 sm:h-56 lg:w-72 lg:h-72 bg-emerald-400/20 rounded-full blur-2xl sm:blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-32 left-[10%] w-36 h-36 sm:w-48 sm:h-48 lg:w-64 lg:h-64 bg-cyan-400/15 rounded-full blur-2xl sm:blur-3xl animate-float-medium"></div>
        <div class="absolute top-1/2 left-1/2 w-48 h-48 sm:w-60 sm:h-60 lg:w-80 lg:h-80 bg-teal-500/15 rounded-full blur-2xl sm:blur-3xl -translate-x-1/2 -translate-y-1/2 animate-pulse-slow"></div>
        
        <!-- Extra Floating Particles - Responsive -->
        <div class="absolute top-[30%] left-[20%] w-2 h-2 sm:w-3 sm:h-3 bg-emerald-300/40 rounded-full animate-float-particle"></div>
        <div class="absolute top-[45%] right-[30%] w-1.5 h-1.5 sm:w-2 sm:h-2 bg-teal-300/50 rounded-full animate-float-particle-delayed"></div>
        <div class="absolute bottom-[35%] left-[40%] w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-4 lg:h-4 bg-cyan-300/30 rounded-full animate-float-particle hidden sm:block" style="animation-delay: 3s;"></div>
        <div class="absolute top-[25%] right-[10%] w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-200/40 rounded-full animate-float-particle-delayed hidden sm:block" style="animation-delay: 1.5s;"></div>
        
        <!-- Gradient Mesh Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/60 via-transparent to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-transparent"></div>
        
        <!-- Corner Accents - Responsive -->
        <div class="absolute top-0 left-0 w-48 h-48 sm:w-72 sm:h-72 lg:w-96 lg:h-96 bg-gradient-to-br from-emerald-500/10 via-transparent to-transparent"></div>
        <div class="absolute bottom-0 right-0 w-48 h-48 sm:w-72 sm:h-72 lg:w-96 lg:h-96 bg-gradient-to-tl from-teal-500/10 via-transparent to-transparent"></div>
    </div>
</template>

<style scoped>
/* Floating Orb Animations */
@keyframes float-slow {
    0%, 100% { 
        transform: translateY(0) scale(1); 
        opacity: 0.2;
    }
    50% { 
        transform: translateY(-20px) scale(1.05); 
        opacity: 0.25;
    }
}

.animate-float-slow {
    animation: float-slow 8s ease-in-out infinite;
    will-change: transform, opacity;
}

@keyframes float-medium {
    0%, 100% { 
        transform: translateY(0) scale(1); 
        opacity: 0.15;
    }
    50% { 
        transform: translateY(15px) scale(0.95); 
        opacity: 0.2;
    }
}

.animate-float-medium {
    animation: float-medium 6s ease-in-out infinite;
    will-change: transform, opacity;
}

@keyframes pulse-slow {
    0%, 100% { 
        transform: translate(-50%, -50%) scale(1); 
        opacity: 0.15;
    }
    50% { 
        transform: translate(-50%, -50%) scale(1.1); 
        opacity: 0.2;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 7s ease-in-out infinite;
    will-change: transform, opacity;
}

/* Floating Particles */
@keyframes float-particle {
    0%, 100% { 
        transform: translateY(0) translateX(0);
        opacity: 0.4;
    }
    25% {
        transform: translateY(-25px) translateX(10px);
        opacity: 0.6;
    }
    50% { 
        transform: translateY(-15px) translateX(-10px);
        opacity: 0.3;
    }
    75% {
        transform: translateY(-35px) translateX(5px);
        opacity: 0.5;
    }
}

.animate-float-particle {
    animation: float-particle 10s ease-in-out infinite;
    will-change: transform, opacity;
}

.animate-float-particle-delayed {
    animation: float-particle 12s ease-in-out infinite;
    animation-delay: 2s;
    will-change: transform, opacity;
}

/* Tree Sway Animation */
@keyframes tree-sway {
    0%, 100% { 
        transform: rotate(0deg); 
        transform-origin: bottom center;
    }
    50% { 
        transform: rotate(1deg);
        transform-origin: bottom center;
    }
}

.animate-tree-sway {
    animation: tree-sway 4s ease-in-out infinite;
    will-change: transform;
}

.animate-tree-sway-delayed {
    animation: tree-sway 5s ease-in-out infinite;
    animation-delay: 1s;
    will-change: transform;
}

/* Birds Flying Animation */
@keyframes birds-fly {
    0%, 100% { 
        transform: translateX(0) translateY(0);
    }
    25% { 
        transform: translateX(10px) translateY(-5px);
    }
    75% { 
        transform: translateX(-5px) translateY(3px);
    }
}

.animate-birds-fly {
    animation: birds-fly 6s ease-in-out infinite;
    will-change: transform;
}
</style>
