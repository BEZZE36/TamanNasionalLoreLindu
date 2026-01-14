<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Trees, Bird, Award, Play, Sparkles, Globe, Shield } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const counters = ref({ hectares: 0, birds: 0, unesco: 0 });
const sectionRef = ref(null);
const videoRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        // Content entrance - stays visible until section leaves viewport
        gsap.fromTo('.info-content-item', 
            { opacity: 0, y: 25, scale: 0.98 },
            { opacity: 1, y: 0, scale: 1, duration: 0.6, stagger: 0.1, ease: 'power2.out',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%', 
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse',
                    onEnter: () => animateCounters(),
                    onEnterBack: () => animateCounters()
                }
            }
        );
        
        // Stats cards
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 30, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'back.out(1.2)',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                }
            }
        );
        
        // Video entrance
        gsap.fromTo(videoRef.value, 
            { opacity: 0, x: 40, scale: 0.96 },
            { opacity: 1, x: 0, scale: 1, duration: 0.7, ease: 'power2.out',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 75%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                }
            }
        );
        
        // Award badges
        gsap.fromTo('.award-badge', 
            { opacity: 0, y: 20, scale: 0.9 },
            { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.1, ease: 'back.out(1.5)',
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 70%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                }
            }
        );
    }, sectionRef.value);
});

