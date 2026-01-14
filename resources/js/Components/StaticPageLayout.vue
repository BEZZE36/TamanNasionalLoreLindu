<script setup>
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Shield, FileText, ArrowLeft, ArrowRight, Calendar, Clock } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import { gsap } from 'gsap';

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    icon: { type: String, default: 'shield' },
    content: { type: String, default: '' },
    backLink: { type: Object, default: () => ({ href: '/', text: 'Kembali ke Beranda' }) },
    nextLink: { type: Object, default: null },
    lastUpdated: { type: String, default: 'Januari 2026' },
});

const iconComponent = { shield: Shield, file: FileText };
const readingTime = computed(() => {
    if (!props.content) return '0 menit';
    const words = props.content.replace(/<[^>]*>/g, '').split(/\s+/).length;
    const minutes = Math.ceil(words / 200);
    return `${minutes} menit`;
});

// Scroll function with GSAP
const scrollToContent = () => {
    const target = document.getElementById('content-section');
    if (target) {
        gsap.to(window, {
            duration: 1.2,
            scrollTo: { y: target, offsetY: 20 },
            ease: 'power3.inOut'
        });
    }
};

onMounted(() => {
    import('gsap/ScrollToPlugin').then(({ ScrollToPlugin }) => {
        gsap.registerPlugin(ScrollToPlugin);
    });
});
</script>

