<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, MessageSquare, Send, Star, MapPin, Reply, Clock, Edit, Trash2, Eye, X } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    feedback: { type: Object, required: true }
});

const replyForm = useForm({ message: '', is_public: false });
const editingReply = ref(null);
const editForm = useForm({ reply_message: '', is_public: false });

const submitReply = () => { replyForm.post(`/admin/testimonial/${props.feedback.id}/reply`, { onSuccess: () => replyForm.reset() }); };

const startEditReply = (r) => { editingReply.value = r.id; editForm.reply_message = r.reply_message; editForm.is_public = r.is_public; };
const cancelEditReply = () => { editingReply.value = null; editForm.reset(); };
const updateReply = (r) => { editForm.put(`/admin/testimonial/reply/${r.id}`, { onSuccess: () => cancelEditReply() }); };
const deleteReply = (r) => { if (confirm('Hapus balasan ini?')) router.delete(`/admin/testimonial/reply/${r.id}`); };

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <Link href="/admin/testimonial" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-6 h-6" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Detail Feedback</h1>
                    <p class="mt-1 text-amber-100/90">Dari {{ feedback.display_name }}</p>
                </div>
                <Link :href="`/admin/testimonial/${feedback.id}/edit`" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 text-white rounded-xl font-bold hover:bg-white/30 transition-all">
                    <Edit class="w-4 h-4" />Edit
                </Link>
            </div>
        </div>

        <!-- Feedback Info -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                            {{ feedback.display_name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ feedback.display_name }}</h2>
                            <div class="flex items-center gap-3 text-sm text-gray-500 mt-1">
                                <span class="flex items-center gap-1"><Clock class="w-4 h-4" />{{ formatDate(feedback.created_at) }}</span>
                                <span v-if="feedback.destination" class="flex items-center gap-1 text-amber-600"><MapPin class="w-4 h-4" />{{ feedback.destination.name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-for="i in 5" :key="i" :class="['text-2xl', i <= feedback.rating ? 'text-amber-400' : 'text-gray-200']">⭐</span>
                    </div>
                </div>
            </div>
            <div class="p-8">
                <p class="text-gray-700 text-lg leading-relaxed whitespace-pre-wrap">{{ feedback.message }}</p>
                <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                    <span :class="['px-3 py-1.5 rounded-full text-sm font-bold capitalize', { 'bg-red-100 text-red-700': feedback.status === 'unread', 'bg-blue-100 text-blue-700': feedback.status === 'read', 'bg-green-100 text-green-700': feedback.status === 'replied', 'bg-gray-100 text-gray-600': feedback.status === 'archived' }]">{{ feedback.status }}</span>
                    <span :class="['px-3 py-1.5 rounded-full text-sm font-bold', feedback.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">{{ feedback.is_published ? '🌐 Publik' : '🔒 Privat' }}</span>
                </div>
            </div>
        </div>

        <!-- Replies -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Reply class="w-6 h-6 text-blue-500" />Balasan ({{ feedback.replies?.length || 0 }})</h2>
            </div>
            <div class="p-6 space-y-4">
                <div v-for="reply in feedback.replies" :key="reply.id" class="bg-blue-50/50 rounded-xl p-4 border border-blue-100">
                    <template v-if="editingReply === reply.id">
                        <textarea v-model="editForm.reply_message" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 mb-3"></textarea>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="editForm.is_public" class="rounded">Tampilkan publik</label>
                            <div class="flex gap-2">
                                <button @click="cancelEditReply" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                                <button @click="updateReply(reply)" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Simpan</button>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">{{ reply.admin?.name?.charAt(0) || 'A' }}</div>
                                <div><p class="font-semibold text-gray-900">{{ reply.admin?.name || 'Admin' }}</p><p class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</p></div>
                            </div>
                            <div class="flex items-center gap-1">
                                <span v-if="reply.is_public" class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Publik</span>
                                <button @click="startEditReply(reply)" class="p-1.5 rounded-lg hover:bg-blue-100 text-gray-500 hover:text-blue-600 transition-colors"><Edit class="w-4 h-4" /></button>
                                <button @click="deleteReply(reply)" class="p-1.5 rounded-lg hover:bg-red-100 text-gray-500 hover:text-red-600 transition-colors"><Trash2 class="w-4 h-4" /></button>
                            </div>
                        </div>
                        <p class="mt-3 text-gray-700">{{ reply.reply_message }}</p>
                    </template>
                </div>
                <div v-if="!feedback.replies?.length" class="text-center py-8 text-gray-500">Belum ada balasan</div>
            </div>
        </div>

        <!-- Reply Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-green-50 to-teal-50 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Send class="w-6 h-6 text-green-500" />Kirim Balasan</h2>
            </div>
            <form @submit.prevent="submitReply" class="p-6 space-y-4">
                <textarea v-model="replyForm.message" rows="4" required placeholder="Tulis balasan..." class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" v-model="replyForm.is_public" class="rounded border-gray-300 text-green-500 focus:ring-green-500">
                        Tampilkan balasan ini ke publik
                    </label>
                    <button type="submit" :disabled="replyForm.processing"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-teal-500 text-white font-bold rounded-xl shadow-lg hover:shadow-green-500/30 transition-all disabled:opacity-50">
                        <Send class="w-4 h-4" />{{ replyForm.processing ? 'Mengirim...' : 'Kirim Balasan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
