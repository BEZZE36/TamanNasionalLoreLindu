<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { Plus, Search, Edit, Trash2, Eye, MapPin, Star, Copy, CheckCircle, AlertTriangle, Sparkles } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({ destinations: Object, filters: { type: Object, default: () => ({}) } });

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const selectedIds = ref([]);
const selectAll = ref(false);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.stat-card', { opacity: 0, y: 20, scale: 0.95 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'back.out(1.7)' });
        gsap.fromTo('.table-row', { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.4, ease: 'power2.out' });
    });
});
onBeforeUnmount(() => { if (ctx) ctx.revert(); });

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => { router.get('/admin/destinations', { search: search.value || undefined, status: status.value || undefined }, { preserveState: true }); };
const toggleSelectAll = () => { selectedIds.value = selectAll.value ? props.destinations.data.map(d => d.id) : []; };
const toggleSelect = (id) => { const i = selectedIds.value.indexOf(id); i > -1 ? selectedIds.value.splice(i, 1) : selectedIds.value.push(id); };
const confirmDelete = (dest) => { deleteTarget.value = dest; showDeleteModal.value = true; };
const deleteDestination = () => { if (deleteTarget.value) { router.delete(`/admin/destinations/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; } }); }};
const bulkDelete = () => { if (!selectedIds.value.length || !confirm(`Hapus ${selectedIds.value.length} destinasi?`)) return; fetch('/admin/destinations/bulk-delete', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }, body: JSON.stringify({ ids: selectedIds.value }) }).then(() => { selectedIds.value = []; router.reload(); }); };
const toggleActive = async (dest) => { await fetch(`/admin/destinations/${dest.id}/toggle-active`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); router.reload(); };
const toggleFeatured = async (dest) => { await fetch(`/admin/destinations/${dest.id}/toggle-featured`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); router.reload(); };
const duplicate = async (dest) => { const res = await fetch(`/admin/destinations/${dest.id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }}); const data = await res.json(); if (data.redirect) window.location.href = data.redirect; };
const formatPrice = (price) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price || 0);

const totalDestinations = computed(() => props.destinations?.total || props.destinations?.data?.length || 0);
const activeCount = computed(() => props.destinations?.data?.filter(d => d.is_active).length || 0);
const featuredCount = computed(() => props.destinations?.data?.filter(d => d.is_featured).length || 0);
</script>

