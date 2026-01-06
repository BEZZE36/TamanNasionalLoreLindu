<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MapPin, TrendingUp, Users, Star, DollarSign } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ destinations: { type: Array, default: () => [] } });

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0);
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <MapPin class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Laporan Destinasi</h1>
                        <p class="mt-1 text-amber-100/90 text-lg">Performa setiap destinasi</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Link href="/admin/reports/revenue" class="px-5 py-3 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition-all">Pendapatan</Link>
                    <Link href="/admin/reports/visitors" class="px-5 py-3 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition-all">Pengunjung</Link>
                </div>
            </div>
        </div>

        <!-- Destinations Table -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Destinasi</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Booking</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Paid</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase">Ulasan</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(dest, idx) in destinations" :key="dest.id" class="hover:bg-amber-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm', idx < 3 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600']">
                                    {{ idx + 1 }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img v-if="dest.image_url" :src="dest.image_url" :alt="dest.name" class="w-12 h-12 rounded-xl object-cover shadow" @error="(e) => e.target.style.display='none'">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold" v-else>
                                        {{ dest.name?.charAt(0) || 'D' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ dest.name }}</p>
                                        <p class="text-xs text-gray-500">{{ dest.category?.name || 'Uncategorized' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">{{ dest.bookings_count || 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-bold">{{ dest.paid_bookings_count || 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Star class="w-4 h-4 text-amber-400" />
                                    <span class="font-bold text-gray-700">{{ dest.reviews_count || 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="font-bold text-emerald-600">{{ formatCurrency(dest.total_revenue) }}</p>
                            </td>
                        </tr>
                        <tr v-if="!destinations.length">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <MapPin class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                <p class="font-medium">Tidak ada data destinasi</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
