<script setup>
/**
 * Admin Feedback Index - Premium Design matching Newsletter
 * Features: GSAP animations, glassmorphism, optimistic updates
 */
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    MessageSquare, Search, Plus, Eye, Edit, Archive, Star, Filter,
    ChevronLeft, ChevronRight, CheckCircle, XCircle, Loader2, AlertTriangle,
    Quote, MapPin, User, Calendar, ToggleLeft, ToggleRight, Trash2, Sparkles,
    MessageCircle, StarOff, TrendingUp
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    feedbacks: { type: Object, required: true },
    stats: { type: Object, default: () => ({ unread: 0, total: 0, published: 0, avgRating: 0 }) },
    filters: { type: Object, default: () => ({}) }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Local reactive copies for optimistic updates
const localFeedbacks = ref(props.feedbacks?.data ? JSON.parse(JSON.stringify(props.feedbacks.data)) : []);
const localStats = ref(props.stats ? JSON.parse(JSON.stringify(props.stats)) : { unread: 0, total: 0, published: 0, avgRating: 0 });

watch(() => props.feedbacks, (newVal) => {
    localFeedbacks.value = newVal?.data ? JSON.parse(JSON.stringify(newVal.data)) : [];
}, { deep: true });

watch(() => props.stats, (newVal) => {
    localStats.value = newVal ? JSON.parse(JSON.stringify(newVal)) : { unread: 0, total: 0, published: 0, avgRating: 0 };
}, { deep: true });

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const rating = ref(props.filters?.rating || '');
const showDeleteModal = ref(false);
const selectedFeedback = ref(null);
const isDeleting = ref(false);
const isToggling = ref(null);
let ctx;

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

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 500); });
watch([status, rating], () => applyFilters());

const applyFilters = () => {
    router.get('/admin/testimonial', {
        search: search.value || undefined,
        status: status.value || undefined,
        rating: rating.value || undefined
    }, { preserveState: true, replace: true });
};

const togglePublish = (fb) => {
    isToggling.value = fb.id;
    
    // Optimistic update
    const index = localFeedbacks.value.findIndex(f => f.id === fb.id);
    const wasPublished = fb.is_published;
    
    if (index !== -1) {
        localFeedbacks.value[index].is_published = !wasPublished;
        if (wasPublished) {
            localStats.value.published--;
        } else {
            localStats.value.published++;
        }
    }
    
    router.patch(`/admin/testimonial/${fb.id}/status`, { is_published: !wasPublished }, { 
        preserveScroll: true,
        preserveState: true,
        onFinish: () => { isToggling.value = null; },
        onError: () => {
            // Revert on error
            if (index !== -1) {
                localFeedbacks.value[index].is_published = wasPublished;
                if (wasPublished) {
                    localStats.value.published++;
                } else {
                    localStats.value.published--;
                }
            }
        }
    });
};

const openDeleteModal = (fb) => { selectedFeedback.value = fb; showDeleteModal.value = true; };
const closeModal = () => { showDeleteModal.value = false; selectedFeedback.value = null; };

const confirmDelete = () => {
    if (!selectedFeedback.value) return;
    isDeleting.value = true;
    
    const feedbackId = selectedFeedback.value.id;
    const wasPublished = selectedFeedback.value.is_published;
    
    // Optimistic update
    const index = localFeedbacks.value.findIndex(f => f.id === feedbackId);
    let removedFeedback = null;
    if (index !== -1) {
        removedFeedback = localFeedbacks.value.splice(index, 1)[0];
        localStats.value.total--;
        if (wasPublished) localStats.value.published--;
        if (removedFeedback.status === 'unread') localStats.value.unread--;
    }
    
    router.delete(`/admin/testimonial/${feedbackId}`, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => { isDeleting.value = false; closeModal(); },
        onError: () => {
            // Revert on error
            if (removedFeedback && index !== -1) {
                localFeedbacks.value.splice(index, 0, removedFeedback);
                localStats.value.total++;
                if (wasPublished) localStats.value.published++;
                if (removedFeedback.status === 'unread') localStats.value.unread++;
            }
        }
    });
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

const statusBadge = (s) => {
    const badges = {
        unread: { class: 'from-red-500 to-rose-600 text-white', label: 'Belum Dibaca', icon: XCircle },
        read: { class: 'from-blue-500 to-indigo-600 text-white', label: 'Sudah Dibaca', icon: Eye },
        replied: { class: 'from-emerald-500 to-teal-600 text-white', label: 'Dibalas', icon: MessageCircle },
        archived: { class: 'from-gray-500 to-slate-600 text-white', label: 'Diarsipkan', icon: Archive }
    };
    return badges[s] || badges.unread;
};

