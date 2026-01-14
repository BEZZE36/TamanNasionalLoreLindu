<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    ChevronDown, HelpCircle, ArrowRight,
    Lightbulb, Clock, Phone, Mail
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ faqItems: Array });
const expandedFaq = ref(null);
const sectionRef = ref(null);
let ctx;

const toggleFaq = (i) => { 
    expandedFaq.value = expandedFaq.value === i ? null : i; 
};

// Display 6 FAQs
const displayFaqs = computed(() => {
    if (!props.faqItems) return [];
    return props.faqItems.slice(0, 6);
});

// Quick Contact Info
const contactInfo = [
    { icon: Phone, label: 'Telepon', value: '(0451) 123-456', color: 'emerald' },
    { icon: Mail, label: 'Email', value: 'info@tnll.go.id', color: 'teal' },
    { icon: Clock, label: 'Jam Operasional', value: '08:00 - 17:00 WITA', color: 'cyan' }
];

onMounted(() => {
    ctx = gsap.context(() => {
        // Header entrance
        gsap.fromTo('.faq-header > *', 
            { opacity: 0, y: 25, scale: 0.98 }, 
            { 
                opacity: 1, y: 0, scale: 1, 
                duration: 0.6, stagger: 0.1, 
                ease: 'power2.out', 
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
            { 
                opacity: 1, x: 0, scale: 1, 
                duration: 0.5, stagger: 0.08, 
                ease: 'power2.out', 
                scrollTrigger: { 
                    trigger: sectionRef.value, 
                    start: 'top 80%',
                    end: 'bottom top',
                    toggleActions: 'play reverse play reverse'
                } 
            }
        );

        // Contact cards
        gsap.fromTo('.contact-card', 
            { opacity: 0, y: 25 }, 
            { 
                opacity: 1, y: 0, 
                duration: 0.5, stagger: 0.08, 
                ease: 'power2.out', 
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

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <section ref="sectionRef" class="relative py-16 md:py-20 lg:py-24 bg-gradient-to-br from-gray-50 via-white to-emerald-50/30 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 right-0 w-48 sm:w-64 h-48 sm:h-64 bg-gradient-to-br from-emerald-200/25 to-teal-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
            <div class="absolute bottom-1/4 left-0 w-44 sm:w-56 h-44 sm:h-56 bg-gradient-to-tr from-cyan-200/20 to-blue-200/10 rounded-full blur-2xl sm:blur-3xl"></div>
        </div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="faq-header text-center mb-8 sm:mb-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 mb-2 sm:mb-3 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-300">
                    <HelpCircle class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide">FAQ</span>
                </div>
                
                <!-- Title -->
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                    Pertanyaan 
                    <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        Umum
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto">Jawaban untuk pertanyaan yang sering diajukan</p>
            </div>

            <!-- FAQ List -->
            <div v-if="displayFaqs.length > 0" class="faq-list space-y-3 mb-8">
                <div 
                    v-for="(faq, i) in displayFaqs" 
                    :key="i" 
                    class="faq-item group"
                >
                    <div :class="[
                        'relative bg-white rounded-xl sm:rounded-2xl overflow-hidden transition-all duration-300',
                        expandedFaq === i 
                            ? 'shadow-xl ring-2 ring-emerald-200' 
                            : 'shadow-lg hover:shadow-xl hover:-translate-y-0.5'
                    ]">
                        <!-- Question Button -->
                        <button 
                            @click="toggleFaq(i)" 
                            class="w-full flex items-center justify-between p-4 sm:p-5 text-left"
                        >
                            <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                <!-- Number Badge -->
                                <div :class="[
                                    'flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center text-xs sm:text-sm font-bold transition-all duration-300',
                                    expandedFaq === i 
                                        ? 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg' 
                                        : 'bg-gray-100 text-gray-500 group-hover:bg-emerald-50 group-hover:text-emerald-600'
                                ]">
                                    {{ String(i + 1).padStart(2, '0') }}
                                </div>
                                
                                <!-- Question Text -->
                                <h3 :class="[
                                    'text-xs sm:text-sm font-bold transition-colors duration-300 pr-2',
                                    expandedFaq === i ? 'text-emerald-600' : 'text-gray-900 group-hover:text-emerald-600'
                                ]">
                                    {{ faq.question }}
                                </h3>
                            </div>
                            
                            <!-- Expand Icon -->
                            <div :class="[
                                'flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center transition-all duration-300',
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
                                <div class="px-4 sm:px-5 pb-4 sm:pb-5">
                                    <div class="pt-3 pl-11 sm:pl-14 border-t border-gray-100">
                                        <div class="flex gap-2 sm:gap-3">
                                            <div class="flex-shrink-0 w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-50 flex items-center justify-center">
                                                <Lightbulb class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-600" />
                                            </div>
                                            <p class="text-gray-600 leading-relaxed text-xs sm:text-sm flex-1">
                                                {{ faq.answer }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-10 bg-white/60 backdrop-blur-sm rounded-xl border border-gray-100 mb-8">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center mx-auto mb-3">
                    <HelpCircle class="w-5 h-5 text-gray-400" />
                </div>
                <h3 class="text-sm font-bold text-gray-700 mb-1">Belum Ada FAQ</h3>
                <p class="text-gray-500 text-xs">Pertanyaan umum akan segera ditampilkan.</p>
            </div>

            <!-- Quick Contact Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
                <div 
                    v-for="(contact, index) in contactInfo" 
                    :key="index"
                    class="contact-card group bg-white rounded-xl p-3 sm:p-4 shadow-md hover:shadow-lg border border-gray-100 hover:border-emerald-200 hover:-translate-y-0.5 transition-all duration-300"
                >
                    <div :class="[
                        'w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center mb-2 transition-transform duration-300 group-hover:scale-110',
                        contact.color === 'emerald' ? 'bg-gradient-to-br from-emerald-100 to-emerald-50' : '',
                        contact.color === 'teal' ? 'bg-gradient-to-br from-teal-100 to-teal-50' : '',
                        contact.color === 'cyan' ? 'bg-gradient-to-br from-cyan-100 to-cyan-50' : ''
                    ]">
                        <component 
                            :is="contact.icon" 
                            :class="[
                                'w-4 h-4 sm:w-5 sm:h-5',
                                contact.color === 'emerald' ? 'text-emerald-600' : '',
                                contact.color === 'teal' ? 'text-teal-600' : '',
                                contact.color === 'cyan' ? 'text-cyan-600' : ''
                            ]" 
                        />
                    </div>
                    <p class="text-[10px] text-gray-400 font-medium mb-0.5">{{ contact.label }}</p>
                    <p :class="[
                        'text-xs sm:text-sm font-bold',
                        contact.color === 'emerald' ? 'text-emerald-700' : '',
                        contact.color === 'teal' ? 'text-teal-700' : '',
                        contact.color === 'cyan' ? 'text-cyan-700' : ''
                    ]">{{ contact.value }}</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center">
                <Link href="/faq" class="group inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-emerald-600 font-semibold text-xs sm:text-sm hover:bg-emerald-50 hover:shadow-sm transition-all duration-300">
                    <HelpCircle class="w-4 h-4" />
                    <span>Lihat Semua FAQ</span>
                    <ArrowRight class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
    </section>
</template>
