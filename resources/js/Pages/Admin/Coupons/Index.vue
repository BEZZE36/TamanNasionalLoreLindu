<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Ticket, Plus, Edit, Trash2, ToggleLeft, ToggleRight, Percent, 
    CircleDollarSign, Users, Calendar, Loader2, AlertTriangle,
    Search, Filter, Sparkles, TrendingUp, Eye, Clock, CheckCircle
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    coupons: { type: Object, required: true }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Local reactive copy for optimistic updates
const localCoupons = ref(JSON.parse(JSON.stringify(props.coupons.data)));

watch(() => props.coupons, (newVal) => {
    localCoupons.value = JSON.parse(JSON.stringify(newVal.data));
}, { deep: true });

// State
const search = ref('');
const isDeleting = ref(false);
const isToggling = ref(null);
const showDeleteModal = ref(false);
const selectedCoupon = ref(null);
let ctx;

// Computed stats from local data
const stats = computed(() => {
    const data = localCoupons.value || [];
    const now = new Date();
    return {
        total: props.coupons?.total || data.length,
        active: data.filter(c => c.is_active).length,
        expired: data.filter(c => c.end_date && new Date(c.end_date) < now).length,
        this_month: data.length
    };
});

// Filtered coupons
const filteredCoupons = computed(() => {
    if (!search.value) return localCoupons.value;
    const q = search.value.toLowerCase();
    return localCoupons.value.filter(c => 
        c.code.toLowerCase().includes(q) || 
        c.name.toLowerCase().includes(q)
    );
});

// Animations
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.table-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Toggle status with optimistic update
const toggleActive = (coupon) => {
    isToggling.value = coupon.id;
    
    const index = localCoupons.value.findIndex(c => c.id === coupon.id);
    if (index !== -1) {
        localCoupons.value[index].is_active = !localCoupons.value[index].is_active;
    }
    
    router.post(`/admin/coupons/${coupon.id}/toggle`, {}, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => { isToggling.value = null; },
        onError: () => {
            if (index !== -1) {
                localCoupons.value[index].is_active = !localCoupons.value[index].is_active;
            }
        }
    });
};

// Delete modal handlers
const openDeleteModal = (coupon) => {
    selectedCoupon.value = coupon;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showDeleteModal.value = false;
    selectedCoupon.value = null;
};

const confirmDelete = () => {
    if (!selectedCoupon.value) return;
    isDeleting.value = true;
    
    const couponId = selectedCoupon.value.id;
    const index = localCoupons.value.findIndex(c => c.id === couponId);
    let removedCoupon = null;
    
    if (index !== -1) {
        removedCoupon = localCoupons.value.splice(index, 1)[0];
    }
    
    router.delete(`/admin/coupons/${couponId}`, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isDeleting.value = false;
            closeModal();
        },
        onError: () => {
            if (removedCoupon && index !== -1) {
                localCoupons.value.splice(index, 0, removedCoupon);
            }
        }
    });
};

// Formatters
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatDiscount = (coupon) => {
    return coupon.discount_type === 'percentage' 
        ? coupon.discount_value + '%' 
        : 'Rp ' + new Intl.NumberFormat('id-ID').format(coupon.discount_value);
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-cyan-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-emerald-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-cyan-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Icon with Glow -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Ticket class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Manajemen Kupon</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ stats.total }} Total
                            </span>
                        </div>
                        <p class="text-emerald-100/80 text-xs">Kelola kode diskon dan promo destinasi wisata</p>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <Link 
                    href="/admin/coupons/create"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-600 text-xs font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                >
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                    <span>Buat Kupon</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                </Link>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Kupon -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Kupon</p>
                    </div>
                </div>
            </div>
            
            <!-- Active -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <ToggleRight class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.active }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Kupon Aktif</p>
                    </div>
                </div>
            </div>
            
            <!-- Expired -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                        <Clock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.expired }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Kadaluarsa</p>
                    </div>
                </div>
            </div>
            
            <!-- This Month -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.this_month }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-md group">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-emerald-500 transition-colors" />
                    <input 
                        type="text" 
                        v-model="search"
                        placeholder="Cari kode atau nama kupon..."
                        class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all duration-300"
                    />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Kode</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Diskon</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Penggunaan</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Periode</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="coupon in filteredCoupons" 
                            :key="coupon.id" 
                            class="table-row group hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-bold text-gray-900 font-mono text-sm">{{ coupon.code }}</p>
                                    <p class="text-[10px] text-gray-500 truncate max-w-[150px]">{{ coupon.name }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div :class="['p-2 rounded-lg', coupon.discount_type === 'percentage' ? 'bg-gradient-to-br from-blue-100 to-indigo-100' : 'bg-gradient-to-br from-amber-100 to-orange-100']">
                                        <Percent v-if="coupon.discount_type === 'percentage'" class="w-4 h-4 text-blue-600" />
                                        <CircleDollarSign v-else class="w-4 h-4 text-amber-600" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-xs">{{ formatDiscount(coupon) }}</p>
                                        <p v-if="coupon.max_discount" class="text-[10px] text-gray-500">Max: {{ formatCurrency(coupon.max_discount) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-gray-100">
                                    <Users class="w-3.5 h-3.5 text-gray-500" />
                                    <span class="font-bold text-xs text-gray-900">{{ coupon.usages_count || 0 }}</span>
                                    <span v-if="coupon.usage_limit" class="text-[10px] text-gray-400">/ {{ coupon.usage_limit }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-600">
                                    <Calendar class="w-3.5 h-3.5 text-gray-400" />
                                    <span>{{ formatDate(coupon.start_date) }} - {{ formatDate(coupon.end_date) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button 
                                    @click="toggleActive(coupon)"
                                    :disabled="isToggling === coupon.id"
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition-all',
                                        coupon.is_active 
                                            ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-sm' 
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    ]"
                                >
                                    <Loader2 v-if="isToggling === coupon.id" class="w-3 h-3 animate-spin" />
                                    <ToggleRight v-else-if="coupon.is_active" class="w-3 h-3" />
                                    <ToggleLeft v-else class="w-3 h-3" />
                                    {{ coupon.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <Link 
                                        :href="`/admin/coupons/${coupon.id}`"
                                        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-all"
                                        title="Lihat Detail"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link 
                                        :href="`/admin/coupons/${coupon.id}/edit`"
                                        class="p-2 rounded-lg text-blue-500 hover:bg-blue-50 hover:text-blue-600 transition-all"
                                        title="Edit"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button 
                                        @click="openDeleteModal(coupon)"
                                        class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-600 transition-all"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="filteredCoupons.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                                        <Ticket class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada kupon</p>
                                    <p class="text-xs text-gray-400">Kupon yang dibuat akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="coupons.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ coupons.from }}-{{ coupons.to }}</strong> dari <strong>{{ coupons.total }}</strong> kupon
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in coupons.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active 
                                ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md' 
                                : link.url 
                                    ? 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' 
                                    : 'bg-gray-50 text-gray-300 cursor-not-allowed border border-gray-100'
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <!-- Header -->
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Kupon</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus kupon <strong class="text-gray-900 font-mono">"{{ selectedCoupon?.code }}"</strong>?
                        </p>
                        
                        <div class="flex gap-3 justify-end">
                            <button 
                                @click="closeModal"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                @click="confirmDelete"
                                :disabled="isDeleting"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
                                <Trash2 v-else class="w-4 h-4" />
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    transform: translateY(-16px);
}

.modal-enter-active, .modal-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
.modal-enter-from .relative, .modal-leave-to .relative {
    transform: scale(0.95) translateY(10px);
}
</style>
