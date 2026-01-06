<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Ticket, Plus, Edit, Trash2, ToggleLeft, ToggleRight, Percent, CircleDollarSign, Users, Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({ coupons: { type: Object, required: true } });

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const openDelete = (c) => { deleteTarget.value = c; showDeleteModal.value = true; };
const closeDelete = () => { deleteTarget.value = null; showDeleteModal.value = false; };
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/coupons/${deleteTarget.value.id}`, { onSuccess: () => closeDelete() });
    }
};

const toggleActive = (coupon) => {
    router.post(`/admin/coupons/${coupon.id}/toggle`, {}, { preserveScroll: true });
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);

const stats = computed(() => ({
    total: props.coupons?.total || 0,
    active: props.coupons?.data?.filter(c => c.is_active).length || 0,
    expired: props.coupons?.data?.filter(c => c.end_date && new Date(c.end_date) < new Date()).length || 0
}));
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-60 h-60 bg-white/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                        <Ticket class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Manajemen Kupon</h1>
                        <p class="mt-1 text-green-100/90 text-lg">Kelola kode diskon dan promo</p>
                    </div>
                </div>
                <Link href="/admin/coupons/create"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-xl text-white rounded-xl font-bold hover:bg-white/30 transition-all shadow-lg hover:-translate-y-0.5">
                    <Plus class="w-4 h-4" /><span>Buat Kupon</span>
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3"><div class="p-3 rounded-xl bg-green-100"><Ticket class="w-6 h-6 text-green-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Total Kupon</p><p class="text-lg font-bold text-gray-900">{{ stats.total }}</p></div></div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3"><div class="p-3 rounded-xl bg-emerald-100"><ToggleRight class="w-6 h-6 text-emerald-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Aktif</p><p class="text-2xl font-bold text-emerald-600">{{ stats.active }}</p></div></div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <div class="flex items-center gap-3"><div class="p-3 rounded-xl bg-red-100"><Calendar class="w-6 h-6 text-red-600" /></div>
                    <div><p class="text-[11px] text-gray-500">Expired</p><p class="text-2xl font-bold text-red-600">{{ stats.expired }}</p></div></div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Diskon</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Penggunaan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Periode</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="coupon in coupons.data" :key="coupon.id" class="hover:bg-green-50/30 transition-colors group">
                            <td class="px-4 py-4">
                                <div><p class="font-bold text-gray-900 font-mono text-lg">{{ coupon.code }}</p><p class="text-xs text-gray-500">{{ coupon.name }}</p></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <div :class="['p-2 rounded-lg', coupon.discount_type === 'percentage' ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600']">
                                        <Percent v-if="coupon.discount_type === 'percentage'" class="w-4 h-4" />
                                        <CircleDollarSign v-else class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ coupon.discount_type === 'percentage' ? coupon.discount_value + '%' : formatCurrency(coupon.discount_value) }}</p>
                                        <p v-if="coupon.max_discount" class="text-xs text-gray-500">Max: {{ formatCurrency(coupon.max_discount) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Users class="w-4 h-4 text-gray-400" />
                                    <span class="font-bold text-gray-900">{{ coupon.usages_count || 0 }}</span>
                                    <span v-if="coupon.usage_limit" class="text-gray-400">/ {{ coupon.usage_limit }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm text-gray-600">{{ formatDate(coupon.start_date) }} - {{ formatDate(coupon.end_date) }}</p>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <button @click="toggleActive(coupon)" :class="['px-3 py-1.5 rounded-full text-xs font-bold transition-colors', coupon.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                    {{ coupon.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="`/admin/coupons/${coupon.id}/edit`" class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"><Edit class="w-4 h-4" /></Link>
                                    <button @click="openDelete(coupon)" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"><Trash2 class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!coupons.data?.length"><td colspan="6" class="px-4 py-12 text-center text-gray-500"><Ticket class="w-12 h-12 mx-auto mb-4 text-gray-300" /><p class="font-medium">Belum ada kupon</p></td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="coupons.last_page > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">Halaman {{ coupons.current_page }} dari {{ coupons.last_page }}</p>
                <div class="flex gap-2">
                    <Link v-if="coupons.prev_page_url" :href="coupons.prev_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronLeft class="w-5 h-5" /></Link>
                    <Link v-if="coupons.next_page_url" :href="coupons.next_page_url" class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50"><ChevronRight class="w-5 h-5" /></Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeDelete">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Kupon</h3>
                <p class="text-gray-600 mb-6">Yakin ingin menghapus kupon "<strong class="text-green-600 font-mono">{{ deleteTarget?.code }}</strong>"?</p>
                <div class="flex gap-3 justify-end">
                    <button @click="closeDelete" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="confirmDelete" class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
