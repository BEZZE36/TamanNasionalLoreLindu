<script setup>
/**
 * Booking/Edit.vue - Edit Pending Booking Page
 * Allows users to modify pending bookings (quantities, date, coupon, contact info)
 */
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { gsap } from 'gsap';
import { 
    ArrowLeft, Calendar, Users, MapPin, Ticket, Edit, Plus, Minus,
    Gift, Check, X, Loader2, AlertCircle, CreditCard, Save
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    booking: Object,
    destination: Object,
    coupons: { type: Array, default: () => [] }
});

const page = usePage();
let ctx;

// Initialize quantities from existing booking items
const quantities = ref({});
props.destination?.prices?.forEach(price => {
    const item = props.booking?.items?.find(i => i.destination_price_id === price.id);
    quantities.value[price.id] = item?.quantity || 0;
});

// Form data
const form = useForm({
    visit_date: props.booking?.visit_date?.split('T')[0] || '',
    leader_name: props.booking?.leader_name || '',
    leader_phone: props.booking?.leader_phone || '',
    leader_email: props.booking?.leader_email || '',
    quantities: quantities.value,
    coupon_code: props.booking?.discount_code || ''
});

// Coupon state
const couponCode = ref(props.booking?.discount_code || '');
const couponMessage = ref('');
const isCouponValid = ref(!!props.booking?.discount_code);
const isCouponApplied = ref(!!props.booking?.discount_code);
const couponAmount = ref(props.booking?.discount || 0);
const loadingCoupon = ref(false);

// Price helpers
const getPriceLabel = (id) => props.destination?.prices?.find(p => p.id === id)?.label || '-';
const getPriceValue = (id) => props.destination?.prices?.find(p => p.id === id)?.price || 0;
const formatRupiah = (n) => 'Rp ' + (n || 0).toLocaleString('id-ID');

// Calculations
const calculateSubtotal = () => {
    let subtotal = 0;
    for (const id in quantities.value) {
        subtotal += (getPriceValue(parseInt(id)) * (quantities.value[id] || 0));
    }
    return subtotal;
};

const serviceFee = 5000;

const calculateTotal = computed(() => {
    const subtotal = calculateSubtotal();
    const grossTotal = subtotal + serviceFee;
    return Math.max(0, grossTotal - couponAmount.value);
});

const totalItems = computed(() => Object.values(quantities.value).reduce((a, b) => a + b, 0));

// Filter prices by category
const ticketPrices = computed(() => props.destination?.prices?.filter(p => 
    ['dewasa', 'adult', 'anak', 'child', 'lansia', 'senior'].includes(p.category)
) || []);

const parkingPrices = computed(() => props.destination?.prices?.filter(p => 
    ['motor', 'car', 'bus', 'kendaraan_motor', 'kendaraan_mobil', 'kendaraan_bus'].includes(p.category)
) || []);

// Quantity controls
const increaseQty = (id) => { quantities.value[id] = (quantities.value[id] || 0) + 1; };
const decreaseQty = (id) => { if (quantities.value[id] > 0) quantities.value[id]--; };

// Coupon functions
const validateCoupon = async () => {
    if (!couponCode.value.trim()) {
        isCouponValid.value = false;
        isCouponApplied.value = false;
        couponAmount.value = 0;
        couponMessage.value = '';
        return;
    }

    loadingCoupon.value = true;
    try {
        const response = await fetch('/api/coupon/validate', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
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
            couponMessage.value = data.message;
            couponAmount.value = data.discount;
        } else {
            isCouponValid.value = false;
            couponAmount.value = 0;
            couponMessage.value = data.message || 'Kupon tidak valid';
        }
    } catch (e) {
        couponMessage.value = 'Gagal memvalidasi kupon';
        isCouponValid.value = false;
    }
    loadingCoupon.value = false;
};

const applyCoupon = () => {
    if (isCouponValid.value) {
        isCouponApplied.value = true;
        form.coupon_code = couponCode.value;
    }
};

const resetCoupon = () => {
    couponCode.value = '';
    isCouponValid.value = false;
    isCouponApplied.value = false;
    couponAmount.value = 0;
    couponMessage.value = '';
    form.coupon_code = '';
};

