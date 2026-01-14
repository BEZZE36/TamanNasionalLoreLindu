<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    User, ArrowLeft, Edit, Mail, Phone, Calendar, MapPin, Shield, ShieldOff,
    CreditCard, TrendingUp, CheckCircle, XCircle, Eye, Sparkles, Globe,
    ExternalLink, Hash, Ticket
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ 
    user: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) }
});

const pageRef = ref(null);
const isGoogleUser = computed(() => !!props.user.google_id);

const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.split(' ');
    return parts.length >= 2 ? parts[0].charAt(0) + parts[1].charAt(0) : name.charAt(0);
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

const formatPhone = (phone) => {
    if (!phone) return '-';
    let digits = phone.replace(/\D/g, '');
    if (digits.startsWith('0')) digits = '62' + digits.slice(1);
    if (!digits.startsWith('62')) digits = '62' + digits;
    const countryCode = '+62';
    const rest = digits.slice(2);
    if (rest.length === 0) return countryCode;
    let formatted = countryCode;
    if (rest.length > 0) formatted += ' ' + rest.slice(0, 3);
    if (rest.length > 3) formatted += ' ' + rest.slice(3, 7);
    if (rest.length > 7) formatted += ' ' + rest.slice(7, 11);
    return formatted;
};

const formatCurrency = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);

// Normalize legacy statuses to new 4 statuses
const normalizeStatus = (status) => {
    const mapping = {
        'pending': 'pending',
        'awaiting_cash': 'pending',
        'paid': 'confirmed',
        'confirmed': 'confirmed',
        'used': 'used',
        'cancelled': 'cancelled',
        'expired': 'cancelled',
        'refunded': 'cancelled'
    };
    return mapping[status] || status;
};

const getBookingStatus = (status) => {
    const normalizedStat = normalizeStatus(status);
    return {
        'confirmed': { label: 'Terkonfirmasi', class: 'from-emerald-500 to-teal-600 text-white' },
        'used': { label: 'Digunakan', class: 'from-blue-500 to-indigo-600 text-white' },
        'pending': { label: 'Pending', class: 'from-amber-500 to-orange-600 text-white' },
        'cancelled': { label: 'Dibatalkan', class: 'from-red-500 to-rose-600 text-white' }
    }[normalizedStat] || { label: status, class: 'from-gray-500 to-gray-600 text-white' };
};

const toggleBlock = () => {
    const action = props.user.status === 'active' ? 'block' : 'unblock';
    router.post(`/admin/users/${props.user.id}/${action}`, {}, { preserveScroll: true });
};

