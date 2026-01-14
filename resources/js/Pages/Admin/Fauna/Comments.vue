<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { MessageCircle, Plus, Search, Trash2, Eye, EyeOff, Pin, Reply, ArrowLeft, Filter, Sparkles, AlertTriangle, Loader2, Bird } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    comments: Object,
    faunaList: Array,
    filters: Object
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.stat-card', { opacity: 0, y: 20, scale: 0.95 }, { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'back.out(1.7)' });
        gsap.fromTo('.comment-item', { opacity: 0, x: -20 }, { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.4, ease: 'power2.out' });
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const selectedIds = ref([]);
const showDeleteModal = ref(false);
const showAddModal = ref(false);
const deleteType = ref('');
const deleteId = ref(null);
const replyToId = ref(null);
const isSubmitting = ref(false);
const searchQuery = ref(props.filters?.search || '');
const filterFauna = ref(props.filters?.fauna_id || '');

const newComment = ref({ fauna_id: '', content: '' });

const stats = computed(() => {
    const data = props.comments?.data || [];
    return {
        total: data.length,
        visible: data.filter(c => c.is_visible).length,
        hidden: data.filter(c => !c.is_visible).length,
        pinned: data.filter(c => c.is_pinned).length
    };
});

const search = () => {
    router.get('/admin/fauna/comments', { search: searchQuery.value, fauna_id: filterFauna.value }, { preserveState: true });
};

const openAddModal = () => { replyToId.value = null; newComment.value = { fauna_id: '', content: '' }; showAddModal.value = true; };
const openReplyModal = (commentId, faunaId, username) => { replyToId.value = commentId; newComment.value = { fauna_id: faunaId, content: `@${username || 'user'} ` }; showAddModal.value = true; };

const submitComment = async () => {
    if (!newComment.value.content.trim()) return alert('Komentar tidak boleh kosong');
    if (!replyToId.value && !newComment.value.fauna_id) return alert('Pilih fauna terlebih dahulu');
    isSubmitting.value = true;
    try {
        const url = replyToId.value ? `/admin/fauna/comments/${replyToId.value}/reply` : '/admin/fauna/comments';
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify(newComment.value)
        });
        const data = await response.json();
        if (data.success) { showAddModal.value = false; router.reload(); } else { alert(data.message || 'Gagal'); }
    } catch (e) { alert('Terjadi kesalahan'); }
    finally { isSubmitting.value = false; }
};

const promptDelete = (id) => { deleteType.value = 'single'; deleteId.value = id; showDeleteModal.value = true; };
const promptDeleteSelected = () => { if (!selectedIds.value.length) return; deleteType.value = 'batch'; showDeleteModal.value = true; };

