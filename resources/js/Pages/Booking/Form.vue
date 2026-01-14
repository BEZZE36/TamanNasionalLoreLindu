<script setup>
/**
 * BookingForm.vue - Premium Booking Form Page
 * Modern design with GSAP animations, glassmorphism, and responsive layout
 */
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
    Calendar, User, Phone, Mail, Ticket, Minus, Plus, 
    Tag, ChevronRight, ArrowRight, AlertCircle, CheckCircle,
    MapPin, Clock, Users, CreditCard, Shield, Car
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    destination: Object,
    coupons: Array,
});

const { props: pageProps } = usePage();
const user = computed(() => pageProps.auth?.user);

// Refs for animations
const heroRef = ref(null);
const formRef = ref(null);
let ctx;

// Form state
const form = useForm({
    visit_date: new Date().toISOString().split('T')[0],
    leader_name: user.value?.name || '',
    leader_phone: user.value?.phone || '',
    leader_email: user.value?.email || '',
    quantities: {},
    coupon_code: '',
});

// Initialize quantities for each price
const quantities = ref({});
props.destination?.prices?.forEach(price => {
    quantities.value[price.id] = 0;
});

// Coupon state
const couponCode = ref('');
const couponMessage = ref('');
const isCouponValid = ref(false);
const isCouponApplied = ref(false);
const couponAmount = ref(0);
const couponLabel = ref('');
const loadingCoupon = ref(false);

// Price helpers
const getPriceLabel = (id) => {
    const price = props.destination?.prices?.find(p => p.id == id);
    return price?.label || '';
};

const getPriceValue = (id) => {
    const price = props.destination?.prices?.find(p => p.id == id);
    return price?.price || 0;
};

const formatRupiah = (number) => {
    return 'Rp ' + number.toLocaleString('id-ID');
};

// Calculations
const calculateSubtotal = () => {
    let total = 0;
    for (const id in quantities.value) {
        total += quantities.value[id] * getPriceValue(id);
    }
    return total;
};

const serviceFee = 5000;

const calculateTotal = computed(() => {
    const subtotal = calculateSubtotal();
    const grossTotal = subtotal + serviceFee;
    return Math.max(0, grossTotal - couponAmount.value);
});

const totalItems = computed(() => {
    let total = 0;
    for (const id in quantities.value) {
        total += quantities.value[id];
    }
    return total;
});

// Separate ticket prices from parking prices
const ticketPrices = computed(() => {
    return props.destination?.prices?.filter(p => 
        ['adult', 'child', 'senior', 'dewasa', 'anak', 'lansia'].includes(p.category)
    ) || [];
});

const parkingPrices = computed(() => {
    return props.destination?.prices?.filter(p => 
        ['motor', 'car', 'bus', 'kendaraan_motor', 'kendaraan_mobil', 'kendaraan_bus'].includes(p.category)
    ) || [];
});

// Quantity controls with animation
const increaseQty = (id) => {
    quantities.value[id]++;
    // Animate the number
    gsap.fromTo(`#qty-${id}`, 
        { scale: 1.3, color: '#10b981' }, 
        { scale: 1, color: '#1f2937', duration: 0.3, ease: 'back.out(2)' }
    );
};

const decreaseQty = (id) => {
    if (quantities.value[id] > 0) {
        quantities.value[id]--;
        gsap.fromTo(`#qty-${id}`, 
            { scale: 0.7, color: '#ef4444' }, 
            { scale: 1, color: '#1f2937', duration: 0.3, ease: 'back.out(2)' }
        );
    }
};