const getStarColor = (i, r) => i <= r ? 'text-amber-400 fill-amber-400' : 'text-gray-200';
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header with Glassmorphism -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-pink-600 via-fuchsia-600 to-purple-600 p-6 shadow-2xl">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-purple-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 left-1/3 w-24 h-24 bg-pink-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-pink-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            <div class="absolute bottom-8 right-1/4 w-2 h-2 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Quote class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Testimoni & Ulasan</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ localStats.total }} Total
                            </span>
                        </div>
                        <p class="text-pink-100/80 text-xs">Kelola ulasan dan testimoni pengunjung</p>
                    </div>
                </div>
                
                <Link 
                    href="/admin/testimonial/create"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-pink-600 text-xs font-bold hover:bg-pink-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                >
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                    <span>Tambah Manual</span>
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
            <!-- Unread -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                        <MessageSquare class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.unread }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Belum Dibaca</p>
                    </div>
                </div>
            </div>
            
            <!-- Total -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-pink-100 to-fuchsia-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-fuchsia-600 shadow-lg shadow-pink-500/30 group-hover:scale-110 transition-transform">
                        <Quote class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Ulasan</p>
                    </div>
                </div>
            </div>
            
            <!-- Published -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.published }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Dipublikasikan</p>
                    </div>
                </div>
            </div>
            
            <!-- Average Rating -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <Star class="w-5 h-5 text-white fill-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ Number(localStats.avgRating || 0).toFixed(1) }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Rating Rata-rata</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3 justify-between">
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-pink-500 transition-colors" />
                        <input 
                            type="text" 
                            v-model="search"
                            placeholder="Cari nama atau pesan..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 focus:bg-white transition-all duration-300"
                        />
                    </div>
                    <select 
                        v-model="status"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Status</option>
                        <option value="unread">Belum Dibaca</option>
                        <option value="read">Sudah Dibaca</option>
                        <option value="replied">Sudah Dibalas</option>
                        <option value="archived">Diarsipkan</option>
                    </select>
                    <select 
                        v-model="rating"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-pink-500 focus:ring-4 focus:ring-pink-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Rating</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4">⭐⭐⭐⭐ (4)</option>
                        <option value="3">⭐⭐⭐ (3)</option>
                        <option value="2">⭐⭐ (2)</option>
                        <option value="1">⭐ (1)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Pengirim</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Pesan</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Rating</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Publik</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="(fb, index) in localFeedbacks" 
                            :key="fb.id" 
                            class="table-row group hover:bg-gradient-to-r hover:from-pink-50/50 hover:to-fuchsia-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-pink-400 to-fuchsia-500 flex items-center justify-center text-white text-xs font-bold shadow-lg">
                                        {{ fb.is_anonymous ? '?' : (fb.display_name?.[0] || 'A') }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-xs text-gray-900">{{ fb.is_anonymous ? 'Anonim' : fb.display_name }}</p>
                                        <div class="flex items-center gap-1 text-[10px] text-gray-500">
                                            <Calendar class="w-3 h-3" />
                                            {{ formatDate(fb.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-xs text-gray-700 line-clamp-2 max-w-xs">{{ fb.message }}</p>
                                <div v-if="fb.destination" class="flex items-center gap-1 text-[10px] text-pink-600 mt-1">
                                    <MapPin class="w-3 h-3" />
                                    {{ fb.destination.name }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-center gap-0.5">
                                    <Star 
                                        v-for="i in 5" 
                                        :key="i" 
                                        :class="['w-3.5 h-3.5 transition-transform group-hover:scale-110', getStarColor(i, fb.rating)]" 
                                    />
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span 
                                    :class="['inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm', statusBadge(fb.status).class]"
                                >
                                    <component :is="statusBadge(fb.status).icon" class="w-3 h-3" />
                                    {{ statusBadge(fb.status).label }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <button 
                                    @click="togglePublish(fb)"
                                    :disabled="isToggling === fb.id"
                                    :class="[
                                        'p-2 rounded-lg transition-all duration-200',
                                        fb.is_published 
                                            ? 'text-emerald-600 hover:bg-emerald-50' 
                                            : 'text-gray-400 hover:bg-gray-50'
                                    ]"
                                    :title="fb.is_published ? 'Klik untuk unpublish' : 'Klik untuk publish'"
                                >
                                    <Loader2 v-if="isToggling === fb.id" class="w-4 h-4 animate-spin" />
                                    <ToggleRight v-else-if="fb.is_published" class="w-5 h-5" />
                                    <ToggleLeft v-else class="w-5 h-5" />
                                </button>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <Link 
                                        :href="`/admin/testimonial/${fb.id}`" 
                                        class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 hover:shadow-sm transition-all"
                                        title="Lihat"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link 
                                        :href="`/admin/testimonial/${fb.id}/edit`" 
                                        class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 hover:shadow-sm transition-all"
                                        title="Edit"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button 
                                        @click="openDeleteModal(fb)" 
                                        class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:shadow-sm transition-all"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="localFeedbacks.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-pink-100 to-fuchsia-200 flex items-center justify-center mb-4 shadow-lg">
                                        <Quote class="w-8 h-8 text-pink-500" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada ulasan</p>
                                    <p class="text-xs text-gray-400">Ulasan dari pengunjung akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="feedbacks.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ feedbacks.from }}-{{ feedbacks.to }}</strong> dari <strong>{{ feedbacks.total }}</strong> ulasan
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in feedbacks.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active 
                                ? 'bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white shadow-md' 
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

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Ulasan</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus ulasan dari <strong class="text-gray-900">"{{ selectedFeedback?.display_name }}"</strong>?
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
