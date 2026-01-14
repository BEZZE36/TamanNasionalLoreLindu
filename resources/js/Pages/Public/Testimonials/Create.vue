<script setup>
/**
 * Create.vue - Premium Create Testimonial Page
 * Features: Dark gradient hero, GSAP animations, glassmorphism form, responsive
 * Fixed: Uses defineOptions for proper Inertia layout
 */
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
    ArrowLeft, Star, Send, AlertTriangle, MapPin, Info, 
    Quote, CheckCircle, Loader2, PenLine, Sparkles, User, Eye, EyeOff
} from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

defineOptions({ layout: PublicLayout });

const props = defineProps({
    hasBooking: Boolean,
    destinations: Array,
});

const heroRef = ref(null);
const backgroundRef = ref(null);
const contentRef = ref(null);
const formRef = ref(null);
const rating = ref(5);
const hoveredStar = ref(0);
let ctx;

const form = useForm({
    destination_id: '',
    rating: 5,
    message: '',
    is_anonymous: false,
});

const messageLength = computed(() => form.message.length);
const isMessageValid = computed(() => messageLength.value >= 20 && messageLength.value <= 1000);

const setRating = (value) => {
    rating.value = value;
    form.rating = value;
    
    // Animate star selection
    gsap.to('.star-btn', {
        scale: 1,
        duration: 0.2,
        ease: 'power2.out'
    });
    gsap.to(`.star-btn-${value}`, {
        scale: 1.15,
        duration: 0.3,
        ease: 'elastic.out(1, 0.5)'
    });
};

const getRatingText = () => {
    const texts = {
        1: 'Sangat Buruk 😞',
        2: 'Buruk 😕',
        3: 'Cukup 😐',
        4: 'Bagus 😊',
        5: 'Sangat Bagus! 🤩'
    };
    return texts[rating.value] || '';
};