// Coupon functions
const validateCoupon = async () => {
    if (!couponCode.value) {
        couponMessage.value = 'Masukkan kode kupon terlebih dahulu.';
        isCouponValid.value = false;
        return;
    }

    loadingCoupon.value = true;
    couponMessage.value = 'Mengecek kupon...';

    try {
        const response = await fetch('/api/coupon/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                code: couponCode.value,
                amount: calculateSubtotal() + serviceFee,
                destination_id: props.destination?.id
            })
        });

        const data = await response.json();

        if (data.valid) {
            isCouponValid.value = true;
            isCouponApplied.value = true;
            couponCode.value = data.coupon.code;
            couponAmount.value = data.discount;
            couponLabel.value = data.coupon.discount_label;
            couponMessage.value = data.message;
            // Success animation
            gsap.fromTo('.coupon-success', 
                { scale: 0.8, opacity: 0 }, 
                { scale: 1, opacity: 1, duration: 0.4, ease: 'back.out(2)' }
            );
        } else {
            isCouponValid.value = false;
            couponMessage.value = data.message;
            isCouponApplied.value = false;
            couponAmount.value = 0;
        }
    } catch (error) {
        console.error(error);
        couponMessage.value = 'Terjadi kesalahan saat mengecek kupon.';
        isCouponApplied.value = false;
        couponAmount.value = 0;
    } finally {
        loadingCoupon.value = false;
    }
};

const applyCoupon = () => {
    if (isCouponApplied.value) {
        resetCoupon();
    } else {
        validateCoupon();
    }
};

const resetCoupon = () => {
    isCouponApplied.value = false;
    isCouponValid.value = false;
    couponAmount.value = 0;
    couponCode.value = '';
    couponMessage.value = '';
};

const useCoupon = (code) => {
    couponCode.value = code;
    validateCoupon();
};

// Submit form
const submit = () => {
    form.quantities = quantities.value;
    form.coupon_code = isCouponApplied.value ? couponCode.value : '';
    form.post(`/book/${props.destination?.slug}`);
};

// Watch quantities for coupon recalculation
watch(quantities, () => {
    if (isCouponApplied.value) {
        validateCoupon();
    }
}, { deep: true });

