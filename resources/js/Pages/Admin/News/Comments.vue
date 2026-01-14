<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { MessageCircle, Plus, Search, Trash2, Eye, EyeOff, Pin, Reply, ArrowLeft, Filter, Sparkles } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

let ctx;
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.stat-card', { opacity: 0, y: 20, scale: 0.95 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'back.out(1.7)' });
        gsap.fromTo('.comment-item', { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.4, ease: 'power2.out' });
    });
});
onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const props = defineProps({
    comments: Object,
    articleList: Array,
    filters: Object
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// State
const selectedIds = ref([]);
const showDeleteModal = ref(false);
const showAddModal = ref(false);
const deleteType = ref('');
const deleteId = ref(null);
const replyToId = ref(null);
const isSubmitting = ref(false);
const searchQuery = ref(props.filters?.search || '');
const filterArticle = ref(props.filters?.article_id || '');

const newComment = ref({
    article_id: '',
    content: ''
});

// Computed
const stats = computed(() => {
    const data = props.comments?.data || [];
    return {
        total: data.length,
        visible: data.filter(c => c.is_visible).length,
        hidden: data.filter(c => !c.is_visible).length,
        pinned: data.filter(c => c.is_pinned).length
    };
});

// Methods
const search = () => {
    router.get('/admin/news/comments', { search: searchQuery.value, article_id: filterArticle.value }, { preserveState: true });
};

const openAddModal = () => {
    replyToId.value = null;
    newComment.value = { article_id: '', content: '' };
    showAddModal.value = true;
};

const openReplyModal = (commentId, articleId, username) => {
    replyToId.value = commentId;
    newComment.value = { article_id: articleId, content: `@${username || 'user'} ` };
    showAddModal.value = true;
};

const submitComment = async () => {
    if (!newComment.value.content.trim()) return alert('Komentar tidak boleh kosong');
    if (!replyToId.value && !newComment.value.article_id) return alert('Pilih berita terlebih dahulu');
    
    isSubmitting.value = true;
    try {
        const url = replyToId.value 
            ? `/admin/news/comments/${replyToId.value}/reply`
            : '/admin/news/comments';
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(newComment.value)
        });
        const data = await response.json();
        if (data.success) {
            showAddModal.value = false;
            router.reload();
        } else {
            alert(data.message || 'Gagal');
        }
    } catch (e) {
        alert('Terjadi kesalahan');
    } finally {
        isSubmitting.value = false;
    }
};

const promptDelete = (id) => {
    deleteType.value = 'single';
    deleteId.value = id;
    showDeleteModal.value = true;
};

