<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    ArrowLeft, Save, Ticket, Percent, CircleDollarSign, Calendar, 
    Users, Power, Sparkles, X, CheckCircle, Eye, MapPin, Edit
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    coupon: { type: Object, required: true },
    destinations: { type: Array, default: () => [] }
});

let ctx;

// Properly format dates - FIX for auto-reset bug
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    // Handle ISO string format (e.g., "2026-01-15T00:00:00.000Z")
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';
    // Format as YYYY-MM-DD for input[type=date]
    return date.toISOString().split('T')[0];
};

const form = useForm({
    code: props.coupon.code || '',
    name: props.coupon.name || '',
    description: props.coupon.description || '',
    discount_type: props.coupon.discount_type || 'percentage',
    discount_value: props.coupon.discount_value || '',
    min_order_amount: props.coupon.min_order_amount || '',
    max_discount: props.coupon.max_discount || '',
    usage_limit: props.coupon.usage_limit || '',
    per_user_limit: props.coupon.per_user_limit || '',
    start_date: formatDateForInput(props.coupon.start_date),
    end_date: formatDateForInput(props.coupon.end_date),
    is_active: props.coupon.is_active ?? true,
    applicable_destinations: props.coupon.applicable_destinations || []
});

// Discount preview
const discountPreview = computed(() => {
    if (!form.discount_value) return '-';
    if (form.discount_type === 'percentage') {
        return form.discount_value + '%';
    }
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(form.discount_value);
});

// Toggle destination selection
const toggleDestination = (id) => {
    const destinations = form.applicable_destinations || [];
    const index = destinations.indexOf(id);
    if (index === -1) {
        form.applicable_destinations.push(id);
    } else {
        form.applicable_destinations.splice(index, 1);
    }
};

const submit = () => { 
    form.put(`/admin/coupons/${props.coupon.id}`); 
};