<template>
    <div class="min-h-screen space-y-5">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-500 via-green-500 to-teal-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden"><div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div><div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div></div>
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-emerald-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="header-content relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative"><div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div><div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl"><MapPin class="h-6 w-6 text-white" /></div></div>
                    <div><div class="flex items-center gap-2 mb-1"><h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Destinasi Wisata</h1><span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90">{{ totalDestinations }} Total</span></div><p class="text-emerald-100/80 text-xs">Kelola semua destinasi wisata</p></div>
                </div>
                <Link href="/admin/destinations/create" class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-600 text-xs font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all shadow-lg"><Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" /><span>Tambah Destinasi</span><Sparkles class="w-3.5 h-3.5 text-amber-500" /></Link>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"><div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div><div class="relative flex items-center gap-3"><div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);"><MapPin class="w-5 h-5 text-white" /></div><div><p class="text-2xl font-black text-white">{{ totalDestinations }}</p><p class="text-[10px] text-white/80">Total Destinasi</p></div></div></div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);"><div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div><div class="relative flex items-center gap-3"><div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);"><CheckCircle class="w-5 h-5 text-white" /></div><div><p class="text-2xl font-black text-white">{{ activeCount }}</p><p class="text-[10px] text-white/80">Aktif</p></div></div></div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"><div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div><div class="relative flex items-center gap-3"><div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);"><Star class="w-5 h-5 text-white fill-white" /></div><div><p class="text-2xl font-black text-white">{{ featuredCount }}</p><p class="text-[10px] text-white/80">Unggulan</p></div></div></div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3 justify-between">
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md group"><Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-emerald-500 transition-colors" /><input type="text" v-model="search" placeholder="Cari destinasi..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all" /></div>
                    <select v-model="status" @change="applyFilters" class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 cursor-pointer"><option value="">Semua Status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select>
                </div>
                <button v-if="selectedIds.length" @click="bulkDelete" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all"><Trash2 class="w-4 h-4" /><span>Hapus ({{ selectedIds.length }})</span></button>
            </div>
        </div>

        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left w-12"><input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" /></th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Destinasi</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Lokasi</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Harga</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Unggulan</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="dest in destinations.data" :key="dest.id" class="table-row group hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/30 transition-all">
                            <td class="px-4 py-3"><input type="checkbox" :checked="selectedIds.includes(dest.id)" @change="toggleSelect(dest.id)" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex-shrink-0"><img :src="dest.images?.[0]?.image_url || '/images/placeholder-no-image.svg'" :alt="dest.name" class="w-16 h-16 rounded-xl object-cover shadow-lg ring-2 ring-gray-100 group-hover:ring-emerald-200 transition-all" @error="(e) => e.target.src='/images/placeholder-no-image.svg'" /><div v-if="dest.images?.length > 1" class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 rounded-lg flex items-center justify-center shadow"><span class="text-[9px] text-white font-bold">{{ dest.images.length }}</span></div></div>
                                    <div class="min-w-0"><p class="text-sm font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ dest.name }}</p><p class="text-[11px] text-gray-400 mt-0.5">{{ dest.category?.name || 'Umum' }}</p></div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><div class="flex items-center gap-1.5 text-xs text-gray-600"><MapPin class="w-3.5 h-3.5 text-gray-400" /><span>{{ dest.city }}</span></div></td>
                            <td class="px-4 py-3"><span class="text-sm font-semibold text-gray-900">{{ formatPrice(dest.prices?.[0]?.price) }}</span></td>
                            <td class="px-4 py-3 text-center"><button @click="toggleActive(dest)" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold shadow-sm transition-all', dest.is_active ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white' : 'bg-gray-200 text-gray-600']"><span :class="['w-1.5 h-1.5 rounded-full', dest.is_active ? 'bg-white' : 'bg-gray-500']"></span>{{ dest.is_active ? 'Aktif' : 'Nonaktif' }}</button></td>
                            <td class="px-4 py-3 text-center"><button @click="toggleFeatured(dest)" :class="['p-2.5 rounded-xl transition-all', dest.is_featured ? 'bg-amber-100 text-amber-600 hover:bg-amber-200 shadow-sm' : 'text-gray-300 hover:bg-gray-100 hover:text-gray-400']"><Star :class="['w-5 h-5', dest.is_featured ? 'fill-amber-500' : '']" /></button></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <Link v-if="dest.is_active" :href="`/destinations/${dest.slug || dest.id}`" target="_blank" class="p-2 rounded-lg text-gray-400 hover:text-violet-600 hover:bg-violet-50 transition-all"><Eye class="w-4 h-4" /></Link>
                                    <Link :href="`/admin/destinations/${dest.id}/edit`" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all"><Edit class="w-4 h-4" /></Link>
                                    <button @click="duplicate(dest)" class="p-2 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all"><Copy class="w-4 h-4" /></button>
                                    <button @click="confirmDelete(dest)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all"><Trash2 class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!destinations.data?.length"><td colspan="7" class="px-6 py-16 text-center"><div class="flex flex-col items-center"><div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center mb-4"><MapPin class="w-8 h-8 text-emerald-400" /></div><p class="text-sm font-semibold text-gray-600 mb-1">Belum ada destinasi</p><p class="text-xs text-gray-400 mb-4">Mulai tambahkan destinasi wisata pertama</p><Link href="/admin/destinations/create" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-xs font-bold shadow-lg"><Plus class="w-4 h-4" />Tambah Destinasi</Link></div></td></tr>
                    </tbody>
                </table>
            </div>
            <div v-if="destinations.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50"><p class="text-[11px] text-gray-500">Menampilkan <strong>{{ destinations.from }}-{{ destinations.to }}</strong> dari <strong>{{ destinations.total }}</strong> destinasi</p><div class="flex gap-1"><Link v-for="link in destinations.links" :key="link.label" :href="link.url || '#'" :class="['px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all', link.active ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md' : link.url ? 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' : 'bg-gray-50 text-gray-300 cursor-not-allowed border border-gray-100']" v-html="link.label" preserve-scroll /></div></div>
        </div>

        <Teleport to="body"><Transition name="modal"><div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"><div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div><div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6"><div class="flex items-center gap-4 mb-5"><div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100"><AlertTriangle class="w-6 h-6 text-red-600" /></div><div><h3 class="text-lg font-bold text-gray-900">Hapus Destinasi</h3><p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p></div></div><p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus destinasi <strong class="text-gray-900">"{{ deleteTarget?.name }}"</strong>?</p><div class="flex gap-3 justify-end"><button @click="showDeleteModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all">Batal</button><button @click="deleteDestination" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all flex items-center gap-2"><Trash2 class="w-4 h-4" /> Hapus</button></div></div></div></Transition></Teleport>
    </div>
</template>

<style scoped>.modal-enter-active, .modal-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }.modal-enter-from, .modal-leave-to { opacity: 0; }</style>