const submit = () => {
    form.post('/testimonials');
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Hero animations
        gsap.fromTo('.hero-item', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out', delay: 0.3 }
        );

        // Form entrance animation
        if (formRef.value) {
            gsap.fromTo(formRef.value,
                { opacity: 0, y: 30 },
                { opacity: 1, y: 0, duration: 0.6, delay: 0.5, ease: 'power3.out' }
            );
        }

        // Floating shapes
        gsap.to('.float-shape', {
            y: -15,
            duration: 3,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: { each: 0.5, from: 'random' }
        });

        // Parallax effects
        if (backgroundRef.value) {
            gsap.to(backgroundRef.value, {
                yPercent: 30,
                ease: 'none',
                scrollTrigger: {
                    trigger: heroRef.value,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: true
                }
            });
        }
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <Head title="Tulis Ulasan - Taman Nasional Lore Lindu" />

    <div class="min-h-screen">
        <!-- Hero Section -->
        <section ref="heroRef" class="relative min-h-[45vh] sm:min-h-[50vh] flex items-center overflow-hidden pt-20 pb-12">
            <!-- Background -->
            <div ref="backgroundRef" class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-pink-950 to-slate-900"></div>
                
                <!-- Mesh Gradient -->
                <div class="absolute inset-0 opacity-60">
                    <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(ellipse_at_20%_30%,rgba(236,72,153,0.15),transparent_50%)]"></div>
                    <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_80%_60%,rgba(168,85,247,0.12),transparent_50%)]"></div>
                </div>

                <!-- Floating Shapes -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="float-shape absolute top-[20%] left-[10%] w-16 h-16 border border-white/[0.06] rounded-2xl rotate-12"></div>
                    <div class="float-shape absolute top-[30%] right-[15%] w-12 h-12 border border-pink-500/10 rounded-full"></div>
                    <div class="float-shape absolute bottom-[30%] left-[20%] w-10 h-10 bg-gradient-to-br from-fuchsia-500/5 to-pink-500/5 rounded-lg rotate-45"></div>
                </div>

                <!-- Grid Pattern -->
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>

                <!-- Wave SVG -->
                <svg class="absolute -bottom-1 left-0 right-0 w-full h-32 sm:h-40" viewBox="0 0 1440 200" fill="none" preserveAspectRatio="none">
                    <path d="M0,140L48,135C96,130,192,120,288,115C384,110,480,110,576,120C672,130,768,150,864,155C960,160,1056,150,1152,135C1248,120,1344,100,1392,90L1440,80L1440,200L0,200Z" fill="rgba(236,72,153,0.03)"/>
                    <path d="M0,160L60,155C120,150,240,140,360,145C480,150,600,170,720,175C840,180,960,170,1080,160C1200,150,1320,140,1380,135L1440,130L1440,200L0,200Z" fill="#f9fafb"/>
                </svg>
            </div>

            <!-- Content -->
            <div ref="contentRef" class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="max-w-2xl">
                    <!-- Back Link -->
                    <Link 
                        href="/testimonials" 
                        class="hero-item inline-flex items-center gap-2 text-white/70 hover:text-white text-xs sm:text-sm mb-4 transition-colors group"
                    >
                        <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                        Kembali ke Testimoni
                    </Link>

                    <!-- Badge -->
                    <div class="hero-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 mb-4">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-pink-500"></span>
                        </span>
                        <span class="text-[10px] font-semibold tracking-wide">Tulis Ulasan</span>
                        <PenLine class="w-3 h-3 text-pink-400" />
                    </div>

                    <!-- Title -->
                    <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-2">
                        Bagikan
                    </h1>
                    <h1 class="hero-item text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black bg-gradient-to-r from-pink-300 via-fuchsia-200 to-purple-300 bg-clip-text text-transparent mb-4">
                        Pengalaman Anda ✍️
                    </h1>

                    <!-- Description -->
                    <p class="hero-item text-xs sm:text-sm text-white/60 max-w-xl leading-relaxed">
                        Ceritakan pengalaman wisata Anda di Taman Nasional Lore Lindu untuk membantu pengunjung lainnya
                    </p>
                </div>
            </div>
        </section>

        <!-- Form Section -->
        <section class="py-10 sm:py-14 lg:py-16 bg-gradient-to-b from-gray-50 to-white relative">
            <!-- Background Decorations -->
            <div class="absolute top-0 left-0 w-64 h-64 bg-pink-100/40 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-fuchsia-100/30 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <!-- No Booking Warning -->
                <div 
                    v-if="!hasBooking" 
                    ref="formRef"
                    class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 p-6 sm:p-8 lg:p-10 text-center opacity-0"
                >
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-5 sm:mb-6 bg-gradient-to-br from-amber-100 to-orange-200 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-200/50">
                        <AlertTriangle class="w-8 h-8 sm:w-10 sm:h-10 text-amber-600" />
                    </div>
                    <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2 sm:mb-3">Belum Ada Riwayat Kunjungan</h2>
                    <p class="text-xs sm:text-sm text-gray-600 mb-6 sm:mb-8 max-w-md mx-auto leading-relaxed">
                        Anda perlu memiliki minimal satu pemesanan tiket yang sudah selesai untuk dapat memberikan ulasan.
                    </p>
                    <Link 
                        href="/destinations" 
                        class="group inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 hover:-translate-y-1 transition-all duration-300"
                    >
                        <MapPin class="w-4 h-4 group-hover:scale-110 transition-transform" />
                        Booking Tiket Sekarang
                        <Sparkles class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                    </Link>
                </div>

                <!-- Feedback Form -->
                <div 
                    v-else 
                    ref="formRef"
                    class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 p-5 sm:p-6 lg:p-8 opacity-0"
                >
                    <form @submit.prevent="submit" class="space-y-5 sm:space-y-6">
                        <!-- Destination Select -->
                        <div>
                            <label class="flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-700 mb-2 sm:mb-3">
                                <MapPin class="w-4 h-4 text-pink-500" />
                                Destinasi yang Dikunjungi
                                <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <select 
                                v-model="form.destination_id"
                                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-pink-400 focus:ring-2 focus:ring-pink-500/20 transition-all text-xs sm:text-sm"
                            >
                                <option value="">Pilih destinasi...</option>
                                <option v-for="destination in destinations" :key="destination.id" :value="destination.id">
                                    {{ destination.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Rating -->
                        <div>
                            <label class="flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-700 mb-2 sm:mb-3">
                                <Star class="w-4 h-4 text-pink-500" />
                                Rating Pengalaman Anda
                                <span class="text-pink-500">*</span>
                            </label>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2 sm:mb-3">
                                <button 
                                    v-for="i in 5" 
                                    :key="i" 
                                    type="button" 
                                    @click="setRating(i)"
                                    @mouseenter="hoveredStar = i"
                                    @mouseleave="hoveredStar = 0"
                                    :class="[
                                        `star-btn star-btn-${i} w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center transition-all duration-300`,
                                        rating >= i || hoveredStar >= i
                                            ? 'bg-gradient-to-br from-yellow-400 to-amber-500 text-white shadow-lg shadow-yellow-400/30' 
                                            : 'bg-gray-100 text-gray-400 hover:bg-yellow-100 hover:text-yellow-500'
                                    ]"
                                >
                                    <Star class="w-5 h-5 sm:w-6 sm:h-6" :fill="rating >= i || hoveredStar >= i ? 'currentColor' : 'none'" />
                                </button>
                                <span class="text-xs sm:text-sm font-medium text-gray-600 ml-2">{{ getRatingText() }}</span>
                            </div>
                            <p v-if="form.errors.rating" class="text-red-500 text-[10px] sm:text-xs mt-1">{{ form.errors.rating }}</p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="flex items-center gap-2 text-xs sm:text-sm font-semibold text-gray-700 mb-2 sm:mb-3">
                                <Quote class="w-4 h-4 text-pink-500" />
                                Ceritakan Pengalaman Anda
                                <span class="text-pink-500">*</span>
                            </label>
                            <textarea 
                                v-model="form.message" 
                                rows="5" 
                                required
                                placeholder="Bagikan pengalaman wisata Anda di TNLL... Apa yang paling berkesan? Tips untuk pengunjung lain?"
                                :class="[
                                    'w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border bg-gray-50 focus:bg-white transition-all resize-none text-xs sm:text-sm',
                                    form.errors.message 
                                        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20' 
                                        : 'border-gray-200 focus:border-pink-400 focus:ring-2 focus:ring-pink-500/20'
                                ]"
                            ></textarea>
                            <div class="flex items-center justify-between mt-1.5 sm:mt-2">
                                <p class="text-[10px] sm:text-xs text-gray-500">Minimal 20 karakter, maksimal 1000 karakter</p>
                                <p :class="['text-[10px] sm:text-xs font-medium', messageLength < 20 || messageLength > 1000 ? 'text-red-500' : 'text-green-500']">
                                    {{ messageLength }}/1000
                                </p>
                            </div>
                            <p v-if="form.errors.message" class="text-red-500 text-[10px] sm:text-xs mt-1">{{ form.errors.message }}</p>
                        </div>

                        <!-- Anonymous Toggle -->
                        <div class="flex items-center gap-3 p-3 sm:p-4 rounded-xl bg-gray-50 border border-gray-100">
                            <button 
                                type="button"
                                @click="form.is_anonymous = !form.is_anonymous"
                                :class="[
                                    'relative w-10 sm:w-12 h-5 sm:h-6 rounded-full transition-colors duration-300',
                                    form.is_anonymous ? 'bg-gradient-to-r from-pink-500 to-fuchsia-600' : 'bg-gray-300'
                                ]"
                            >
                                <span 
                                    :class="[
                                        'absolute top-0.5 left-0.5 w-4 h-4 sm:w-5 sm:h-5 bg-white rounded-full shadow transition-transform duration-300',
                                        form.is_anonymous ? 'translate-x-5 sm:translate-x-6' : 'translate-x-0'
                                    ]"
                                ></span>
                            </button>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <component :is="form.is_anonymous ? EyeOff : Eye" class="w-4 h-4 text-gray-500" />
                                    <span class="text-xs sm:text-sm font-medium text-gray-700">
                                        {{ form.is_anonymous ? 'Tampil sebagai Anonim' : 'Tampil dengan nama Anda' }}
                                    </span>
                                </div>
                                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">
                                    {{ form.is_anonymous ? 'Nama Anda tidak akan ditampilkan' : 'Nama Anda akan terlihat di ulasan' }}
                                </p>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="flex gap-3 p-3 sm:p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                            <Info class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500 shrink-0 mt-0.5" />
                            <p class="text-[10px] sm:text-xs text-blue-700 leading-relaxed">
                                Ulasan Anda akan diverifikasi oleh admin sebelum ditampilkan ke publik. Proses moderasi biasanya memakan waktu 1-2 hari kerja.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            :disabled="form.processing || !isMessageValid"
                            :class="[
                                'w-full flex items-center justify-center gap-2 px-5 sm:px-6 py-3 sm:py-4 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg transition-all duration-300',
                                form.processing || !isMessageValid
                                    ? 'bg-gray-300 cursor-not-allowed'
                                    : 'bg-gradient-to-r from-pink-500 to-fuchsia-600 shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 hover:-translate-y-1'
                            ]"
                        >
                            <Loader2 v-if="form.processing" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" />
                            <Send v-else class="w-4 h-4 sm:w-5 sm:h-5" />
                            <span>{{ form.processing ? 'Mengirim...' : 'Kirim Ulasan' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</template>