<template>
    <PublicLayout>
        <!-- Hero Section -->
        <section class="relative min-h-[60vh] sm:min-h-[65vh] md:min-h-[70vh] lg:min-h-[75vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-purple-950">
            <!-- Background Effects -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-0 left-1/4 w-[250px] sm:w-[350px] md:w-[400px] h-[250px] sm:h-[350px] md:h-[400px] bg-indigo-500/20 rounded-full blur-[80px] sm:blur-[100px] animate-pulse"></div>
                <div class="absolute bottom-0 right-1/4 w-[200px] sm:w-[300px] md:w-[350px] h-[200px] sm:h-[300px] md:h-[350px] bg-purple-500/20 rounded-full blur-[60px] sm:blur-[80px] animate-pulse" style="animation-delay: 1s"></div>
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 30px 30px;"></div>
            </div>

            <!-- Hero Content -->
            <div class="container mx-auto px-3 sm:px-4 relative z-10 pt-16 sm:pt-20 pb-28 sm:pb-36 md:pb-44 lg:pb-48">
                <div class="max-w-2xl sm:max-w-3xl mx-auto text-center">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 sm:gap-2.5 px-3 sm:px-4 py-1.5 sm:py-2 mb-4 sm:mb-5 rounded-lg sm:rounded-xl bg-white/[0.08] backdrop-blur-xl border border-white/10 shadow-xl">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-md sm:rounded-lg blur-md opacity-50 group-hover:opacity-70 animate-pulse transition-opacity"></div>
                            <div class="relative w-6 h-6 sm:w-8 sm:h-8 rounded-md sm:rounded-lg bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-md">
                                <component :is="iconComponent[icon] || Shield" class="w-3 h-3 sm:w-4 sm:h-4 text-white" />
                            </div>
                        </div>
                        <div class="text-left">
                            <span class="block text-[8px] sm:text-[9px] font-bold text-indigo-300 uppercase tracking-[0.12em] sm:tracking-[0.15em]">Dokumen Legal</span>
                            <span class="block text-[10px] sm:text-xs font-medium text-white/90">TNLL Explore</span>
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-extrabold mb-3 sm:mb-4 leading-tight px-2">
                        <span class="bg-gradient-to-r from-white via-indigo-100 to-white bg-clip-text text-transparent">{{ title.split(' ')[0] }}</span>
                        <br v-if="title.split(' ').length > 1">
                        <span class="bg-gradient-to-r from-indigo-300 via-purple-300 to-pink-300 bg-clip-text text-transparent">{{ title.split(' ').slice(1).join(' ') }}</span>
                    </h1>

                    <!-- Subtitle -->
                    <p v-if="subtitle" class="text-[10px] sm:text-xs md:text-sm text-white/50 mb-3 sm:mb-4 max-w-md sm:max-w-xl mx-auto px-4 sm:px-0">{{ subtitle }}</p>

                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center justify-center gap-1.5 sm:gap-2 mb-4 sm:mb-6 text-[9px] sm:text-[10px] md:text-xs text-white/40">
                        <div class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full bg-white/5 border border-white/5">
                            <Calendar class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                            <span>{{ lastUpdated }}</span>
                        </div>
                        <div class="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full bg-white/5 border border-white/5">
                            <Clock class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                            <span>{{ readingTime }}</span>
                        </div>
                    </div>

                    <!-- Decorative Dots -->
                    <div class="flex justify-center mb-4 sm:mb-6">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <div class="w-6 sm:w-10 h-px bg-gradient-to-r from-transparent to-indigo-500/50"></div>
                            <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-indigo-500/50 animate-pulse"></div>
                            <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-purple-500/50 animate-pulse" style="animation-delay: 0.2s"></div>
                            <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-pink-500/50 animate-pulse" style="animation-delay: 0.4s"></div>
                            <div class="w-6 sm:w-10 h-px bg-gradient-to-l from-transparent to-pink-500/50"></div>
                        </div>
                    </div>

                    <!-- Mouse Scroll Indicator (Inline) -->
                    <button 
                        @click="scrollToContent"
                        class="flex flex-col items-center gap-1 sm:gap-1.5 cursor-pointer group transition-all duration-300 hover:scale-110 text-white/40 hover:text-white/60 mx-auto"
                        aria-label="Scroll ke bawah"
                    >
                        <span class="text-[8px] sm:text-[9px] uppercase tracking-widest font-medium">Scroll</span>
                        <div class="w-4 h-6 sm:w-5 sm:h-8 rounded-full border-2 border-white/20 group-hover:border-indigo-400/60 flex items-start justify-center p-1 sm:p-1.5 transition-colors duration-300">
                            <div class="w-0.5 h-1.5 sm:w-1 sm:h-2 bg-white/40 group-hover:bg-indigo-400 rounded-full animate-scroll-wheel transition-colors duration-300"></div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Enhanced Curved Wave Bottom - Multi-layer effect -->
            <div class="absolute bottom-0 left-0 right-0 overflow-hidden pointer-events-none">
                <!-- Glow Layer -->
                <div class="absolute bottom-[40px] sm:bottom-[60px] md:bottom-[80px] left-0 right-0 h-[60px] sm:h-[80px] md:h-[100px] bg-gradient-to-t from-white/20 via-white/5 to-transparent blur-xl"></div>
                
                <!-- Back Wave Layer (subtle) -->
                <svg 
                    class="absolute bottom-0 w-full h-[80px] sm:h-[100px] md:h-[130px] lg:h-[160px]" 
                    viewBox="0 0 1200 120" 
                    preserveAspectRatio="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <linearGradient id="waveGradient1" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color: rgba(99, 102, 241, 0.15)" />
                            <stop offset="50%" style="stop-color: rgba(168, 85, 247, 0.1)" />
                            <stop offset="100%" style="stop-color: rgba(236, 72, 153, 0.15)" />
                        </linearGradient>
                    </defs>
                    <path 
                        d="M0,40 C150,90 350,10 600,50 C850,90 1050,20 1200,40 L1200,120 L0,120 Z" 
                        fill="url(#waveGradient1)"
                    />
                </svg>

                <!-- Middle Wave Layer -->
                <svg 
                    class="absolute bottom-0 w-full h-[70px] sm:h-[90px] md:h-[115px] lg:h-[140px]" 
                    viewBox="0 0 1200 120" 
                    preserveAspectRatio="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <linearGradient id="waveGradient2" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color: rgba(255, 255, 255, 0.6)" />
                            <stop offset="50%" style="stop-color: rgba(238, 242, 255, 0.7)" />
                            <stop offset="100%" style="stop-color: rgba(255, 255, 255, 0.6)" />
                        </linearGradient>
                    </defs>
                    <path 
                        d="M0,30 C200,80 400,0 600,40 C800,80 1000,10 1200,30 L1200,120 L0,120 Z" 
                        fill="url(#waveGradient2)"
                    />
                </svg>

                <!-- Front Wave Layer (main) -->
                <svg 
                    class="relative block w-full h-[60px] sm:h-[80px] md:h-[100px] lg:h-[120px]" 
                    viewBox="0 0 1200 120" 
                    preserveAspectRatio="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path 
                        d="M0,20 C300,80 500,0 700,40 C900,80 1100,20 1200,20 L1200,120 L0,120 Z" 
                        fill="white"
                        class="drop-shadow-sm"
                    />
                </svg>

                <!-- Decorative floating particles -->
                <div class="absolute bottom-[80px] sm:bottom-[100px] md:bottom-[120px] left-1/4 w-2 h-2 bg-indigo-400/30 rounded-full blur-[2px] animate-float-slow"></div>
                <div class="absolute bottom-[100px] sm:bottom-[120px] md:bottom-[140px] left-1/2 w-1.5 h-1.5 bg-purple-400/40 rounded-full blur-[1px] animate-float-medium"></div>
                <div class="absolute bottom-[70px] sm:bottom-[90px] md:bottom-[110px] right-1/4 w-2.5 h-2.5 bg-pink-400/30 rounded-full blur-[2px] animate-float-fast"></div>
            </div>
        </section>

        <!-- Content Section - White Background to cover hero -->
        <section id="content-section" class="relative py-8 sm:py-12 lg:py-16 bg-white">
            <!-- Subtle decorative blobs -->
            <div class="absolute top-10 -right-20 sm:-right-32 w-[200px] sm:w-[300px] h-[200px] sm:h-[300px] bg-indigo-100/50 rounded-full blur-[80px] sm:blur-[100px]"></div>
            <div class="absolute bottom-20 -left-20 sm:-left-32 w-[200px] sm:w-[300px] h-[200px] sm:h-[300px] bg-purple-100/50 rounded-full blur-[80px] sm:blur-[100px]"></div>

            <div class="container mx-auto px-3 sm:px-4 relative z-10">
                <div class="max-w-2xl sm:max-w-3xl mx-auto">
                    <!-- Content Card -->
                    <div class="relative">
                        <div class="absolute -inset-2 sm:-inset-3 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 rounded-2xl sm:rounded-[2rem] blur-xl sm:blur-2xl opacity-50"></div>
                        
                        <div class="relative bg-white rounded-xl sm:rounded-2xl shadow-lg sm:shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                            <!-- Card Header -->
                            <div class="relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-50 via-white to-slate-50"></div>
                                <div class="absolute -top-10 sm:-top-16 -right-10 sm:-right-16 w-24 sm:w-40 h-24 sm:h-40 bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-transparent rounded-full blur-lg sm:blur-xl"></div>
                                
                                <div class="relative px-4 sm:px-5 md:px-6 py-4 sm:py-5 border-b border-slate-100">
                                    <div class="flex items-center gap-2.5 sm:gap-3">
                                        <div class="relative group">
                                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-400 via-purple-500 to-pink-500 rounded-lg sm:rounded-xl blur-md sm:blur-lg opacity-40 group-hover:opacity-60 transition-all"></div>
                                            <div class="relative w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-md sm:shadow-lg group-hover:scale-105 transition-transform">
                                                <component :is="iconComponent[icon] || Shield" class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="text-base sm:text-lg md:text-xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">{{ title }}</h2>
                                            <p class="text-slate-400 text-[10px] sm:text-xs hidden sm:block">{{ subtitle || 'Perlindungan data dan privasi Anda' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Body -->
                            <div class="p-4 sm:p-5 md:p-6 lg:p-8">
                                <article v-if="content" class="prose prose-slate prose-sm max-w-none policy-content" v-html="content"></article>
                                <div v-else class="text-center py-8 sm:py-12">
                                    <div class="relative inline-flex mb-4 sm:mb-5">
                                        <div class="absolute inset-0 bg-gradient-to-br from-slate-200 to-slate-100 rounded-xl sm:rounded-2xl blur-md sm:blur-lg opacity-50"></div>
                                        <div class="relative w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-slate-100 to-white flex items-center justify-center border border-slate-100 shadow-md">
                                            <component :is="iconComponent[icon] || Shield" class="w-6 h-6 sm:w-8 sm:h-8 text-slate-300" />
                                        </div>
                                    </div>
                                    <p class="text-slate-400 text-xs sm:text-sm mb-1">Konten belum ditambahkan.</p>
                                    <p class="text-slate-300 text-[10px] sm:text-xs">Silakan edit di panel admin.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-3">
                        <Link :href="backLink.href" class="w-full sm:w-auto group inline-flex items-center justify-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-lg sm:rounded-xl bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5">
                            <ArrowLeft class="w-3.5 h-3.5 sm:w-4 sm:h-4 group-hover:-translate-x-0.5 transition-transform" />
                            <span>{{ backLink.text }}</span>
                        </Link>
                        <Link v-if="nextLink" :href="nextLink.href" class="w-full sm:w-auto group relative inline-flex items-center justify-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-white font-medium text-xs sm:text-sm shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <span class="relative">{{ nextLink.text }}</span>
                            <ArrowRight class="relative w-3.5 h-3.5 sm:w-4 sm:h-4 group-hover:translate-x-0.5 transition-transform" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style>
/* Mouse Scroll Animation */
@keyframes scroll-wheel {
    0%, 100% { opacity: 0.6; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(3px); }
}
.animate-scroll-wheel { animation: scroll-wheel 1.5s ease-in-out infinite; }

/* Floating Particles Animation */
@keyframes float-slow {
    0%, 100% { transform: translateY(0) translateX(0); opacity: 0.3; }
    25% { transform: translateY(-8px) translateX(4px); opacity: 0.5; }
    50% { transform: translateY(-15px) translateX(0); opacity: 0.4; }
    75% { transform: translateY(-8px) translateX(-4px); opacity: 0.5; }
}
@keyframes float-medium {
    0%, 100% { transform: translateY(0) translateX(0); opacity: 0.4; }
    33% { transform: translateY(-12px) translateX(-6px); opacity: 0.6; }
    66% { transform: translateY(-6px) translateX(6px); opacity: 0.5; }
}
@keyframes float-fast {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; }
    50% { transform: translateY(-10px) scale(1.2); opacity: 0.5; }
}
.animate-float-slow { animation: float-slow 4s ease-in-out infinite; }
.animate-float-medium { animation: float-medium 3s ease-in-out infinite; animation-delay: 0.5s; }
.animate-float-fast { animation: float-fast 2.5s ease-in-out infinite; animation-delay: 1s; }

/* Policy Content - Responsive */
.policy-content { font-size: 0.8125rem; line-height: 1.7; color: #475569; }
@media (min-width: 640px) { .policy-content { font-size: 0.875rem; line-height: 1.75; } }

.policy-content h1 { font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid; border-image: linear-gradient(90deg, #6366f1, #a855f7, #ec4899) 1; }
@media (min-width: 640px) { .policy-content h1 { font-size: 1.375rem; margin: 1.75rem 0 0.875rem; } }

.policy-content h2 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 1.25rem 0 0.5rem; padding-bottom: 0.25rem; border-bottom: 1px solid #e2e8f0; position: relative; display: inline-block; }
@media (min-width: 640px) { .policy-content h2 { font-size: 1.125rem; margin: 1.5rem 0 0.625rem; padding-bottom: 0.375rem; } }
.policy-content h2::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 30px; height: 2px; background: linear-gradient(90deg, #6366f1, #a855f7); border-radius: 1px; }
@media (min-width: 640px) { .policy-content h2::after { width: 40px; } }

.policy-content h3 { font-size: 0.875rem; font-weight: 600; color: #334155; margin: 1rem 0 0.375rem; display: flex; align-items: center; gap: 0.25rem; }
@media (min-width: 640px) { .policy-content h3 { font-size: 0.9375rem; margin: 1.25rem 0 0.5rem; gap: 0.375rem; } }
.policy-content h3::before { content: ''; width: 2px; height: 0.875rem; background: linear-gradient(180deg, #6366f1, #a855f7); border-radius: 1px; }
@media (min-width: 640px) { .policy-content h3::before { width: 3px; height: 0.9375rem; } }

.policy-content p { margin: 0.625rem 0; }
@media (min-width: 640px) { .policy-content p { margin: 0.75rem 0; } }

.policy-content ul, .policy-content ol { margin: 0.75rem 0; padding-left: 0; }
@media (min-width: 640px) { .policy-content ul, .policy-content ol { margin: 0.875rem 0; } }

.policy-content li { margin: 0.375rem 0; padding-left: 1.25rem; position: relative; list-style: none; transition: transform 0.2s; }
@media (min-width: 640px) { .policy-content li { margin: 0.5rem 0; padding-left: 1.5rem; } }
.policy-content li:hover { transform: translateX(2px); }

.policy-content ul li::before { content: ''; position: absolute; left: 0.25rem; top: 0.5em; width: 4px; height: 4px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 50%; box-shadow: 0 0 4px rgba(99, 102, 241, 0.3); }
@media (min-width: 640px) { .policy-content ul li::before { left: 0.375rem; width: 5px; height: 5px; box-shadow: 0 0 6px rgba(99, 102, 241, 0.3); } }

.policy-content a { color: #6366f1; text-decoration: none; font-weight: 500; background: linear-gradient(90deg, #6366f1, #a855f7); background-size: 0% 1px; background-repeat: no-repeat; background-position: left bottom; transition: background-size 0.3s; }
.policy-content a:hover { background-size: 100% 1px; }

.policy-content blockquote { background: linear-gradient(135deg, #eef2ff 0%, #faf5ff 100%); border-left: 2px solid; border-image: linear-gradient(180deg, #6366f1, #a855f7) 1; padding: 0.75rem 1rem; margin: 1rem 0; border-radius: 0 0.5rem 0.5rem 0; color: #4338ca; font-size: 0.75rem; }
@media (min-width: 640px) { .policy-content blockquote { border-left-width: 3px; padding: 1rem 1.25rem; margin: 1.25rem 0; border-radius: 0 0.75rem 0.75rem 0; font-size: 0.8125rem; } }

.policy-content strong { color: #1e293b; font-weight: 600; }
</style>