// Submit form
const submit = () => {
    // Sync quantities to form before submission
    form.quantities = { ...quantities.value };
    
    form.put(`/booking/${props.booking.order_number}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success message
            console.log('Booking updated successfully!');
        },
        onError: (errors) => {
            console.error('Booking update failed:', errors);
        }
    });
};

// Watch quantities for coupon recalculation
watch(quantities, () => {
    if (isCouponApplied.value) validateCoupon();
}, { deep: true });

// GSAP Animations
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power2.out', delay: 0.2 }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <Head :title="`Edit Booking - ${booking?.order_number}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="relative bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 py-8 sm:py-12">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6">
                <Link :href="`/booking/${booking.order_number}/success`" 
                      class="inline-flex items-center gap-2 text-white/80 hover:text-white text-xs sm:text-sm mb-4 transition-colors">
                    <ArrowLeft class="w-4 h-4" />
                    Kembali ke Detail Tiket
                </Link>
                <h1 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-3">
                    <Edit class="w-6 h-6" />
                    Edit Booking
                </h1>
                <p class="text-white/70 text-xs sm:text-sm mt-1 font-mono">{{ booking.order_number }}</p>
            </div>
        </div>

        <!-- Form Content -->
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column - Form Fields -->
                    <div class="lg:col-span-2 space-y-5">
                        <!-- Destination Info -->
                        <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <img :src="destination?.primary_image_url || '/assets/logo.png'" 
                                     class="w-16 h-16 rounded-xl object-cover">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm sm:text-base">{{ destination?.name }}</h3>
                                    <p class="text-[10px] sm:text-xs text-gray-500 flex items-center gap-1">
                                        <MapPin class="w-3 h-3" />
                                        Lore Lindu, Sulawesi Tengah
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Visit Date & Contact Info -->
                        <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-blue-500" />
                                Informasi Kunjungan
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Tanggal Kunjungan *</label>
                                    <input v-model="form.visit_date" type="date" required
                                           class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nama Ketua *</label>
                                    <input v-model="form.leader_name" type="text" required
                                           class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">No. HP *</label>
                                    <input v-model="form.leader_phone" type="tel" required
                                           class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Email *</label>
                                    <input v-model="form.leader_email" type="email" required
                                           class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Selection -->
                        <div v-if="ticketPrices.length" class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                                <Ticket class="w-4 h-4 text-emerald-500" />
                                Jumlah Tiket
                            </h3>
                            <div class="space-y-3">
                                <div v-for="price in ticketPrices" :key="price.id" 
                                     class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                                    <div>
                                        <p class="font-semibold text-gray-900 text-xs">{{ price.label }}</p>
                                        <p class="text-[10px] text-gray-500">{{ formatRupiah(price.price) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="decreaseQty(price.id)"
                                                class="w-8 h-8 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors">
                                            <Minus class="w-4 h-4 text-gray-600" />
                                        </button>
                                        <span class="w-8 text-center font-bold text-sm">{{ quantities[price.id] || 0 }}</span>
                                        <button type="button" @click="increaseQty(price.id)"
                                                class="w-8 h-8 rounded-lg bg-emerald-500 hover:bg-emerald-600 flex items-center justify-center transition-colors">
                                            <Plus class="w-4 h-4 text-white" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parking Selection -->
                        <div v-if="parkingPrices.length" class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                                🚗 Parkir Kendaraan
                            </h3>
                            <div class="space-y-3">
                                <div v-for="price in parkingPrices" :key="price.id" 
                                     class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100">
                                    <div>
                                        <p class="font-semibold text-gray-900 text-xs">{{ price.label }}</p>
                                        <p class="text-[10px] text-gray-500">{{ formatRupiah(price.price) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="decreaseQty(price.id)"
                                                class="w-8 h-8 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors">
                                            <Minus class="w-4 h-4 text-gray-600" />
                                        </button>
                                        <span class="w-8 text-center font-bold text-sm">{{ quantities[price.id] || 0 }}</span>
                                        <button type="button" @click="increaseQty(price.id)"
                                                class="w-8 h-8 rounded-lg bg-blue-500 hover:bg-blue-600 flex items-center justify-center transition-colors">
                                            <Plus class="w-4 h-4 text-white" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coupon Section -->
                        <div class="form-card bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                                <Gift class="w-4 h-4 text-amber-500" />
                                Kode Kupon
                            </h3>
                            <div v-if="!isCouponApplied" class="space-y-3">
                                <div class="flex gap-2">
                                    <input v-model="couponCode" type="text" placeholder="Masukkan kode kupon"
                                           class="flex-1 px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 uppercase font-mono transition-all">
                                    <button type="button" @click="validateCoupon" :disabled="loadingCoupon || !couponCode"
                                            class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-xl disabled:opacity-50 transition-all">
                                        <Loader2 v-if="loadingCoupon" class="w-4 h-4 animate-spin" />
                                        <span v-else>Cek</span>
                                    </button>
                                </div>
                                <p v-if="couponMessage" :class="['text-xs', isCouponValid ? 'text-emerald-600' : 'text-red-500']">
                                    {{ couponMessage }}
                                </p>
                                <button v-if="isCouponValid && !isCouponApplied" type="button" @click="applyCoupon"
                                        class="w-full py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold text-xs rounded-xl hover:shadow-lg transition-all">
                                    <Check class="w-4 h-4 inline mr-1" />
                                    Terapkan Kupon (Diskon {{ formatRupiah(couponAmount) }})
                                </button>

                                <!-- Available Coupons -->
                                <div v-if="coupons.length" class="pt-3 border-t border-gray-100">
                                    <p class="text-[10px] font-semibold text-gray-500 uppercase mb-2">Kupon Tersedia</p>
                                    <div class="space-y-2">
                                        <button v-for="c in coupons" :key="c.id" type="button"
                                                @click="couponCode = c.code; validateCoupon()"
                                                class="w-full p-2.5 rounded-lg border border-amber-200 bg-amber-50 hover:bg-amber-100 text-left transition-colors">
                                            <span class="font-mono font-bold text-amber-700 text-xs">{{ c.code }}</span>
                                            <span class="text-[10px] text-gray-600 block">{{ c.name }} - {{ c.discount_label }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                                <div>
                                    <p class="font-mono font-bold text-emerald-700 text-xs">{{ couponCode }}</p>
                                    <p class="text-[10px] text-emerald-600">Diskon {{ formatRupiah(couponAmount) }}</p>
                                </div>
                                <button type="button" @click="resetCoupon" class="p-2 rounded-lg hover:bg-emerald-100 transition-colors">
                                    <X class="w-4 h-4 text-emerald-700" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="lg:col-span-1">
                        <div class="form-card sticky top-6 bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                                <CreditCard class="w-4 h-4 text-blue-500" />
                                Ringkasan Booking
                            </h3>
                            
                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-500">Total Item</span>
                                    <span class="font-semibold text-gray-900">{{ totalItems }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-500">Subtotal</span>
                                    <span class="text-gray-700">{{ formatRupiah(calculateSubtotal()) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-500">Biaya Layanan</span>
                                    <span class="text-gray-700">{{ formatRupiah(serviceFee) }}</span>
                                </div>
                                <div v-if="couponAmount > 0" class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-emerald-600">Diskon</span>
                                    <span class="text-emerald-600">-{{ formatRupiah(couponAmount) }}</span>
                                </div>
                                <div class="flex justify-between py-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl px-3 -mx-1">
                                    <span class="font-bold text-gray-900">Total</span>
                                    <span class="font-black text-blue-600">{{ formatRupiah(calculateTotal) }}</span>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <button type="submit" :disabled="form.processing || totalItems < 1"
                                        class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                    <Save v-else class="w-4 h-4" />
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                                <Link :href="`/booking/${booking.order_number}/payment`"
                                      class="block w-full py-2.5 text-center border-2 border-gray-200 text-gray-700 font-semibold text-xs rounded-xl hover:bg-gray-100 transition-colors">
                                    Lanjut ke Pembayaran
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
