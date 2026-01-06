<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Zap, QrCode, Leaf, Landmark, ShieldCheck, Sparkles, Check, Star, Award, ArrowRight } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
let ctx;

const features = [
    { icon: QrCode, title: 'Tiket Digital QR', description: 'Tunjukkan QR dari smartphone Anda untuk akses cepat.', color: 'cyan', stats: '2 min' },
    { icon: Leaf, title: 'Satwa Endemik', description: '267+ spesies burung & mamalia langka menanti Anda.', color: 'emerald', stats: '267+' },
    { icon: Landmark, title: 'Situs Megalitikum', description: 'Patung batu purba di Lembah Bada, Besoa, dan Napu.', color: 'amber', stats: '1000+' },
    { icon: ShieldCheck, title: 'Resmi & Terpercaya', description: 'Kerjasama resmi dengan Balai Besar TNLL.', color: 'teal', stats: '100%' }
];

const mainFeatures = ['Bayar Online Mudah', 'Konfirmasi Instan', 'Support 24/7'];

onMounted(() => {
    ctx = gsap.context(() => {
        // Header entrance - stays visible until section leaves
        gsap.fromTo('.why-header > *', 
            { opacity: 0, y: 25 }, 
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 85%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
        
        // Main card
        gsap.fromTo('.why-main-card', 
            { opacity: 0, x: -40, scale: 0.95 }, 
            { opacity: 1, x: 0, scale: 1, duration: 0.7, ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
        
        // Feature cards
        gsap.fromTo('.why-feature-card', 
            { opacity: 0, y: 30, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'back.out(1.2)', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 75%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const getColorClasses = (color) => {
    const colors = { 
        cyan: { 
            icon: 'bg-gradient-to-br from-cyan-100 to-cyan-50 text-cyan-600', 
            hover: 'hover:border-cyan-300 hover:shadow-cyan-100/50',
            stat: 'text-cyan-600 bg-cyan-50'
        }, 
        emerald: { 
            icon: 'bg-gradient-to-br from-emerald-100 to-emerald-50 text-emerald-600', 
            hover: 'hover:border-emerald-300 hover:shadow-emerald-100/50',
            stat: 'text-emerald-600 bg-emerald-50'
        }, 
        amber: { 
            icon: 'bg-gradient-to-br from-amber-100 to-amber-50 text-amber-600', 
            hover: 'hover:border-amber-300 hover:shadow-amber-100/50',
            stat: 'text-amber-600 bg-amber-50'
        }, 
        teal: { 
            icon: 'bg-gradient-to-br from-teal-100 to-teal-50 text-teal-600', 
            hover: 'hover:border-teal-300 hover:shadow-teal-100/50',
            stat: 'text-teal-600 bg-teal-50'
        } 
    };
    return colors[color] || colors.emerald;
};
</script>

<template>
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-b from-white via-emerald-50/30 to-white overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-0 w-72 h-72 bg-gradient-to-br from-emerald-200/30 to-teal-200/15 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-gradient-to-tl from-teal-200/25 to-cyan-200/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="why-header mb-10">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <Award class="w-3.5 h-3.5" />
                    <span class="text-[11px] font-semibold tracking-wide">Mengapa Kami</span>
                    <Sparkles class="w-3 h-3 text-emerald-500" />
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-2">
                    Kenapa Harus 
                    <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        TNLL Explore?
                    </span>
                </h2>
                <p class="text-sm text-gray-500 max-w-lg">Platform resmi untuk pengalaman terbaik menjelajahi keindahan Taman Nasional Lore Lindu</p>
            </div>

            <div class="why-grid grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
                <!-- Main Card -->
                <div class="why-main-card lg:col-span-5">
                    <div class="h-full bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-6 sm:p-7 text-white relative overflow-hidden shadow-2xl shadow-emerald-600/20 hover:shadow-emerald-600/30 transition-shadow duration-500">
                        <!-- Background Elements -->
                        <div class="absolute top-0 right-0 w-28 h-28 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        
                        <div class="relative z-10">
                            <!-- Icon -->
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center mb-5 shadow-lg">
                                <Zap class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-xl sm:text-2xl font-bold mb-3">Pemesanan Instan</h3>
                            
                            <!-- Description -->
                            <p class="text-white/80 text-sm leading-relaxed mb-5">
                                Tidak perlu antri di loket. Pesan tiket kapan saja, di mana saja. Proses cepat kurang dari 2 menit dengan pembayaran aman.
                            </p>
                            
                            <!-- Feature Tags -->
                            <div class="flex flex-wrap gap-2 mb-6">
                                <span v-for="f in mainFeatures" :key="f" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 backdrop-blur-sm text-[11px] font-medium ring-1 ring-white/10 hover:bg-white/20 transition-colors">
                                    <Check class="w-3 h-3 text-emerald-300" />
                                    {{ f }}
                                </span>
                            </div>
                            
                            <!-- Testimonial Preview -->
                            <div class="pt-5 border-t border-white/15">
                                <div class="flex items-center gap-3">
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 rounded-full bg-white/20 ring-2 ring-white/30 flex items-center justify-center text-[10px] font-bold">AS</div>
                                        <div class="w-8 h-8 rounded-full bg-white/20 ring-2 ring-white/30 flex items-center justify-center text-[10px] font-bold">SR</div>
                                        <div class="w-8 h-8 rounded-full bg-white/20 ring-2 ring-white/30 flex items-center justify-center text-[10px] font-bold">+</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-1 mb-0.5">
                                            <Star v-for="i in 5" :key="i" class="w-3 h-3 text-amber-400 fill-current" />
                                        </div>
                                        <p class="text-[10px] text-white/60">500+ pengunjung puas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Cards Grid -->
                <div class="why-features-grid lg:col-span-7 grid grid-cols-2 gap-3 lg:gap-4">
                    <div 
                        v-for="f in features" 
                        :key="f.title" 
                        :class="[
                            'why-feature-card group relative p-4 sm:p-5 rounded-2xl bg-white border border-gray-100 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300', 
                            getColorClasses(f.color).hover
                        ]"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <!-- Icon -->
                            <div :class="['w-11 h-11 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300', getColorClasses(f.color).icon]">
                                <component :is="f.icon" class="w-5 h-5" />
                            </div>
                            
                            <!-- Stats Badge -->
                            <span :class="['px-2 py-1 rounded-lg text-[10px] font-bold', getColorClasses(f.color).stat]">
                                {{ f.stats }}
                            </span>
                        </div>
                        
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 mb-1.5 group-hover:text-emerald-600 transition-colors">
                            {{ f.title }}
                        </h3>
                        <p class="text-gray-500 text-xs leading-relaxed">
                            {{ f.description }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- CTA Section -->
            <div class="mt-10 text-center">
                <Link 
                    href="/about" 
                    class="group inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-sm shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300"
                >
                    <span>Pelajari Lebih Lanjut</span>
                    <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
    </section>
</template>