// GSAP Animations
onMounted(() => {
    ctx = gsap.context(() => {
        // Hero animations
        const tl = gsap.timeline({ delay: 0.2 });
        tl.fromTo('.hero-breadcrumb', 
            { opacity: 0, y: -15 }, 
            { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }
        )
        .fromTo('.hero-badge', 
            { opacity: 0, scale: 0.8 }, 
            { opacity: 1, scale: 1, duration: 0.4, ease: 'back.out(2)' }, 
            '-=0.2'
        )
        .fromTo('.hero-title', 
            { opacity: 0, y: 25, filter: 'blur(8px)' }, 
            { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.5, ease: 'power3.out' }, 
            '-=0.2'
        )
        .fromTo('.hero-meta > *', 
            { opacity: 0, x: -10 }, 
            { opacity: 1, x: 0, duration: 0.3, stagger: 0.06 }, 
            '-=0.2'
        )
        .fromTo('.hero-step', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }, 
            '-=0.1'
        );

        // Ken Burns effect on background
        gsap.to('.hero-bg-image', {
            scale: 1.1,
            duration: 15,
            ease: 'none',
            repeat: -1,
            yoyo: true
        });

        // Floating shapes animation
        gsap.to('.float-shape', {
            y: -15,
            duration: 3,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: { each: 0.5, from: 'random' }
        });

        // Form cards entrance
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power2.out', delay: 0.6 }
        );

        // Sidebar entrance
        gsap.fromTo('.sidebar-card', 
            { opacity: 0, x: 30 }, 
            { opacity: 1, x: 0, duration: 0.5, ease: 'power2.out', delay: 0.8 }
        );
    }, heroRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <Head :title="`Pesan Tiket - ${destination?.name}`" />

    <div ref="heroRef" class="min-h-screen bg-gray-50">
        <!-- Premium Hero Section -->
        <section class="relative min-h-[45vh] sm:min-h-[50vh] overflow-hidden">
            <!-- Background Image with Ken Burns -->
            <div class="absolute inset-0">
                <img 
                    :src="destination?.primary_image_url || '/images/placeholder-no-image.svg'" 
                    :alt="destination?.name"
                    class="hero-bg-image w-full h-full object-cover"
                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                >
                <!-- Premium Gradient Overlays -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/70 to-slate-900/40"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-900/60 to-transparent"></div>
            </div>

            <!-- Floating Decorative Elements -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="float-shape absolute top-[15%] right-[10%] w-16 h-16 sm:w-20 sm:h-20 border border-white/[0.06] rounded-2xl rotate-12"></div>
                <div class="float-shape absolute top-[35%] left-[5%] w-10 h-10 sm:w-14 sm:h-14 border border-emerald-500/10 rounded-full"></div>
                <div class="float-shape absolute bottom-[30%] right-[15%] w-8 h-8 sm:w-12 sm:h-12 bg-gradient-to-br from-cyan-500/5 to-teal-500/5 rounded-lg rotate-45"></div>
            </div>

            <!-- Wave Transition -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-20 sm:h-28" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
                <path d="M0,50L60,45C120,40,240,30,360,35C480,40,600,60,720,65C840,70,960,60,1080,50C1200,40,1320,30,1380,25L1440,20L1440,100L0,100Z" fill="#f9fafb"/>
            </svg>

            <!-- Hero Content -->
            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 sm:pt-28 pb-20 sm:pb-24">
                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb flex items-center gap-1.5 text-[10px] sm:text-[11px] text-white/60 mb-4 sm:mb-5 font-medium flex-wrap">
                    <Link href="/" class="hover:text-white transition-colors">Beranda</Link>
                    <ChevronRight class="w-3 h-3" />
                    <Link href="/destinations" class="hover:text-white transition-colors">Destinasi</Link>
                    <ChevronRight class="w-3 h-3" />
                    <Link :href="`/destinations/${destination?.slug}`" class="hover:text-white transition-colors truncate max-w-[100px] sm:max-w-[150px]">{{ destination?.name }}</Link>
                    <ChevronRight class="w-3 h-3" />
                    <span class="text-white/90">Pesan Tiket</span>
                </nav>

                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 lg:gap-6">
                    <div class="flex-1">
                        <!-- Badge -->
                        <div class="hero-badge inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white text-[10px] sm:text-[11px] font-semibold mb-3 sm:mb-4">
                            <Ticket class="w-3.5 h-3.5 text-emerald-400" />
                            <span>Pemesanan Tiket</span>
                        </div>

                        <!-- Title -->
                        <h1 class="hero-title text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white leading-tight mb-3 sm:mb-4">
                            Pesan Tiket
                            <span class="block text-emerald-400">{{ destination?.name }}</span>
                        </h1>

                        <!-- Meta Info -->
                        <div class="hero-meta flex flex-wrap items-center gap-2 text-white/70 text-[10px] sm:text-[11px]">
                            <span class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm">
                                <MapPin class="w-3.5 h-3.5 text-emerald-400" />
                                {{ destination?.city }}
                            </span>
                            <span v-if="destination?.operating_hours" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 backdrop-blur-sm">
                                <Clock class="w-3.5 h-3.5 text-cyan-400" />
                                {{ destination?.operating_hours }}
                            </span>
                        </div>
                    </div>

                    <!-- Progress Steps -->
                    <div class="hero-step flex items-center gap-2 sm:gap-3 bg-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-white/15">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] sm:text-xs font-bold shadow-lg shadow-emerald-500/30">1</div>
                            <span class="text-white text-[10px] sm:text-xs font-medium hidden sm:inline">Isi Data</span>
                        </div>
                        <div class="w-6 sm:w-8 h-0.5 bg-white/20"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 flex items-center justify-center text-white/60 text-[10px] sm:text-xs font-bold">2</div>
                            <span class="text-white/60 text-[10px] sm:text-xs font-medium hidden sm:inline">Bayar</span>
                        </div>
                        <div class="w-6 sm:w-8 h-0.5 bg-white/20"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/20 flex items-center justify-center text-white/60 text-[10px] sm:text-xs font-bold">3</div>
                            <span class="text-white/60 text-[10px] sm:text-xs font-medium hidden sm:inline">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Form Section -->
        <section ref="formRef" class="py-8 sm:py-12 lg:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Error Alert -->
                <div v-if="form.errors && Object.keys(form.errors).length > 0" 
                     class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl animate-shake">
                    <div class="flex items-center gap-2 text-red-700 font-medium mb-2">
                        <AlertCircle class="w-4 h-4 sm:w-5 sm:h-5" />
                        <span class="text-sm">Terjadi kesalahan:</span>
                    </div>
                    <ul class="text-xs sm:text-sm text-red-600 list-disc list-inside">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                        <!-- Visit Date Card -->
                        <div class="form-card group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 p-5 sm:p-6 transition-all duration-300">
                            <h2 class="text-sm sm:text-base font-bold text-gray-900 mb-4 flex items-center gap-2.5">
                                <span class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                                    <Calendar class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </span>
                                Tanggal Kunjungan
                            </h2>
                            <input type="date" v-model="form.visit_date" 
                                :min="new Date().toISOString().split('T')[0]"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-sm sm:text-base bg-gray-50 hover:bg-white"
                                :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.visit_date }" required>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" />
                                Pilih tanggal kunjungan mulai dari hari ini
                            </p>
                        </div>

                        <!-- Leader Information Card -->
                        <div class="form-card group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 p-5 sm:p-6 transition-all duration-300">
                            <h2 class="text-sm sm:text-base font-bold text-gray-900 mb-4 flex items-center gap-2.5">
                                <span class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform duration-300">
                                    <User class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </span>
                                Informasi Ketua Rombongan
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <User class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input type="text" v-model="form.leader_name" 
                                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-sm bg-gray-50 hover:bg-white"
                                            placeholder="Nama sesuai KTP" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                                        Nomor HP/WhatsApp <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <Phone class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input type="tel" v-model="form.leader_phone" 
                                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-sm bg-gray-50 hover:bg-white"
                                            placeholder="08xxxxxxxxxx" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <Mail class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input type="email" v-model="form.leader_email" 
                                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-sm bg-gray-50 hover:bg-white"
                                            placeholder="email@example.com" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tickets Selection Card -->
                        <div v-if="ticketPrices.length > 0" class="form-card group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 p-5 sm:p-6 transition-all duration-300">
                            <h2 class="text-sm sm:text-base font-bold text-gray-900 mb-4 flex items-center gap-2.5">
                                <span class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                                    <Ticket class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </span>
                                Tiket Pengunjung
                            </h2>
                            <p class="text-[10px] sm:text-xs text-gray-500 mb-4 -mt-2">Pilih jumlah tiket berdasarkan kategori pengunjung</p>
                            <div class="space-y-3">
                                <div v-for="price in ticketPrices" :key="price.id"
                                    class="group/item flex items-center justify-between p-3 sm:p-4 rounded-xl border border-gray-100 hover:border-amber-200 hover:bg-amber-50/30 transition-all duration-300">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 text-sm sm:text-base">{{ price.label }}</p>
                                        <p v-if="price.description" class="text-[10px] sm:text-xs text-gray-500 truncate">{{ price.description }}</p>
                                        <p class="text-amber-600 font-bold text-sm sm:text-base mt-0.5">{{ price.formatted_price }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 sm:gap-3 ml-4">
                                        <button type="button" @click="decreaseQty(price.id)"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-red-400 hover:bg-red-50 hover:text-red-500 transition-all duration-200 active:scale-95">
                                            <Minus class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>
                                        <span :id="`qty-${price.id}`" class="w-8 sm:w-10 text-center font-black text-base sm:text-lg text-gray-900">
                                            {{ quantities[price.id] || 0 }}
                                        </span>
                                        <button type="button" @click="increaseQty(price.id)"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-amber-400 hover:bg-amber-50 hover:text-amber-500 transition-all duration-200 active:scale-95">
                                            <Plus class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parking Selection Card -->
                        <div v-if="parkingPrices.length > 0" class="form-card group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 p-5 sm:p-6 transition-all duration-300">
                            <h2 class="text-sm sm:text-base font-bold text-gray-900 mb-4 flex items-center gap-2.5">
                                <span class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-cyan-500/20 group-hover:scale-110 transition-transform duration-300">
                                    <Car class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                                </span>
                                Biaya Parkir
                            </h2>
                            <p class="text-[10px] sm:text-xs text-gray-500 mb-4 -mt-2">Pilih jenis kendaraan yang akan dibawa</p>
                            <div class="space-y-3">
                                <div v-for="price in parkingPrices" :key="price.id"
                                    class="group/item flex items-center justify-between p-3 sm:p-4 rounded-xl border border-gray-100 hover:border-cyan-200 hover:bg-cyan-50/30 transition-all duration-300">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 text-sm sm:text-base">{{ price.label }}</p>
                                        <p v-if="price.description" class="text-[10px] sm:text-xs text-gray-500 truncate">{{ price.description }}</p>
                                        <p class="text-cyan-600 font-bold text-sm sm:text-base mt-0.5">{{ price.formatted_price }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 sm:gap-3 ml-4">
                                        <button type="button" @click="decreaseQty(price.id)"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-red-400 hover:bg-red-50 hover:text-red-500 transition-all duration-200 active:scale-95">
                                            <Minus class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>
                                        <span :id="`qty-${price.id}`" class="w-8 sm:w-10 text-center font-black text-base sm:text-lg text-gray-900">
                                            {{ quantities[price.id] || 0 }}
                                        </span>
                                        <button type="button" @click="increaseQty(price.id)"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-gray-200 flex items-center justify-center hover:border-cyan-400 hover:bg-cyan-50 hover:text-cyan-500 transition-all duration-200 active:scale-95">
                                            <Plus class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Summary -->
                    <div class="lg:col-span-1">
                        <div class="sidebar-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5 sm:p-6 sticky top-24">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <CreditCard class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" />
                                Ringkasan Pesanan
                            </h3>

                            <!-- Destination Info -->
                            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                                <img :src="destination?.primary_image_url || '/images/placeholder-no-image.svg'" :alt="destination?.name"
                                    class="w-16 h-12 sm:w-20 sm:h-14 rounded-xl object-cover shadow-md">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 text-xs sm:text-sm truncate">{{ destination?.name }}</p>
                                    <p class="text-[10px] sm:text-xs text-gray-500 flex items-center gap-1">
                                        <MapPin class="w-3 h-3" />
                                        {{ destination?.city }}
                                    </p>
                                </div>
                            </div>

                            <!-- Price Breakdown -->
                            <div class="py-4 space-y-2 border-b border-gray-100">
                                <template v-for="(qty, priceId) in quantities" :key="priceId">
                                    <div v-if="qty > 0" class="flex justify-between text-xs sm:text-sm">
                                        <span class="text-gray-600">{{ getPriceLabel(priceId) }} x {{ qty }}</span>
                                        <span class="font-medium text-gray-900">{{ formatRupiah(qty * getPriceValue(priceId)) }}</span>
                                    </div>
                                </template>
                                <div v-if="totalItems === 0" class="text-xs sm:text-sm text-gray-400 text-center py-2">
                                    <Users class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                                    Pilih setidaknya 1 tiket
                                </div>
                            </div>

                            <!-- Service Fee -->
                            <div class="py-3 border-b border-gray-100">
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600 flex items-center gap-1">
                                        <Shield class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                                        Biaya Layanan
                                    </span>
                                    <span class="font-medium text-gray-900">Rp 5.000</span>
                                </div>
                            </div>

                            <!-- Coupon Input -->
                            <div class="py-4 border-b border-gray-100 space-y-3">
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700">Kode Promo / Kupon</label>
                                <div class="flex gap-2">
                                    <input type="text" v-model="couponCode"
                                        class="flex-1 px-3 py-2 rounded-lg border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-xs sm:text-sm uppercase bg-gray-50"
                                        placeholder="KODE" :disabled="isCouponApplied">
                                    <button type="button" @click="applyCoupon" :disabled="loadingCoupon"
                                        :class="isCouponApplied ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100' : 'bg-gray-900 text-white hover:bg-gray-800'"
                                        class="px-3 sm:px-4 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all disabled:opacity-50">
                                        {{ loadingCoupon ? '...' : (isCouponApplied ? 'Hapus' : 'Pakai') }}
                                    </button>
                                </div>
                                <p v-if="couponMessage" 
                                   :class="isCouponValid ? 'text-emerald-600' : 'text-red-500'" 
                                   class="text-[10px] sm:text-xs font-medium coupon-success">
                                    {{ couponMessage }}
                                </p>

                                <!-- Available Coupons -->
                                <div v-if="coupons?.length > 0 && !isCouponApplied" class="mt-3 pt-3 border-t border-gray-100"> 
                                    <h4 class="text-[10px] sm:text-xs font-semibold text-gray-900 mb-2 flex items-center gap-1.5">
                                        <Tag class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-pink-500" />
                                        Voucher Tersedia
                                    </h4>
                                    <div class="space-y-2 max-h-32 overflow-y-auto">
                                        <div v-for="coupon in coupons" :key="coupon.id"
                                            @click="useCoupon(coupon.code)"
                                            class="p-2.5 border border-dashed border-gray-300 rounded-lg bg-gray-50/50 flex justify-between items-center hover:border-pink-300 hover:bg-pink-50/30 transition-all cursor-pointer group">
                                            <div>
                                                <p class="font-bold text-gray-900 text-[10px] sm:text-xs font-mono group-hover:text-pink-600 transition-colors">{{ coupon.code }}</p>
                                                <p class="text-[9px] sm:text-[10px] text-gray-500">{{ coupon.name }}</p>
                                            </div>
                                            <span class="text-[9px] sm:text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded">
                                                {{ coupon.discount_label }} OFF
                                            </span>
                                        </div>
                                    </div>
                                 </div> 
                            </div>

                            <!-- Total -->
                            <div class="py-4 space-y-2">
                                <div v-if="isCouponApplied" class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="font-medium text-gray-900">{{ formatRupiah(calculateSubtotal() + serviceFee) }}</span>
                                </div>
                                <div v-if="isCouponApplied" class="flex justify-between text-xs sm:text-sm text-emerald-600">
                                    <span class="font-medium flex items-center gap-1">
                                        <Tag class="w-3.5 h-3.5" />
                                        Diskon {{ couponLabel }}
                                    </span>
                                    <span class="font-bold">-{{ formatRupiah(couponAmount) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-gray-900 font-bold text-sm sm:text-base">Total Bayar</span>
                                    <span class="text-xl sm:text-2xl font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                                        {{ formatRupiah(calculateTotal) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" :disabled="totalItems < 1 || form.processing"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 sm:py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-sm sm:text-base rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-lg group">
                                <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <template v-else>
                                    <ArrowRight class="w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-1 transition-transform" />
                                    Lanjut ke Pembayaran
                                </template>
                            </button>

                            <p class="text-center text-[9px] sm:text-[10px] text-gray-400 mt-4 leading-relaxed">
                                Dengan melanjutkan, Anda menyetujui 
                                <Link href="/terms" class="text-emerald-600 hover:underline">Syarat & Ketentuan</Link>
                                yang berlaku.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
    20%, 40%, 60%, 80% { transform: translateX(4px); }
}
.animate-shake {
    animation: shake 0.5s ease-in-out;
}
</style>