// Animations
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.form-section', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }
        );
        gsap.fromTo('.preview-card', 
            { opacity: 0, scale: 0.95 }, 
            { opacity: 1, scale: 1, duration: 0.6, delay: 0.3, ease: 'back.out(1.7)' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="max-w-5xl mx-auto pb-24">
        <!-- Premium Header -->
        <div class="form-section relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 shadow-2xl mb-5">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-purple-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex items-center gap-4">
                <Link href="/admin/coupons" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-white hover:bg-white/30 transition-all hover:scale-105">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Edit Kupon</h1>
                        <Edit class="w-4 h-4 text-purple-200" />
                    </div>
                    <p class="text-blue-100/80 text-xs font-mono">{{ coupon.code }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Coupon Info Section -->
                    <div class="form-section bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <Ticket class="w-4 h-4 text-emerald-500" />
                                Informasi Kupon
                            </h2>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Kode Kupon *</label>
                                    <input 
                                        v-model="form.code" 
                                        type="text" 
                                        required 
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 font-mono uppercase transition-all"
                                    >
                                    <p v-if="form.errors.code" class="text-red-500 text-[10px] mt-1">{{ form.errors.code }}</p>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nama Kupon *</label>
                                    <input 
                                        v-model="form.name" 
                                        type="text" 
                                        required 
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                    >
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Deskripsi</label>
                                <textarea 
                                    v-model="form.description" 
                                    rows="2" 
                                    class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all resize-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Settings Section -->
                    <div class="form-section bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <Percent class="w-4 h-4 text-blue-500" />
                                Pengaturan Diskon
                            </h2>
                        </div>
                        <div class="p-5 space-y-4">
                            <!-- Discount Type -->
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-2">Tipe Diskon *</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label 
                                        :class="[
                                            'flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all duration-200',
                                            form.discount_type === 'percentage' 
                                                ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 shadow-sm' 
                                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                                        ]"
                                    >
                                        <input type="radio" v-model="form.discount_type" value="percentage" class="hidden">
                                        <div :class="['p-2 rounded-lg', form.discount_type === 'percentage' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500']">
                                            <Percent class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <div :class="['font-semibold text-xs', form.discount_type === 'percentage' ? 'text-blue-900' : 'text-gray-700']">Persentase</div>
                                            <div class="text-[10px] text-gray-500">Potongan %</div>
                                        </div>
                                    </label>
                                    <label 
                                        :class="[
                                            'flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all duration-200',
                                            form.discount_type === 'fixed' 
                                                ? 'border-amber-500 bg-gradient-to-br from-amber-50 to-orange-50 shadow-sm' 
                                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                                        ]"
                                    >
                                        <input type="radio" v-model="form.discount_type" value="fixed" class="hidden">
                                        <div :class="['p-2 rounded-lg', form.discount_type === 'fixed' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500']">
                                            <CircleDollarSign class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <div :class="['font-semibold text-xs', form.discount_type === 'fixed' ? 'text-amber-900' : 'text-gray-700']">Nominal Tetap</div>
                                            <div class="text-[10px] text-gray-500">Potongan Rp</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Discount Values -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        {{ form.discount_type === 'percentage' ? 'Nilai (%)' : 'Nilai (Rp)' }} *
                                    </label>
                                    <input 
                                        v-model="form.discount_value" 
                                        type="number" 
                                        required 
                                        min="0"
                                        :max="form.discount_type === 'percentage' ? 100 : undefined"
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    >
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Min. Order (Rp)</label>
                                    <input 
                                        v-model="form.min_order_amount" 
                                        type="number" 
                                        min="0" 
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    >
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Max Diskon (Rp)</label>
                                    <input 
                                        v-model="form.max_discount" 
                                        type="number" 
                                        min="0" 
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Usage & Period Section -->
                    <div class="form-section bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <Users class="w-4 h-4 text-purple-500" />
                                Batas Penggunaan & Periode
                            </h2>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Batas Total Penggunaan</label>
                                    <input 
                                        v-model="form.usage_limit" 
                                        type="number" 
                                        min="1" 
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all"
                                    >
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Batas Per User</label>
                                    <input 
                                        v-model="form.per_user_limit" 
                                        type="number" 
                                        min="1" 
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all"
                                    >
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        <Calendar class="w-3 h-3 inline mr-1" />Mulai Berlaku
                                    </label>
                                    <input 
                                        v-model="form.start_date" 
                                        type="date"
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all"
                                    >
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        <Calendar class="w-3 h-3 inline mr-1" />Berakhir
                                    </label>
                                    <input 
                                        v-model="form.end_date" 
                                        type="date"
                                        class="w-full px-3 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all"
                                    >
                                </div>
                            </div>

                            <!-- Active Toggle -->
                            <label 
                                :class="[
                                    'flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all',
                                    form.is_active ? 'border-emerald-500 bg-gradient-to-br from-emerald-50 to-teal-50' : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <input type="checkbox" v-model="form.is_active" class="hidden">
                                <div :class="['p-2 rounded-lg transition-colors', form.is_active ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500']">
                                    <Power class="w-4 h-4" />
                                </div>
                                <span :class="['font-medium text-xs', form.is_active ? 'text-emerald-900' : 'text-gray-700']">
                                    {{ form.is_active ? 'Kupon Aktif' : 'Kupon Nonaktif' }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Destinations Section -->
                    <div v-if="destinations.length > 0" class="form-section bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <MapPin class="w-4 h-4 text-amber-500" />
                                Destinasi Berlaku
                            </h2>
                            <p class="text-[10px] text-gray-500 mt-0.5">Kosongkan jika berlaku untuk semua destinasi</p>
                        </div>
                        <div class="p-5">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="dest in destinations"
                                    :key="dest.id"
                                    type="button"
                                    @click="toggleDestination(dest.id)"
                                    :class="[
                                        'px-3 py-1.5 rounded-lg text-xs font-medium transition-all',
                                        (form.applicable_destinations || []).includes(dest.id)
                                            ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-sm'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ dest.name }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Preview Card -->
                <div class="lg:col-span-1">
                    <div class="preview-card sticky top-6 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <Eye class="w-4 h-4 text-gray-500" />
                                Preview Kupon
                            </h3>
                        </div>
                        <div class="p-5">
                            <!-- Coupon Card Preview -->
                            <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 p-4 text-white shadow-lg">
                                <!-- Decorative circles -->
                                <div class="absolute -top-4 -right-4 w-20 h-20 bg-white/10 rounded-full"></div>
                                <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-white/5 rounded-full"></div>
                                
                                <div class="relative">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="px-2 py-0.5 bg-white/20 rounded-full text-[9px] font-bold uppercase tracking-wide">
                                            {{ form.discount_type === 'percentage' ? 'Persen' : 'Fixed' }}
                                        </span>
                                        <span v-if="form.is_active" class="flex items-center gap-1 text-[9px]">
                                            <CheckCircle class="w-3 h-3" />
                                            Aktif
                                        </span>
                                    </div>
                                    
                                    <p class="font-mono text-lg font-bold tracking-wider mb-1">{{ form.code || 'KODE' }}</p>
                                    <p class="text-xs text-white/80 mb-3 truncate">{{ form.name || 'Nama Kupon' }}</p>
                                    
                                    <div class="pt-3 border-t border-white/20">
                                        <p class="text-[10px] text-white/60 uppercase tracking-wide mb-0.5">Diskon</p>
                                        <p class="text-2xl font-black">{{ discountPreview }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Summary -->
                            <div class="mt-4 space-y-2">
                                <div v-if="form.min_order_amount" class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500">Min. Order</span>
                                    <span class="font-semibold text-gray-900">Rp {{ parseInt(form.min_order_amount).toLocaleString('id-ID') }}</span>
                                </div>
                                <div v-if="form.max_discount" class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500">Max. Diskon</span>
                                    <span class="font-semibold text-gray-900">Rp {{ parseInt(form.max_discount).toLocaleString('id-ID') }}</span>
                                </div>
                                <div v-if="form.usage_limit" class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500">Batas Penggunaan</span>
                                    <span class="font-semibold text-gray-900">{{ form.usage_limit }}x</span>
                                </div>
                                <div v-if="form.start_date || form.end_date" class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500">Periode</span>
                                    <span class="font-semibold text-gray-900">{{ form.start_date || '...' }} - {{ form.end_date || '...' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Floating Action Buttons -->
        <div class="fixed bottom-6 right-6 flex items-center gap-3 z-40">
            <Link 
                href="/admin/coupons"
                class="px-5 py-3 rounded-xl bg-white text-gray-700 font-semibold text-xs shadow-lg border border-gray-200 hover:bg-gray-50 transition-all flex items-center gap-2"
            >
                <X class="w-4 h-4" />
                Batal
            </Link>
            <button 
                @click="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-xs shadow-xl shadow-blue-500/30 hover:shadow-2xl hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <Save class="w-4 h-4" />
                <span>{{ form.processing ? 'Menyimpan...' : 'Perbarui Kupon' }}</span>
            </button>
        </div>
    </div>
</template>
