<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MessageCircle, Plus, Search, Trash2, Check, X, Pin, Reply, ArrowLeft, Filter } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    comments: Object,
    destinationList: Array,
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
const filterDestination = ref(props.filters?.destination_id || '');

const newComment = ref({
    destination_id: '',
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
    router.get('/admin/destinations/comments', { search: searchQuery.value, destination_id: filterDestination.value }, { preserveState: true });
};

const openAddModal = () => {
    replyToId.value = null;
    newComment.value = { destination_id: '', content: '' };
    showAddModal.value = true;
};

const openReplyModal = (commentId, destinationId) => {
    replyToId.value = commentId;
    newComment.value = { destination_id: destinationId, content: '' };
    showAddModal.value = true;
};

const submitComment = async () => {
    if (!newComment.value.content.trim()) return alert('Komentar tidak boleh kosong');
    if (!replyToId.value && !newComment.value.destination_id) return alert('Pilih destinasi terlebih dahulu');
    
    isSubmitting.value = true;
    try {
        const url = replyToId.value 
            ? `/admin/destinations/comments/${replyToId.value}/reply`
            : '/admin/destinations/comments';
        
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
            await fetch(`/admin/destinations/comments/${deleteId.value}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
        } else {
            await fetch('/admin/destinations/comments/batch-delete', {
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
    await fetch(`/admin/destinations/comments/${id}/toggle-status`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    router.reload();
};

const togglePin = async (id) => {
    await fetch(`/admin/destinations/comments/${id}/toggle-pin`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    router.reload();
};

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 via-green-500 to-teal-500 rounded-xl flex items-center justify-center shadow-xl shadow-emerald-500/30">
                    <MessageCircle class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Komentar <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-green-600">Destinasi</span></h1>
                    <p class="text-gray-500 mt-1">Kelola dan moderasi komentar</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link href="/admin/destinations" class="px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" /> Kembali
                </Link>
                <button v-if="selectedIds.length" @click="promptDeleteSelected"
                    class="px-4 py-2.5 text-sm font-bold text-red-600 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition-all flex items-center gap-2">
                    <Trash2 class="w-4 h-4" /> Hapus ({{ selectedIds.length }})
                </button>
                <button @click="openAddModal"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl hover:from-emerald-600 hover:to-green-700 shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
                    <Plus class="w-4 h-4" /> Tambah Komentar
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="text-2xl font-black text-gray-900">{{ stats.total }}</div>
                <div class="text-[11px] text-gray-500">Total</div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="text-2xl font-black text-green-600">{{ stats.visible }}</div>
                <div class="text-[11px] text-gray-500">Ditampilkan</div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="text-2xl font-black text-yellow-600">{{ stats.hidden }}</div>
                <div class="text-[11px] text-gray-500">Disembunyikan</div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <div class="text-2xl font-black text-emerald-600">{{ stats.pinned }}</div>
                <div class="text-[11px] text-gray-500">Disematkan</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="searchQuery" @keyup.enter="search" type="text" placeholder="Cari komentar..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>
            </div>
            <select v-model="filterDestination" @change="search"
                class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                <option value="">Semua Destinasi</option>
                <option v-for="d in destinationList" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <button @click="search" class="px-4 py-2.5 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-all flex items-center gap-2">
                <Filter class="w-4 h-4" /> Filter
            </button>
        </div>

        <!-- Comments List -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div v-if="comments?.data?.length" class="divide-y divide-gray-100">
                <div v-for="comment in comments.data" :key="comment.id"
                    :class="['p-5 hover:bg-gray-50/50 transition-all', comment.is_pinned ? 'bg-emerald-50/30' : '']">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" :value="comment.id" v-model="selectedIds"
                            class="mt-1 w-5 h-5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                        
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ (comment.user?.name || comment.admin?.name || 'A').charAt(0).toUpperCase() }}
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-gray-900">{{ comment.user?.name || comment.admin?.name || 'Admin' }}</span>
                                <span v-if="comment.is_pinned" class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-xs font-bold rounded-full flex items-center gap-1">
                                    <Pin class="w-3 h-3" /> Disematkan
                                </span>
                                <span :class="comment.is_visible ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600'"
                                    class="px-2 py-0.5 text-xs font-bold rounded-full">
                                    {{ comment.is_visible ? 'Ditampilkan' : 'Disembunyikan' }}
                                </span>
                            </div>
                            <p class="text-gray-600 mt-1">{{ comment.content }}</p>
                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                <span>{{ formatDate(comment.created_at) }}</span>
                                <span v-if="comment.destination">di <strong class="text-gray-600">{{ comment.destination.name }}</strong></span>
                            </div>
                            
                            <!-- Replies -->
                            <div v-if="comment.replies?.length" class="mt-4 space-y-3 pl-4 border-l-2 border-emerald-200">
                                <div v-for="reply in comment.replies" :key="reply.id" class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-xs">
                                        {{ (reply.user?.name || reply.admin?.name || 'A').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-800 text-sm">{{ reply.user?.name || reply.admin?.name || 'Admin' }}</span>
                                        <p class="text-gray-600 text-sm">{{ reply.content }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button @click="openReplyModal(comment.id, comment.destination_id)" 
                                class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Balas">
                                <Reply class="w-5 h-5" />
                            </button>
                            <button @click="toggleStatus(comment.id)"
                                :class="comment.is_visible ? 'hover:text-yellow-600 hover:bg-yellow-50' : 'hover:text-green-600 hover:bg-green-50'"
                                class="p-2 rounded-lg text-gray-400 transition-all" :title="comment.is_visible ? 'Sembunyikan' : 'Tampilkan'">
                                <component :is="comment.is_visible ? X : Check" class="w-5 h-5" />
                            </button>
                            <button @click="togglePin(comment.id)"
                                :class="comment.is_pinned ? 'text-emerald-500 bg-emerald-50' : 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50'"
                                class="p-2 rounded-lg transition-all" title="Pin">
                                <Pin class="w-5 h-5" />
                            </button>
                            <button @click="promptDelete(comment.id)"
                                class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Hapus">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div class="w-20 h-20 bg-gradient-to-br from-emerald-100 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <MessageCircle class="w-10 h-10 text-emerald-400" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum ada komentar</h3>
                <p class="text-gray-500">Komentar dari pengunjung akan muncul di sini</p>
            </div>

            <!-- Pagination -->
            <div v-if="comments?.links" class="px-6 py-4 border-t border-gray-100 flex justify-center gap-2">
                <Link v-for="link in comments.links" :key="link.label" :href="link.url || '#'"
                    :class="['px-3 py-1 rounded-lg text-sm font-medium transition-all', link.active ? 'bg-emerald-500 text-white' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Destinasi</label>
                            <select v-model="newComment.destination_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">Pilih Destinasi...</option>
                                <option v-for="d in destinationList" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                            <textarea v-model="newComment.content" rows="4" placeholder="Tulis komentar..."
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 resize-none"></textarea>
                        </div>
                        
                        <div class="flex gap-3">
                            <button @click="showAddModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all">Batal</button>
                            <button @click="submitComment" :disabled="isSubmitting"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-green-700 transition-all disabled:opacity-50">
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