const animateCounters = () => {
    gsap.to(counters.value, { hectares: 217, duration: 1.5, ease: 'power2.out', snap: { hectares: 1 } });
    gsap.to(counters.value, { birds: 267, duration: 1.5, ease: 'power2.out', snap: { birds: 1 } });
    gsap.to(counters.value, { unesco: 1977, duration: 1.8, ease: 'power2.out', snap: { unesco: 1 } });
};

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section id="video-info-section" ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-emerald-50/30 to-white overflow-hidden">
        <!-- Background Elements - Responsive -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-8 sm:-top-12 lg:-top-16 -right-8 sm:-right-12 lg:-right-16 w-48 sm:w-64 lg:w-80 h-48 sm:h-64 lg:h-80 bg-gradient-to-br from-emerald-200/40 to-teal-200/20 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute -bottom-12 sm:-bottom-16 lg:-bottom-20 -left-12 sm:-left-16 lg:-left-20 w-44 sm:w-56 lg:w-72 h-44 sm:h-56 lg:h-72 bg-gradient-to-tr from-cyan-200/30 to-blue-200/15 rounded-full blur-2xl sm:blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-16 items-center">
                
                <!-- Content Side -->
                <div class="order-1 lg:order-1">
                    <!-- Premium Badge - Responsive -->
                    <div class="info-content-item mb-3 sm:mb-4">
                        <span class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                            <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">Jantung Wallacea</span>
                            <Globe class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-emerald-500" />
                        </span>
                    </div>

                    <!-- Title - Responsive -->
                    <h2 class="info-content-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 leading-tight mb-3 sm:mb-4">
                        Taman Nasional 
                        <span class="block mt-0.5 sm:mt-1 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                            Lore Lindu
                        </span>
                    </h2>

                    <!-- Description - Responsive -->
                    <div class="info-content-item space-y-2 sm:space-y-3 mb-4 sm:mb-6">
                        <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-white/60 backdrop-blur-sm border border-white/80 shadow-sm hover:shadow-md transition-shadow duration-300">
                            <p class="text-gray-700 text-xs sm:text-sm leading-relaxed">
                                Terletak di jantung Sulawesi, TNLL adalah rumah bagi kekayaan hayati yang tidak ditemukan di tempat lain di dunia. Lebih dari <span class="font-bold text-emerald-600">217.000 hektar</span> hutan hujan tropis menyimpan misteri evolusi ribuan tahun.
                            </p>
                        </div>
                        <p class="text-gray-500 text-[10px] sm:text-xs leading-relaxed pl-1">
                            Dari Anoa yang pemalu hingga Maleo yang misterius, dari patung megalith purba hingga danau-danau biru yang memukau.
                        </p>
                    </div>
                </div>

                <!-- Video Side - Shows after description on mobile, on right side on desktop -->
                <div ref="videoRef" class="order-2 lg:order-2">
                    <div class="relative">
                        <!-- Video Container - Responsive -->
                        <div class="relative rounded-xl sm:rounded-2xl overflow-hidden shadow-xl sm:shadow-2xl shadow-gray-200/60 ring-1 ring-gray-100 group hover:shadow-emerald-200/40 transition-shadow duration-500">
                            <div class="relative bg-gray-900 rounded-2xl overflow-hidden">
                                <div class="aspect-video">
                                    <iframe 
                                        class="w-full h-full" 
                                        src="https://www.youtube.com/embed/JGue71f1lBM?si=aJl_QgFj3UukPrXf" 
                                        title="TNLL Video" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen 
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Video Credit - Responsive -->
                        <div class="flex items-center justify-center gap-1.5 sm:gap-2 mt-3 sm:mt-4">
                            <div class="h-px flex-1 max-w-8 sm:max-w-12 bg-gradient-to-r from-transparent to-gray-200"></div>
                            <p class="flex items-center gap-1 sm:gap-1.5 text-[9px] sm:text-[10px] text-gray-400 font-medium">
                                <Play class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
                                <span>Video by National Geographic</span>
                            </p>
                            <div class="h-px flex-1 max-w-8 sm:max-w-12 bg-gradient-to-l from-transparent to-gray-200"></div>
                        </div>
                    </div>
                </div>

                <!-- Stats and Badges - Full width on mobile below video, left column on desktop -->
                <div class="order-3 lg:order-3 lg:col-span-1">
                    <!-- Stats Grid - Responsive -->
                    <div class="stats-grid grid grid-cols-3 gap-2 sm:gap-3 mb-4 sm:mb-6">
                        <div class="stat-card group relative p-2.5 sm:p-3 lg:p-4 rounded-lg sm:rounded-xl bg-gradient-to-br from-white to-emerald-50/50 border border-emerald-100/60 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center mb-1.5 sm:mb-2 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <Trees class="w-3.5 h-3.5 sm:w-4 sm:h-4 lg:w-5 lg:h-5 text-emerald-600" />
                            </div>
                            <p class="text-lg sm:text-xl lg:text-2xl font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ counters.hectares }}K</p>
                            <p class="text-[9px] sm:text-[10px] text-gray-500 font-medium uppercase tracking-wide">Hektar</p>
                        </div>
                        
                        <div class="stat-card group relative p-2.5 sm:p-3 lg:p-4 rounded-lg sm:rounded-xl bg-gradient-to-br from-white to-cyan-50/50 border border-cyan-100/60 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-cyan-100 to-cyan-50 flex items-center justify-center mb-1.5 sm:mb-2 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <Bird class="w-3.5 h-3.5 sm:w-4 sm:h-4 lg:w-5 lg:h-5 text-cyan-600" />
                            </div>
                            <p class="text-lg sm:text-xl lg:text-2xl font-black bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">{{ counters.birds }}+</p>
                            <p class="text-[9px] sm:text-[10px] text-gray-500 font-medium uppercase tracking-wide">Spesies</p>
                        </div>
                        
                        <div class="stat-card group relative p-2.5 sm:p-3 lg:p-4 rounded-lg sm:rounded-xl bg-gradient-to-br from-white to-amber-50/50 border border-amber-100/60 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center mb-1.5 sm:mb-2 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <Award class="w-3.5 h-3.5 sm:w-4 sm:h-4 lg:w-5 lg:h-5 text-amber-600" />
                            </div>
                            <p class="text-lg sm:text-xl lg:text-2xl font-black bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">{{ counters.unesco }}</p>
                            <p class="text-[9px] sm:text-[10px] text-gray-500 font-medium uppercase tracking-wide">UNESCO</p>
                        </div>
                    </div>

                    <!-- Award Badges - Responsive -->
                    <div class="badges-container flex flex-wrap gap-1.5 sm:gap-2">
                        <div class="award-badge group flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-full bg-white border border-gray-200 shadow-sm hover:shadow-lg hover:border-blue-300 hover:-translate-y-0.5 transition-all duration-300 cursor-default">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                                <Globe class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" />
                            </div>
                            <span class="text-[10px] sm:text-xs font-semibold text-gray-700">UNESCO Biosphere</span>
                        </div>
                        <div class="award-badge group flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-full bg-white border border-gray-200 shadow-sm hover:shadow-lg hover:border-teal-300 hover:-translate-y-0.5 transition-all duration-300 cursor-default">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7 rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-sm">
                                <Shield class="w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 text-white" />
                            </div>
                            <span class="text-[10px] sm:text-xs font-semibold text-gray-700">ASEAN Heritage</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
