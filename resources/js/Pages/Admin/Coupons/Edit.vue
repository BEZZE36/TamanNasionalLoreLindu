<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, Ticket, Percent, CircleDollarSign, Calendar, Users, Power } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    coupon: { type: Object, required: true },
    destinations: { type: Array, default: () => [] }
});

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
    start_date: props.coupon.start_date?.split('T')[0] || '',
    end_date: props.coupon.end_date?.split('T')[0] || '',
    is_active: props.coupon.is_active ?? true,
    applicable_destinations: props.coupon.applicable_destinations || []
});

const submit = () => { form.put(`/admin/coupons/${props.coupon.id}`); };
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <Link href="/admin/coupons" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-6 h-6" />
                </Link>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Kupon</h1>
                    <p class="mt-1 text-green-100/90 font-mono">{{ coupon.code }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Basic Info -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Ticket class="w-6 h-6 text-green-500" />Informasi Kupon</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Kupon *</label>
                            <input v-model="form.code" type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 font-mono uppercase">
                            <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kupon *</label>
                            <input v-model="form.name" type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea v-model="form.description" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20"></textarea>
                    </div>
                </div>
            </div>

            <!-- Discount Settings -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Percent class="w-6 h-6 text-blue-500" />Pengaturan Diskon</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Diskon *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label :class="['flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all', form.discount_type === 'percentage' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300']">
                                <input type="radio" v-model="form.discount_type" value="percentage" class="hidden">
                                <Percent :class="['w-6 h-6', form.discount_type === 'percentage' ? 'text-blue-600' : 'text-gray-400']" />
                                <div><div :class="['font-medium', form.discount_type === 'percentage' ? 'text-blue-900' : 'text-gray-700']">Persentase</div></div>
                            </label>
                            <label :class="['flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all', form.discount_type === 'fixed' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300']">
                                <input type="radio" v-model="form.discount_type" value="fixed" class="hidden">
                                <CircleDollarSign :class="['w-6 h-6', form.discount_type === 'fixed' ? 'text-amber-600' : 'text-gray-400']" />
                                <div><div :class="['font-medium', form.discount_type === 'fixed' ? 'text-amber-900' : 'text-gray-700']">Nominal Tetap</div></div>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ form.discount_type === 'percentage' ? 'Nilai (%)' : 'Nilai (Rp)' }} *</label>
                            <input v-model="form.discount_value" type="number" required min="0" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Min. Order (Rp)</label>
                            <input v-model="form.min_order_amount" type="number" min="0" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Diskon (Rp)</label>
                            <input v-model="form.max_discount" type="number" min="0" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage & Period -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Users class="w-6 h-6 text-purple-500" />Batas & Periode</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Batas Total</label>
                            <input v-model="form.usage_limit" type="number" min="1" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Batas Per User</label>
                            <input v-model="form.per_user_limit" type="number" min="1" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><Calendar class="w-4 h-4 inline mr-1" />Mulai</label>
                            <input v-model="form.start_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><Calendar class="w-4 h-4 inline mr-1" />Berakhir</label>
                            <input v-model="form.end_date" type="date" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-green-300 transition-all">
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-green-500 focus:ring-green-500">
                            <Power class="w-5 h-5 text-green-500" />
                            <span class="font-medium text-gray-700">Aktifkan Kupon</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-green-500 to-emerald-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-green-500/30 hover:shadow-green-500/50 hover:-translate-y-1 transition-all disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Perbarui Kupon' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