const promptDeleteSelected = () => {
    if (!selectedIds.value.length) return;
    deleteType.value = 'batch';
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    try {
        if (deleteType.value === 'single') {
            await fetch(`/admin/news/comments/${deleteId.value}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
        } else {
            await fetch('/admin/news/comments/batch-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids: selectedIds.value })
            });
        }
        showDeleteModal.value = false;
        router.reload();
    } catch (e) {
        alert('Error');
    }
};

const toggleStatus = async (id) => {
    await fetch(`/admin/news/comments/${id}/toggle-status`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    router.reload();
};

const togglePin = async (id) => {
    await fetch(`/admin/news/comments/${id}/toggle-pin`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    router.reload();
};

// Reply actions
const toggleReplyStatus = async (replyId) => {
    try {
        await fetch(`/admin/news/comments/${replyId}/toggle-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        router.reload();
    } catch (e) { console.error('Failed to toggle reply status:', e); }
};

const deleteReply = async (replyId) => {
    if (!confirm('Hapus balasan ini?')) return;
    try {
        await fetch(`/admin/news/comments/${replyId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        router.reload();
    } catch (e) { console.error('Failed to delete reply:', e); }
};

const formatDate = (date) => {
    if (!date) return '-';
    try {
        return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    } catch (e) {
        return '-';
    }
};
</script>

<template>
    <div class="space-y-5">
        <!-- Premium Header - Compact -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 p-4 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-16 -right-16 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="absolute top-4 left-16 w-1.5 h-1.5 bg-white/30 rounded-full animate-bounce"></div>
            
            <div class="header-content relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-lg blur-md"></div>
                        <div class="relative flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur-xl ring-1 ring-white/30 shadow-lg">
                            <MessageCircle class="h-5 w-5 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-bold text-white tracking-tight">Komentar Berita</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ stats.total }} Total
                            </span>
                        </div>
                        <p class="text-red-100/80 text-[10px]">Kelola dan moderasi komentar berita</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <Link href="/admin/news" class="px-3 py-2 text-[11px] font-bold text-white bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/30 transition-all flex items-center gap-1.5">
                        <ArrowLeft class="w-3.5 h-3.5" /> Kembali
                    </Link>
                    <button v-if="selectedIds.length" @click="promptDeleteSelected"
                        class="px-3 py-2 text-[11px] font-bold text-red-600 bg-white rounded-lg hover:bg-red-50 transition-all flex items-center gap-1.5 shadow-md">
                        <Trash2 class="w-3.5 h-3.5" /> Hapus ({{ selectedIds.length }})
                    </button>
                    <button @click="openAddModal"
                        class="group inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white text-red-600 text-[11px] font-bold hover:bg-red-50 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 shadow-md">
                        <Plus class="w-3.5 h-3.5 group-hover:rotate-90 transition-transform duration-300" />
                        <span>Tambah</span>
                        <Sparkles class="w-3 h-3 text-amber-500" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards - Using inline styles for guaranteed visibility -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #ef4444 0%, #f43f5e 100%);">
                <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div>
                <p class="text-2xl font-black text-white">{{ stats.total }}</p>
                <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Total</p>
            </div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);">
                <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div>
                <p class="text-2xl font-black text-white">{{ stats.visible }}</p>
                <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Ditampilkan</p>
            </div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
                <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div>
                <p class="text-2xl font-black text-white">{{ stats.hidden }}</p>
                <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Disembunyikan</p>
            </div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);">
                <div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div>
                <p class="text-2xl font-black text-white">{{ stats.pinned }}</p>
                <p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Disematkan</p>
            </div>
        </div>

        <!-- Filters Card - Matches Articles/Comments -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3">
                <div class="relative flex-1 group">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-red-500 transition-colors" />
                    <input v-model="searchQuery" @keyup.enter="search" type="text" placeholder="Cari komentar..."
                        class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-red-500 focus:ring-4 focus:ring-red-500/10 focus:bg-white transition-all duration-300">
                </div>
                <select v-model="filterArticle" @change="search"
                    class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all cursor-pointer">
                    <option value="">Semua Berita</option>
                    <option v-for="a in articleList" :key="a.id" :value="a.id">{{ a.title }}</option>
                </select>
                <button @click="search" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all">
                    <Filter class="w-4 h-4" /> Filter
                </button>
            </div>
        </div>

        <!-- Comments List - Matches Articles/Comments -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div v-if="comments?.data?.length" class="divide-y divide-gray-100">
                <div v-for="comment in comments.data" :key="comment.id"
                    :class="['comment-item p-4 hover:bg-gradient-to-r hover:from-red-50/50 hover:to-rose-50/30 transition-all', comment.is_pinned ? 'bg-red-50/30' : '']">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" :value="comment.id" v-model="selectedIds"
                            class="mt-1 w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-500">
                        
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-md">
                            {{ (comment.user?.name || comment.admin?.name || 'A').charAt(0).toUpperCase() }}
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-gray-900">{{ comment.user?.name || comment.admin?.name || 'Admin' }}</span>
                                <span v-if="comment.is_pinned" class="inline-flex items-center gap-1 px-2 py-0.5 bg-gradient-to-r from-red-500 to-rose-600 text-white text-[10px] font-bold rounded-full shadow-sm">
                                    <Pin class="w-2.5 h-2.5" /> Disematkan
                                </span>
                                <span :class="[
                                    'px-2 py-0.5 text-[10px] font-bold rounded-full',
                                    comment.is_visible ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'
                                ]">
                                    {{ comment.is_visible ? 'Ditampilkan' : 'Disembunyikan' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1.5">{{ comment.content }}</p>
                            <div class="flex items-center gap-3 mt-2 text-[10px] text-gray-400">
                                <span>{{ formatDate(comment.created_at) }}</span>
                                <span v-if="comment.article">di <strong class="text-gray-600">{{ comment.article.title }}</strong></span>
                            </div>
                            
                            <!-- Replies with Action Buttons -->
                            <div v-if="comment.replies?.length" class="mt-4 space-y-3 pl-4 border-l-2 border-red-200">
                                <div v-for="reply in comment.replies" :key="reply.id" class="flex items-start gap-3 group/reply p-2 rounded-lg hover:bg-gray-50 transition-all">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-[10px] flex-shrink-0">
                                        {{ (reply.user?.name || reply.admin?.name || 'A').charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-semibold text-gray-800">{{ reply.user?.name || reply.admin?.name || 'Admin' }}</span>
                                            <span v-if="reply.is_visible === false" class="px-1.5 py-0.5 bg-amber-100 text-amber-600 text-[9px] font-bold rounded-full">Tersembunyi</span>
                                        </div>
                                        <p class="text-[11px] text-gray-600">{{ reply.content }}</p>
                                        <p class="text-[9px] text-gray-400 mt-0.5">{{ formatDate(reply.created_at) }}</p>
                                    </div>
                                    <!-- Reply Actions -->
                                    <div class="flex items-center gap-0.5 opacity-0 group-hover/reply:opacity-100 transition-opacity flex-shrink-0">
                                        <button @click="openReplyModal(comment.id, comment.article_id, reply.user?.name || reply.admin?.name || 'user')"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Balas">
                                            <Reply class="w-3 h-3" />
                                        </button>
                                        <button @click="toggleReplyStatus(reply.id)" 
                                            :class="reply.is_visible ? 'text-emerald-500 hover:text-amber-600 hover:bg-amber-50' : 'text-amber-500 hover:text-emerald-600 hover:bg-emerald-50'"
                                            class="p-1.5 rounded-lg transition-all" 
                                            :title="reply.is_visible ? 'Sembunyikan' : 'Tampilkan'">
                                            <component :is="reply.is_visible ? EyeOff : Eye" class="w-3 h-3" />
                                        </button>
                                        <button @click="deleteReply(reply.id)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                            <Trash2 class="w-3 h-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions - Matches Articles/Comments sizing -->
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button @click="openReplyModal(comment.id, comment.article_id, comment.user?.name || comment.admin?.name || 'user')" 
                                class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Balas">
                                <Reply class="w-4 h-4" />
                            </button>
                            <button @click="toggleStatus(comment.id)"
                                :class="comment.is_visible ? 'text-emerald-500 hover:text-amber-600 hover:bg-amber-50' : 'text-amber-500 hover:text-emerald-600 hover:bg-emerald-50'"
                                class="p-2 rounded-lg transition-all" :title="comment.is_visible ? 'Sembunyikan' : 'Tampilkan'">
                                <component :is="comment.is_visible ? Eye : EyeOff" class="w-4 h-4" />
                            </button>
                            <button @click="togglePin(comment.id)"
                                :class="comment.is_pinned ? 'text-red-500 bg-red-50' : 'text-gray-400 hover:text-red-600 hover:bg-red-50'"
                                class="p-2 rounded-lg transition-all" title="Pin">
                                <Pin class="w-4 h-4" />
                            </button>
                            <button @click="promptDelete(comment.id)"
                                class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State - Matches Articles/Comments -->
            <div v-else class="text-center py-16">
                <div class="w-16 h-16 bg-gradient-to-br from-red-100 to-rose-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <MessageCircle class="w-8 h-8 text-red-400" />
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Belum ada komentar</h3>
                <p class="text-xs text-gray-400">Komentar dari pengunjung akan muncul di sini</p>
            </div>

            <!-- Pagination - Matches Articles/Comments -->
            <div v-if="comments?.links" class="px-5 py-4 border-t border-gray-100 flex justify-center gap-1 bg-gray-50/50">
                <Link v-for="link in comments.links" :key="link.label" :href="link.url || '#'"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all',
                        link.active ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    v-html="link.label" />
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <Trash2 class="w-8 h-8 text-red-500" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Hapus Komentar?</h3>
                        <p class="text-gray-500 text-center mb-6">
                            {{ deleteType === 'single' ? 'Komentar ini akan dihapus permanen.' : `${selectedIds.length} komentar akan dihapus permanen.` }}
                        </p>
                        <div class="flex gap-3">
                            <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all">Batal</button>
                            <button @click="confirmDelete" class="flex-1 px-4 py-2.5 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition-all">Hapus</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Add Comment Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">{{ replyToId ? 'Balas Komentar' : 'Tambah Komentar' }}</h3>
                        
                        <div v-if="!replyToId" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Berita</label>
                            <select v-model="newComment.article_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                <option value="">Pilih Berita...</option>
                                <option v-for="a in articleList" :key="a.id" :value="a.id">{{ a.title }}</option>
                            </select>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                            <textarea v-model="newComment.content" rows="4" placeholder="Tulis komentar..."
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 resize-none"></textarea>
                        </div>
                        
                        <div class="flex gap-3">
                            <button @click="showAddModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all">Batal</button>
                            <button @click="submitComment" :disabled="isSubmitting"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-medium hover:from-red-600 hover:to-rose-700 transition-all disabled:opacity-50">
                                {{ isSubmitting ? 'Mengirim...' : 'Kirim' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
