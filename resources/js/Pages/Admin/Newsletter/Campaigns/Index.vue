<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Mail, Plus, Send, Edit, Trash2, Eye, Clock, CheckCircle, 
    XCircle, Loader2, Users, FileText, AlertCircle, AlertTriangle, 
    Sparkles, TrendingUp, ArrowLeft, Zap, MessageSquare
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    campaigns: Object,
    stats: Object,
    filters: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Local state
const status = ref(props.filters?.status || '');
const isDeleting = ref(null);
const isSending = ref(null);
const showDeleteModal = ref(false);
const showSendModal = ref(false);
const selectedCampaign = ref(null);
let ctx;

// Animation on mount
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        
        gsap.fromTo('.campaign-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.08, delay: 0.3, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

watch(status, () => {
    router.get('/admin/newsletter/campaigns', {
        status: status.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

// Modal handlers
const openDeleteModal = (campaign) => {
    selectedCampaign.value = campaign;
    showDeleteModal.value = true;
};

const openSendModal = (campaign) => {
    selectedCampaign.value = campaign;
    showSendModal.value = true;
};

const closeModals = () => {
    showDeleteModal.value = false;
    showSendModal.value = false;
    selectedCampaign.value = null;
};

const confirmDelete = () => {
    if (!selectedCampaign.value) return;
    isDeleting.value = selectedCampaign.value.id;
    router.delete(`/admin/newsletter/campaigns/${selectedCampaign.value.id}`, {
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            isDeleting.value = null;
            closeModals();
        },
    });
};

const confirmSend = () => {
    if (!selectedCampaign.value) return;
    isSending.value = selectedCampaign.value.id;
    router.post(`/admin/newsletter/campaigns/${selectedCampaign.value.id}/send`, {}, {
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            isSending.value = null;
            closeModals();
        },
    });
};

const cancelCampaign = (campaign) => {
    if (!confirm('Batalkan pengiriman campaign ini?')) return;
    router.post(`/admin/newsletter/campaigns/${campaign.id}/cancel`, {}, {
        preserveScroll: true,
        preserveState: false,
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

const getStatusBadge = (status) => {
    const badges = {
        draft: { class: 'from-gray-400 to-gray-500', bgClass: 'bg-gray-50', icon: FileText, text: 'Draft', pulse: false },
        sending: { class: 'from-blue-500 to-indigo-600', bgClass: 'bg-blue-50', icon: Loader2, text: 'Mengirim...', pulse: true },
        sent: { class: 'from-emerald-500 to-teal-600', bgClass: 'bg-emerald-50', icon: CheckCircle, text: 'Terkirim', pulse: false },
        failed: { class: 'from-red-500 to-rose-600', bgClass: 'bg-red-50', icon: XCircle, text: 'Gagal', pulse: false },
    };
    return badges[status] || badges.draft;
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-6 shadow-2xl">
            <!-- Animated Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-purple-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-purple-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <!-- Back + Icon -->
                    <Link 
                        href="/admin/newsletter"
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 hover:bg-white/30 transition-all"
                    >
                        <ArrowLeft class="h-5 w-5 text-white" />
                    </Link>
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Send class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Newsletter Campaigns</h1>
                            <Zap class="w-4 h-4 text-amber-400" />
                        </div>
                        <p class="text-purple-100/80 text-xs">Buat dan kirim newsletter ke subscriber Anda</p>
                    </div>
                </div>
                
                <Link 
                    href="/admin/newsletter/campaigns/create"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-purple-600 text-xs font-bold hover:bg-purple-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                >
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" />
                    <span>Buat Campaign</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                </Link>
            </div>
        </div>

        <!-- Flash Messages -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>
        <Transition name="slide">
            <div v-if="flash.error" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 border border-red-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-400 to-rose-500 shadow-lg">
                    <AlertCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-red-800">{{ flash.error }}</p>
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <MessageSquare class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Campaign</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-gray-100 to-slate-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-gray-500 to-slate-600 shadow-lg shadow-gray-500/30 group-hover:scale-110 transition-transform">
                        <FileText class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.draft }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Draft</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.sent }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Terkirim</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Users class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.activeSubscribers }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Subscriber</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col sm:flex-row gap-3 justify-between items-center">
                <select 
                    v-model="status"
                    class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 focus:bg-white transition-all cursor-pointer"
                >
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="sending">Mengirim</option>
                    <option value="sent">Terkirim</option>
                    <option value="failed">Gagal</option>
                </select>
                <Link 
                    href="/admin/newsletter"
                    class="text-purple-600 hover:text-purple-700 text-xs font-medium flex items-center gap-1.5 hover:underline"
                >
                    <Mail class="w-3.5 h-3.5" />
                    Kelola Subscriber
                </Link>
            </div>
        </div>

        <!-- Campaigns Table -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Statistik</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Dibuat</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr 
                            v-for="campaign in campaigns.data" 
                            :key="campaign.id" 
                            class="campaign-row group hover:bg-gradient-to-r hover:from-purple-50/50 hover:to-indigo-50/30 transition-all duration-200"
                        >
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">{{ campaign.subject }}</p>
                                    <p v-if="campaign.creator" class="text-[10px] text-gray-500 mt-0.5">
                                        oleh {{ campaign.creator.name }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span 
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold text-white bg-gradient-to-r shadow-sm',
                                        getStatusBadge(campaign.status).class
                                    ]"
                                >
                                    <component 
                                        :is="getStatusBadge(campaign.status).icon" 
                                        class="w-3 h-3"
                                        :class="{ 'animate-spin': getStatusBadge(campaign.status).pulse }"
                                    />
                                    {{ getStatusBadge(campaign.status).text }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="campaign.status !== 'draft'" class="text-[10px]">
                                    <p class="text-gray-900 font-medium">{{ campaign.sent_count }} / {{ campaign.total_recipients }}</p>
                                    <p class="text-gray-500">Terkirim | <span class="text-red-500">Gagal: {{ campaign.failed_count }}</span></p>
                                </div>
                                <span v-else class="text-gray-400 text-[10px]">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                                    <Clock class="w-3 h-3" />
                                    {{ formatDate(campaign.created_at) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Preview -->
                                    <a 
                                        :href="`/admin/newsletter/campaigns/${campaign.id}/preview`"
                                        target="_blank"
                                        class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-all"
                                        title="Preview"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </a>
                                    
                                    <!-- Edit (draft only) -->
                                    <Link 
                                        v-if="campaign.status === 'draft'"
                                        :href="`/admin/newsletter/campaigns/${campaign.id}/edit`"
                                        class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-all"
                                        title="Edit"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    
                                    <!-- Send (draft or failed) -->
                                    <button 
                                        v-if="campaign.status === 'draft' || campaign.status === 'failed'"
                                        @click="openSendModal(campaign)"
                                        class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-all"
                                        title="Kirim"
                                    >
                                        <Send class="w-4 h-4" />
                                    </button>
                                    
                                    <!-- Cancel (sending only) -->
                                    <button 
                                        v-if="campaign.status === 'sending'"
                                        @click="cancelCampaign(campaign)"
                                        class="p-2 rounded-lg text-orange-600 hover:bg-orange-50 transition-all"
                                        title="Batalkan"
                                    >
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                    
                                    <!-- Delete (draft only) -->
                                    <button 
                                        v-if="campaign.status === 'draft'"
                                        @click="openDeleteModal(campaign)"
                                        :disabled="isDeleting === campaign.id"
                                        class="p-2 rounded-lg text-red-500 hover:bg-red-50 transition-all disabled:opacity-50"
                                        title="Hapus"
                                    >
                                        <Loader2 v-if="isDeleting === campaign.id" class="w-4 h-4 animate-spin" />
                                        <Trash2 v-else class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Empty State -->
                        <tr v-if="campaigns.data.length === 0">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center mb-4">
                                        <Send class="w-8 h-8 text-purple-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Belum ada campaign</p>
                                    <p class="text-xs text-gray-400 mb-4">Buat campaign pertama Anda</p>
                                    <Link 
                                        href="/admin/newsletter/campaigns/create"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-all"
                                    >
                                        <Plus class="w-4 h-4" />
                                        Buat Campaign
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="campaigns.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ campaigns.from }}-{{ campaigns.to }}</strong> dari <strong>{{ campaigns.total }}</strong> campaign
                </p>
                <div class="flex gap-1">
                    <Link 
                        v-for="link in campaigns.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                            link.active 
                                ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-md' 
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
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Campaign</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Hapus campaign <strong>"{{ selectedCampaign?.subject }}"</strong>?
                        </p>
                        
                        <div class="flex gap-3 justify-end">
                            <button 
                                @click="closeModals"
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

        <!-- Send Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showSendModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100">
                                <Send class="w-6 h-6 text-emerald-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Kirim Campaign</h3>
                                <p class="text-xs text-gray-500">Konfirmasi pengiriman newsletter</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-4">
                            Kirim <strong>"{{ selectedCampaign?.subject }}"</strong> ke <strong class="text-emerald-600">{{ stats.activeSubscribers }}</strong> subscriber aktif?
                        </p>
                        
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/60 rounded-xl p-4 mb-6">
                            <p class="text-amber-800 text-xs">
                                <strong>Catatan:</strong> Pastikan konten sudah benar. Email akan segera dikirim.
                            </p>
                        </div>
                        
                        <div class="flex gap-3 justify-end">
                            <button 
                                @click="closeModals"
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                            >
                                Batal
                            </button>
                            <button 
                                @click="confirmSend"
                                :disabled="isSending"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"
                            >
                                <Loader2 v-if="isSending" class="w-4 h-4 animate-spin" />
                                <Send v-else class="w-4 h-4" />
                                Kirim Sekarang
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
