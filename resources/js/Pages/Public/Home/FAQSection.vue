<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ChevronDown, MessageCircle, HelpCircle, ArrowRight, Sparkles } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ faqItems: Array });
const expandedFaq = ref(null);
const sectionRef = ref(null);
let ctx;

const toggleFaq = (i) => { 
    expandedFaq.value = expandedFaq.value === i ? null : i; 
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Header entrance - stays visible until section leaves
        gsap.fromTo('.faq-header > *', 
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
        
        // FAQ items
        gsap.fromTo('.faq-item', 
            { opacity: 0, x: -30, scale: 0.98 }, 
            { opacity: 1, x: 0, scale: 1, duration: 0.5, stagger: 0.08, ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-br from-gray-50 via-white to-emerald-50/30 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 right-0 w-64 h-64 bg-gradient-to-br from-emerald-200/25 to-teal-200/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 left-0 w-56 h-56 bg-gradient-to-tr from-cyan-200/20 to-blue-200/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="faq-header text-center mb-10">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <HelpCircle class="w-3.5 h-3.5" />
                    <span class="text-[11px] font-semibold tracking-wide">FAQ</span>
                    <Sparkles class="w-3 h-3 text-emerald-500" />
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-2">
                    Pertanyaan 
                    <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        Umum
                    </span>
                </h2>
                <p class="text-sm text-gray-500 max-w-md mx-auto">Jawaban untuk pertanyaan yang sering diajukan</p>
            </div>

            <!-- FAQ List -->
            <div class="faq-list space-y-3">
                <div 
                    v-for="(faq, i) in faqItems?.slice(0, 5)" 
                    :key="i" 
                    class="faq-item group"
                >
                    <div :class="[
                        'relative bg-white rounded-2xl overflow-hidden transition-all duration-300',
                        expandedFaq === i 
                            ? 'shadow-xl ring-2 ring-emerald-200' 
                            : 'shadow-lg hover:shadow-xl hover:-translate-y-0.5'
                    ]">
                        <div class="relative bg-white rounded-2xl overflow-hidden">
                            <!-- Question Button -->
                            <button 
                                @click="toggleFaq(i)" 
                                class="w-full flex items-center justify-between p-5 text-left"
                            >
                                <div class="flex items-center gap-4">
                                    <!-- Number Badge -->
                                    <div :class="[
                                        'flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold transition-all duration-300',
                                        expandedFaq === i 
                                            ? 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg' 
                                            : 'bg-gray-100 text-gray-500 group-hover:bg-emerald-50 group-hover:text-emerald-600'
                                    ]">
                                        {{ String(i + 1).padStart(2, '0') }}
                                    </div>
                                    
                                    <!-- Question Text -->
                                    <h3 :class="[
                                        'text-sm sm:text-base font-bold transition-colors pr-4',
                                        expandedFaq === i ? 'text-emerald-600' : 'text-gray-900 group-hover:text-emerald-600'
                                    ]">
                                        {{ faq.question }}
                                    </h3>
                                </div>
                                
                                <!-- Expand Icon -->
                                <div :class="[
                                    'flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300',
                                    expandedFaq === i 
                                        ? 'bg-emerald-500 text-white rotate-180 shadow-lg' 
                                        : 'bg-gray-100 text-gray-500 group-hover:bg-emerald-50 group-hover:text-emerald-600'
                                ]">
                                    <ChevronDown class="w-4 h-4" />
                                </div>
                            </button>
                            
                            <!-- Answer Content -->
                            <Transition 
                                enter-active-class="transition-all duration-300 ease-out overflow-hidden" 
                                enter-from-class="max-h-0 opacity-0" 
                                enter-to-class="max-h-96 opacity-100" 
                                leave-active-class="transition-all duration-200 ease-in overflow-hidden" 
                                leave-from-class="max-h-96 opacity-100" 
                                leave-to-class="max-h-0 opacity-0"
                            >
                                <div v-if="expandedFaq === i">
                                    <div class="px-5 pb-5">
                                        <div class="pt-4 border-t border-gray-100">
                                            <p class="text-gray-600 leading-relaxed text-sm">
                                                {{ faq.answer }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center mt-10">
                <Link href="/faq" class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-emerald-600 font-semibold text-sm hover:bg-emerald-50 hover:shadow-sm transition-all duration-300">
                    <MessageCircle class="w-4 h-4" />
                    <span>Lihat Semua FAQ</span>
                    <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
    </section>
</template>
