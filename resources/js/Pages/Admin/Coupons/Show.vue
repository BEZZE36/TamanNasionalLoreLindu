<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Ticket, Calendar, Users, DollarSign, Percent, Tag, Clock, CheckCircle, XCircle, User, MapPin, CreditCard } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    coupon: Object
});

const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
const formatDateTime = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
const formatPrice = (price) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price || 0);

const isExpired = () => props.coupon?.end_date && new Date(props.coupon.end_date) < new Date();
const usagePercent = () => props.coupon?.usage_limit ? Math.round((props.coupon.usages?.length || 0) / props.coupon.usage_limit * 100) : 0;
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-500/30">
                    <Ticket class="w-7 h-7 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Detail Kupon</h1>
                    <p class="text-gray-500 text-sm flex items-center gap-2">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-mono font-bold rounded-lg">{{ coupon?.code }}</span>
                        <span :class="coupon?.is_active ? 'text-green-600' : 'text-gray-500'">{{ coupon?.is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <Link :href="`/admin/coupons/${coupon?.id}/edit`" class="px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all">Edit</Link>
                <Link href="/admin/coupons" class="px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" /> Kembali
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Coupon Details -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ coupon?.name }}</h3>
                    <p v-if="coupon?.description" class="text-gray-600 mb-6">{{ coupon.description }}</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-4 text-center">
                            <div class="text-xl font-bold text-emerald-600">
                                <template v-if="coupon?.discount_type === 'percentage'">{{ coupon.discount_value }}%</template>
                                <template v-else>{{ formatPrice(coupon?.discount_value) }}</template>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Diskon</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <div class="text-lg font-bold text-gray-900">{{ coupon?.usages?.length || 0 }}/{{ coupon?.usage_limit || '∞' }}</div>
                            <div class="text-xs text-gray-500 mt-1">Penggunaan</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <div class="text-lg font-bold text-gray-900">{{ formatPrice(coupon?.min_order_amount) }}</div>
                            <div class="text-xs text-gray-500 mt-1">Min. Order</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 text-center">
                            <div class="text-lg font-bold text-gray-900">{{ formatPrice(coupon?.max_discount) }}</div>
                            <div class="text-xs text-gray-500 mt-1">Max. Diskon</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div v-if="coupon?.usage_limit" class="mt-6">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Penggunaan</span>
                            <span class="font-semibold">{{ usagePercent() }}%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div :style="{width: usagePercent() + '%'}" class="h-full bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full transition-all duration-500"></div>
                        </div>
                    </div>
                </div>

                <!-- Usage History -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><Users class="w-5 h-5 text-emerald-500" /> Riwayat Penggunaan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table v-if="coupon?.usages?.length" class="w-full">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Booking</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Diskon</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="usage in coupon.usages" :key="usage.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3"><div class="flex items-center gap-2"><User class="w-4 h-4 text-gray-400" />{{ usage.user?.name || '-' }}</div></td>
                                    <td class="px-4 py-3 text-gray-600">#{{ usage.booking?.id || '-' }}</td>
                                    <td class="px-6 py-4 text-emerald-600 font-semibold">{{ formatPrice(usage.discount_amount) }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-sm">{{ formatDateTime(usage.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="px-6 py-12 text-center text-gray-500">
                            <Users class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                            <p>Belum ada penggunaan kupon</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Status -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-600">Aktif</span>
                            <span :class="coupon?.is_active ? 'text-green-600' : 'text-red-500'" class="font-semibold flex items-center gap-1">
                                <CheckCircle v-if="coupon?.is_active" class="w-4 h-4" /><XCircle v-else class="w-4 h-4" />{{ coupon?.is_active ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-gray-600">Expired</span>
                            <span :class="isExpired() ? 'text-red-500' : 'text-green-600'" class="font-semibold">{{ isExpired() ? 'Ya' : 'Tidak' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Validity Period -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><Calendar class="w-5 h-5 text-blue-500" /> Periode</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-600">Mulai</span>
                            <span class="text-gray-900 font-medium">{{ formatDate(coupon?.start_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-gray-600">Berakhir</span>
                            <span class="text-gray-900 font-medium">{{ formatDate(coupon?.end_date) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Limits -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Batas Penggunaan</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-600">Total Limit</span>
                            <span class="text-gray-900 font-medium">{{ coupon?.usage_limit || 'Tidak terbatas' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-gray-600">Per User</span>
                            <span class="text-gray-900 font-medium">{{ coupon?.per_user_limit || 'Tidak terbatas' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