const confirmDelete = async () => {
    try {
        if (deleteType.value === 'single') {
            await fetch(`/admin/fauna/comments/${deleteId.value}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
        } else {
            await fetch('/admin/fauna/comments/batch-delete', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ ids: selectedIds.value }) });
        }
        showDeleteModal.value = false; router.reload();
    } catch (e) { alert('Error'); }
};

const toggleStatus = async (id) => { await fetch(`/admin/fauna/comments/${id}/toggle-status`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); router.reload(); };
const togglePin = async (id) => { await fetch(`/admin/fauna/comments/${id}/toggle-pin`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); router.reload(); };
const toggleReplyStatus = async (replyId) => { try { await fetch(`/admin/fauna/comments/${replyId}/toggle-status`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); router.reload(); } catch (e) { console.error(e); } };
const deleteReply = async (replyId) => { if (!confirm('Hapus balasan ini?')) return; try { await fetch(`/admin/fauna/comments/${replyId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); router.reload(); } catch (e) { console.error(e); } };

const formatDate = (date) => {
    if (!date) return '-';
    try { return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); } catch (e) { return '-'; }
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-amber-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="header-content relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <MessageCircle class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Komentar Fauna</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">{{ stats.total }} Total</span>
                        </div>
                        <p class="text-amber-100/80 text-xs">Kelola dan moderasi komentar fauna</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link href="/admin/fauna" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 text-white text-xs font-semibold backdrop-blur-sm border border-white/20 hover:bg-white/20 transition-all"><ArrowLeft class="w-4 h-4" /> Kembali</Link>
                    <button v-if="selectedIds.length" @click="promptDeleteSelected" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-500/80 text-white text-xs font-semibold hover:bg-red-600 transition-all"><Trash2 class="w-4 h-4" /> Hapus ({{ selectedIds.length }})</button>
                    <button @click="openAddModal" class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-amber-600 text-xs font-bold hover:bg-amber-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg">
                        <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300" /><span>Tambah Komentar</span><Sparkles class="w-3.5 h-3.5 text-amber-500" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);"><div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div><p class="text-2xl font-black text-white">{{ stats.total }}</p><p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Total Komentar</p></div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);"><div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div><p class="text-2xl font-black text-white">{{ stats.visible }}</p><p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Ditampilkan</p></div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"><div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div><p class="text-2xl font-black text-white">{{ stats.hidden }}</p><p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Disembunyikan</p></div>
            <div class="stat-card group relative overflow-hidden rounded-xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300" style="background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);"><div class="absolute -top-10 -right-10 w-24 h-24 rounded-full blur-2xl" style="background: rgba(255,255,255,0.1);"></div><p class="text-2xl font-black text-white">{{ stats.pinned }}</p><p class="text-[10px] font-medium" style="color: rgba(255,255,255,0.8);">Disematkan</p></div>
        </div>

        <!-- Filters -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row gap-3">
                <div class="relative flex-1 group"><Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-amber-500 transition-colors" /><input v-model="searchQuery" @keyup.enter="search" type="text" placeholder="Cari komentar..." class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:bg-white transition-all duration-300"></div>
                <select v-model="filterFauna" @change="search" class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all cursor-pointer"><option value="">Semua Fauna</option><option v-for="f in faunaList" :key="f.id" :value="f.id">{{ f.name }}</option></select>
                <button @click="search" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-semibold hover:shadow-lg transition-all"><Filter class="w-4 h-4" /> Filter</button>
            </div>
        </div>

        <!-- Comments List -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div v-if="comments?.data?.length" class="divide-y divide-gray-100">
                <div v-for="comment in comments.data" :key="comment.id" :class="['comment-item p-4 hover:bg-gradient-to-r hover:from-amber-50/50 hover:to-orange-50/30 transition-all', comment.is_pinned ? 'bg-amber-50/30' : '']">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" :value="comment.id" v-model="selectedIds" class="mt-1 w-4 h-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-md">{{ (comment.user?.name || comment.admin?.name || 'A').charAt(0).toUpperCase() }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-gray-900">{{ comment.user?.name || comment.admin?.name || 'Admin' }}</span>
                                <span v-if="comment.is_pinned" class="inline-flex items-center gap-1 px-2 py-0.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-[10px] font-bold rounded-full shadow-sm"><Pin class="w-2.5 h-2.5" /> Disematkan</span>
                                <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full', comment.is_visible ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600']">{{ comment.is_visible ? 'Ditampilkan' : 'Disembunyikan' }}</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1.5">{{ comment.content }}</p>
                            <div class="flex items-center gap-3 mt-2 text-[10px] text-gray-400"><span>{{ formatDate(comment.created_at) }}</span><span v-if="comment.fauna">di <strong class="text-gray-600">{{ comment.fauna.name }}</strong></span></div>
                            <!-- Replies -->
                            <div v-if="comment.replies?.length" class="mt-4 space-y-3 pl-4 border-l-2 border-amber-200">
                                <div v-for="reply in comment.replies" :key="reply.id" class="flex items-start gap-3 group/reply p-2 rounded-lg hover:bg-gray-50 transition-all">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-[10px] flex-shrink-0">{{ (reply.user?.name || reply.admin?.name || 'A').charAt(0).toUpperCase() }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2"><span class="text-[11px] font-semibold text-gray-800">{{ reply.user?.name || reply.admin?.name || 'Admin' }}</span><span v-if="reply.is_approved === false" class="px-1.5 py-0.5 bg-amber-100 text-amber-600 text-[9px] font-bold rounded-full">Tersembunyi</span></div>
                                        <p class="text-[11px] text-gray-600">{{ reply.content }}</p><p class="text-[9px] text-gray-400 mt-0.5">{{ formatDate(reply.created_at) }}</p>
                                    </div>
                                    <div class="flex items-center gap-0.5 opacity-0 group-hover/reply:opacity-100 transition-opacity flex-shrink-0">
                                        <button @click="openReplyModal(comment.id, comment.fauna_id, reply.user?.name || reply.admin?.name || 'user')" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Balas"><Reply class="w-3 h-3" /></button>
                                        <button @click="toggleReplyStatus(reply.id)" :class="reply.is_approved ? 'text-emerald-500 hover:text-amber-600 hover:bg-amber-50' : 'text-amber-500 hover:text-emerald-600 hover:bg-emerald-50'" class="p-1.5 rounded-lg transition-all"><component :is="reply.is_approved ? EyeOff : Eye" class="w-3 h-3" /></button>
                                        <button @click="deleteReply(reply.id)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all"><Trash2 class="w-3 h-3" /></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button @click="openReplyModal(comment.id, comment.fauna_id, comment.user?.name || comment.admin?.name || 'user')" class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Balas"><Reply class="w-4 h-4" /></button>
                            <button @click="toggleStatus(comment.id)" :class="comment.is_visible ? 'text-emerald-500 hover:text-amber-600 hover:bg-amber-50' : 'text-amber-500 hover:text-emerald-600 hover:bg-emerald-50'" class="p-2 rounded-lg transition-all"><component :is="comment.is_visible ? EyeOff : Eye" class="w-4 h-4" /></button>
                            <button @click="togglePin(comment.id)" :class="comment.is_pinned ? 'text-amber-500 bg-amber-50' : 'text-gray-400 hover:text-amber-600 hover:bg-amber-50'" class="p-2 rounded-lg transition-all"><Pin class="w-4 h-4" /></button>
                            <button @click="promptDelete(comment.id)" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all"><Trash2 class="w-4 h-4" /></button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-16"><div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4"><MessageCircle class="w-8 h-8 text-amber-400" /></div><h3 class="text-sm font-bold text-gray-900 mb-1">Belum ada komentar</h3><p class="text-xs text-gray-400">Komentar dari pengunjung akan muncul di sini</p></div>
            <div v-if="comments?.links" class="px-5 py-4 border-t border-gray-100 flex justify-center gap-1 bg-gray-50/50"><Link v-for="link in comments.links" :key="link.label" :href="link.url || '#'" :class="['px-3 py-1.5 rounded-lg text-[11px] font-medium transition-all', link.active ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']" v-html="link.label" /></div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body"><Transition name="modal"><div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"><div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div><div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6"><div class="flex items-center gap-4 mb-5"><div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100"><AlertTriangle class="w-6 h-6 text-red-600" /></div><div><h3 class="text-lg font-bold text-gray-900">Hapus Komentar</h3><p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p></div></div><p class="text-sm text-gray-600 mb-6">{{ deleteType === 'single' ? 'Komentar ini akan dihapus permanen.' : `${selectedIds.length} komentar akan dihapus permanen.` }}</p><div class="flex gap-3 justify-end"><button @click="showDeleteModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all">Batal</button><button @click="confirmDelete" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all flex items-center gap-2"><Trash2 class="w-4 h-4" /> Hapus</button></div></div></div></Transition></Teleport>

        <!-- Add Comment Modal -->
        <Teleport to="body"><Transition name="modal"><div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"><div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showAddModal = false"></div><div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6"><h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><MessageCircle class="w-5 h-5 text-amber-600" />{{ replyToId ? 'Balas Komentar' : 'Tambah Komentar' }}</h3><div v-if="!replyToId" class="mb-4"><label class="block text-xs font-medium text-gray-700 mb-2">Fauna</label><select v-model="newComment.fauna_id" class="w-full px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10"><option value="">Pilih Fauna...</option><option v-for="f in faunaList" :key="f.id" :value="f.id">{{ f.name }}</option></select></div><div class="mb-6"><label class="block text-xs font-medium text-gray-700 mb-2">Komentar</label><textarea v-model="newComment.content" rows="4" placeholder="Tulis komentar..." class="w-full px-4 py-3 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 resize-none"></textarea></div><div class="flex gap-3 justify-end"><button @click="showAddModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all">Batal</button><button @click="submitComment" :disabled="isSubmitting" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2"><Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />{{ isSubmitting ? 'Mengirim...' : 'Kirim' }}</button></div></div></div></Transition></Teleport>
    </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
