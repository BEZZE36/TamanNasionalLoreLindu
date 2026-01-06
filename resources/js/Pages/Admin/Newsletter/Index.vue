<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Mail, Search, Filter, Download, Trash2, ToggleLeft, ToggleRight,
    CheckCircle, XCircle, Users, UserCheck, UserX, Calendar, Loader2, 
    AlertTriangle, Send, Sparkles, TrendingUp, Eye, MoreVertical,
    Shield, ShieldOff, RefreshCw
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    subscribers: Object,
    stats: Object,
    filters: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Local reactive copy of subscribers for instant UI updates
const localSubscribers = ref(JSON.parse(JSON.stringify(props.subscribers.data)));
const localStats = ref(JSON.parse(JSON.stringify(props.stats)));

// Sync with props when they change (e.g., pagination, filter)
watch(() => props.subscribers, (newVal) => {
    localSubscribers.value = JSON.parse(JSON.stringify(newVal.data));
}, { deep: true });

watch(() => props.stats, (newVal) => {
    localStats.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

// Local state
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const selectedIds = ref([]);
const isDeleting = ref(false);
const isToggling = ref(null);
const selectAll = ref(false);
const showDeleteModal = ref(false);
const selectedSubscriber = ref(null);
const tableRef = ref(null);
let ctx;

// Animation on mount
onMounted(() => {
    ctx = gsap.context(() => {
        // Animate stats cards
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        
        // Animate table rows
        gsap.fromTo('.table-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

// Watch for select all
watch(selectAll, (val) => {
    if (val) {
        selectedIds.value = localSubscribers.value.map(s => s.id);
    } else {
        selectedIds.value = [];
    }
});

// Search with debounce
let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

watch(status, () => {
    applyFilters();
});

const applyFilters = () => {
    router.get('/admin/newsletter', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const toggleStatus = (subscriber) => {
    isToggling.value = subscriber.id;
    
    // Optimistic update - update local state immediately
    const index = localSubscribers.value.findIndex(s => s.id === subscriber.id);
    if (index !== -1) {
        const wasActive = localSubscribers.value[index].is_active;
        localSubscribers.value[index].is_active = !wasActive;
        localSubscribers.value[index].disabled_by_admin = wasActive; // If was active, now disabled by admin
        
        // Update stats
        if (wasActive) {
            localStats.value.active--;
            localStats.value.inactive++;
        } else {
            localStats.value.active++;
            localStats.value.inactive--;
        }
    }
    
    router.patch(`/admin/newsletter/${subscriber.id}/toggle`, {}, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isToggling.value = null;
        },
        onError: () => {
            // Revert on error
            if (index !== -1) {
                localSubscribers.value[index].is_active = !localSubscribers.value[index].is_active;
                localSubscribers.value[index].disabled_by_admin = !localSubscribers.value[index].disabled_by_admin;
            }
        }
    });
};

// Modal handlers
const openDeleteModal = (subscriber) => {
    selectedSubscriber.value = subscriber;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showDeleteModal.value = false;
    selectedSubscriber.value = null;
};

const confirmDelete = () => {
    if (!selectedSubscriber.value) return;
    isDeleting.value = true;
    
    const subscriberId = selectedSubscriber.value.id;
    const wasActive = selectedSubscriber.value.is_active;
    
    // Optimistic update - remove from local state immediately
    const index = localSubscribers.value.findIndex(s => s.id === subscriberId);
    let removedSubscriber = null;
    if (index !== -1) {
        removedSubscriber = localSubscribers.value.splice(index, 1)[0];
        // Update stats
        localStats.value.total--;
        if (wasActive) {
            localStats.value.active--;
        } else {
            localStats.value.inactive--;
        }
    }
    
    router.delete(`/admin/newsletter/${subscriberId}`, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isDeleting.value = false;
            closeModal();
        },
        onError: () => {
            // Revert on error
            if (removedSubscriber && index !== -1) {
                localSubscribers.value.splice(index, 0, removedSubscriber);
                localStats.value.total++;
                if (wasActive) {
                    localStats.value.active++;
                } else {
                    localStats.value.inactive++;
                }
            }
        },
    });
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    if (!confirm(`Hapus ${selectedIds.value.length} subscriber yang dipilih?`)) return;
    
    isDeleting.value = true;
    const idsToDelete = [...selectedIds.value];
    
    // Optimistic update - remove selected from local state
    const removedItems = [];
    idsToDelete.forEach(id => {
        const index = localSubscribers.value.findIndex(s => s.id === id);
        if (index !== -1) {
            const item = localSubscribers.value.splice(index, 1)[0];
            removedItems.push({ item, index });
            localStats.value.total--;
            if (item.is_active) {
                localStats.value.active--;
            } else {
                localStats.value.inactive--;
            }
        }
    });
    
    router.post('/admin/newsletter/bulk-delete', {
        ids: idsToDelete,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isDeleting.value = false;
            selectedIds.value = [];
            selectAll.value = false;
        },
        onError: () => {
            // Revert on error
            removedItems.reverse().forEach(({ item, index }) => {
                localSubscribers.value.splice(index, 0, item);
                localStats.value.total++;
                if (item.is_active) {
                    localStats.value.active++;
                } else {
                    localStats.value.inactive++;
                }
            });
        },
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusInfo = (subscriber) => {
    if (subscriber.is_active) {
        return { 
            label: 'Aktif', 
            class: 'from-emerald-500 to-teal-600 text-white',
            bgClass: 'bg-emerald-50',
            icon: CheckCircle 
        };
    }
    if (subscriber.disabled_by_admin) {
        return { 
            label: 'Admin Nonaktifkan', 
            class: 'from-red-500 to-rose-600 text-white',
            bgClass: 'bg-red-50',
            icon: ShieldOff 
        };
    }
    return { 
        label: 'Tidak Aktif', 
        class: 'from-gray-500 to-gray-600 text-white',
        bgClass: 'bg-gray-50',
        icon: XCircle 
    };
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
                            <Mail class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Newsletter Subscribers</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ localStats.total }} Total
                            </span>
                        </div>
                        <p class="text-emerald-100/80 text-xs">Kelola subscriber dan pantau pertumbuhan newsletter Anda</p>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <Link 
                    href="/admin/newsletter/campaigns"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-600 text-xs font-bold hover:bg-emerald-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                >
                    <Send class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                    <span>Kirim Campaign</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                </Link>
            </div>
        </div>

        <!-- Flash Message with Animation -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <!-- Stats Cards with Premium Design -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Total Subscribers -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Users class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Subscriber</p>
                    </div>
                </div>
            </div>
            
            <!-- Active -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <UserCheck class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.active }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Subscriber Aktif</p>
                    </div>
                </div>
            </div>
            
            <!-- Inactive -->
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                        <UserX class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ localStats.inactive }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Tidak Aktif</p>
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
                        <p class="text-xl font-black text-gray-900">+{{ localStats.this_month }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Actions Card -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3 justify-between">
                <!-- Search & Filter -->
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="relative flex-1 max-w-md group">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-emerald-500 transition-colors" />
                        <input 
                            type="text" 
                            v-model="search"
                            placeholder="Cari email atau nama..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all duration-300"
                        />
                    </div>
                    <select 
                        v-model="status"
                        class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all cursor-pointer"
                    >
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button 
                        v-if="selectedIds.length > 0"
                        @click="bulkDelete"
                        :disabled="isDeleting"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all disabled:opacity-50"
                    >
                        <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
                        <Trash2 v-else class="w-4 h-4" />
                        <span>Hapus ({{ selectedIds.length }})</span>
                    </button>
                    <a 
                        :href="`/admin/newsletter/export${status ? '?status=' + status : ''}`"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all"
                    >
                        <Download class="w-4 h-4" />
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Table with Premium Styling -->
        <div ref="tableRef" class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left">
                                <input 
                                    type="checkbox" 
                                    v-model="selectAll"
                                    class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                />
                            </th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="(subscriber, index) in localSubscribers" 
                            :key="subscriber.id" 
                            class="table-row group hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-3">
                                <input 
                                    type="checkbox" 
                                    :value="subscriber.id"
                                    v-model="selectedIds"
                                    class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <a :href="`mailto:${subscriber.email}`" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:underline transition-colors">
                                    {{ subscriber.email }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-700">{{ subscriber.name || '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span 
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm',
                                        getStatusInfo(subscriber).class
                                    ]"
                                >
                                    <component :is="getStatusInfo(subscriber).icon" class="w-3 h-3" />
                                    {{ getStatusInfo(subscriber).label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                                    <Calendar class="w-3 h-3" />
                                    {{ formatDate(subscriber.subscribed_at) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Toggle Button -->
                                    <button 
                                        @click="toggleStatus(subscriber)"
                                        :disabled="isToggling === subscriber.id"
                                        :class="[
                                            'p-2 rounded-lg transition-all duration-200',
                                            subscriber.is_active 
                                                ? 'text-amber-600 hover:bg-amber-50 hover:shadow-sm' 
                                                : 'text-emerald-600 hover:bg-emerald-50 hover:shadow-sm'
                                        ]"
                                        :title="subscriber.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                    >
                                        <Loader2 v-if="isToggling === subscriber.id" class="w-4 h-4 animate-spin" />
                                        <ToggleRight v-else-if="subscriber.is_active" class="w-4 h-4" />
                                        <ToggleLeft v-else class="w-4 h-4" />
                                    </button>
                                    
                                    <!-- Delete Button -->
                                    <button 
                                        @click="openDeleteModal(subscriber)"
                                        class="p-2 rounded-lg text-red-500 hover:bg-red-50 hover:shadow-sm transition-all duration-200"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="localSubscribers.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                                        <Mail class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada subscriber</p>
                                    <p class="text-xs text-gray-400">Newsletter subscriber akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="subscribers.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ subscribers.from }}-{{ subscribers.to }}</strong> dari <strong>{{ subscribers.total }}</strong> subscriber
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in subscribers.links" 
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
                                <h3 class="text-lg font-bold text-gray-900">Hapus Subscriber</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus subscriber <strong class="text-gray-900">"{{ selectedSubscriber?.email }}"</strong>?
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