let ctx;
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.info-card', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.1, delay: 0.2, ease: 'power2.out' }
        );
    }, pageRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div ref="pageRef" class="space-y-5">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link href="/admin/users" class="p-2.5 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-colors">
                        <ArrowLeft class="w-4 h-4 text-white" />
                    </Link>
                    <div class="relative">
                        <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-xl font-bold shadow-lg overflow-hidden">
                            <img v-if="user.avatar_url && !user.avatar_url.includes('ui-avatars')" 
                                :src="user.avatar_url" :alt="user.name" class="w-full h-full object-cover">
                            <span v-else>{{ getInitials(user.name) }}</span>
                        </div>
                        <div v-if="isGoogleUser" class="absolute -bottom-1 -right-1 w-5 h-5 rounded bg-white shadow flex items-center justify-center">
                            <svg class="w-3 h-3" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        </div>
                        <div :class="['absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-white flex items-center justify-center', user.status === 'active' ? 'bg-emerald-500' : 'bg-red-500']" v-if="!isGoogleUser">
                            <component :is="user.status === 'active' ? CheckCircle : XCircle" class="w-2.5 h-2.5 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h1 class="text-lg font-bold text-white">{{ user.name }}</h1>
                            <span :class="['px-2 py-0.5 rounded-full text-[9px] font-bold', user.status === 'active' ? 'bg-emerald-500/20 text-white' : 'bg-red-500/20 text-white']">
                                {{ user.status === 'active' ? '✓ Aktif' : '✕ Diblokir' }}
                            </span>
                        </div>
                        <p class="text-orange-100/80 text-[11px]">@{{ user.username }} · {{ user.email }}</p>
                        <div v-if="isGoogleUser" class="flex items-center gap-1 mt-1 px-2 py-0.5 bg-white/20 rounded backdrop-blur-sm w-fit">
                            <svg class="w-3 h-3" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#fff"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#fff"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#fff"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#fff"/></svg>
                            <span class="text-[9px] font-semibold text-white">Akun Google</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button @click="toggleBlock" :class="['inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-lg hover:-translate-y-0.5', user.status === 'active' ? 'bg-white/20 text-white hover:bg-red-500' : 'bg-white/20 text-white hover:bg-emerald-500']">
                        <component :is="user.status === 'active' ? ShieldOff : Shield" class="w-4 h-4" />
                        {{ user.status === 'active' ? 'Blokir' : 'Aktifkan' }}
                    </button>
                    <Link :href="`/admin/users/${user.id}/edit`"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-orange-600 text-xs font-bold hover:shadow-xl hover:-translate-y-0.5 transition-all shadow-lg">
                        <Edit class="w-4 h-4" />
                        Edit User
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.total_bookings || 0 }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Booking</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <CreditCard class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-lg font-black text-gray-900">{{ formatCurrency(stats.total_spent) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Transaksi</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.completed_bookings || 0 }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Selesai</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.this_month || 0 }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- User Info -->
            <div class="lg:col-span-1 space-y-4">
                <div class="info-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-orange-50 to-amber-50 border-b border-orange-100/50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-lg shadow-orange-500/25">
                                <User class="w-4 h-4 text-white" />
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">Informasi User</h3>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-start gap-3">
                            <Mail class="w-4 h-4 text-gray-400 mt-0.5" />
                            <div>
                                <p class="text-[10px] text-gray-500 font-medium">Email</p>
                                <p class="text-xs font-semibold text-gray-900">{{ user.email }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Phone class="w-4 h-4 text-gray-400 mt-0.5" />
                            <div>
                                <p class="text-[10px] text-gray-500 font-medium">Telepon</p>
                                <p class="text-xs font-semibold text-gray-900">{{ formatPhone(user.phone) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <MapPin class="w-4 h-4 text-gray-400 mt-0.5" />
                            <div>
                                <p class="text-[10px] text-gray-500 font-medium">Alamat</p>
                                <p class="text-xs font-semibold text-gray-900">{{ user.address || '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Calendar class="w-4 h-4 text-gray-400 mt-0.5" />
                            <div>
                                <p class="text-[10px] text-gray-500 font-medium">Bergabung</p>
                                <p class="text-xs font-semibold text-gray-900">{{ formatDate(user.created_at) }}</p>
                            </div>
                        </div>
                        <div v-if="isGoogleUser" class="pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg border border-blue-200/50">
                                <svg class="w-4 h-4" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                <div>
                                    <p class="text-[10px] font-bold text-blue-700">Akun Google</p>
                                    <p class="text-[9px] text-blue-600">Login menggunakan Google</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="lg:col-span-2">
                <div class="info-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-5 py-3.5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                                    <Ticket class="w-4 h-4 text-white" />
                                </div>
                                <h3 class="text-sm font-bold text-gray-900">Booking Terbaru</h3>
                            </div>
                            <span class="text-[10px] font-medium text-gray-500">{{ user.bookings?.length || 0 }} booking</span>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="booking in user.bookings" :key="booking.id" 
                            class="p-4 hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-indigo-50/20 transition-colors group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                        <Hash class="w-4 h-4 text-blue-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">{{ booking.order_number }}</p>
                                        <p class="text-[10px] text-gray-500">{{ booking.destination?.name || '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span :class="['px-2.5 py-1 rounded-full text-[9px] font-bold bg-gradient-to-r shadow-sm', getBookingStatus(booking.status).class]">
                                        {{ getBookingStatus(booking.status).label }}
                                    </span>
                                    <span class="text-xs font-bold text-gray-900">{{ formatCurrency(booking.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="!user.bookings?.length" class="p-8 text-center">
                            <div class="w-14 h-14 mx-auto rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                                <Ticket class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-xs font-semibold text-gray-600">Belum ada booking</p>
                            <p class="text-[10px] text-gray-400">User belum melakukan pemesanan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
