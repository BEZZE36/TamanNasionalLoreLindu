<script setup>
/**
 * DetailComments.vue - Premium Comment Section for Destinations
 * Based on Blog comment system, connects to admin comments management
 * Features: Star Rating, @username reply mention
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { MessageCircle, Send, Reply, ChevronDown, ChevronUp, Shield, Pin, Star } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ 
    destination: { type: Object, required: true },
    comments: { type: Array, default: () => [] }
});

const page = usePage();
const user = page.props.auth?.user;
const sectionRef = ref(null);
let ctx;

// Comment state
const commentText = ref('');
const commentRating = ref(0);
const hoverRating = ref(0);
const isSubmitting = ref(false);
const localComments = ref(props.comments || []);
const replyingTo = ref(null);
const replyingToUser = ref(null);
const parentCommentId = ref(null); // Track the top-level parent comment
const replyText = ref('');
const expandedReplies = ref({});

const formatTimeAgo = (date) => {
    if (!date) return '';
    const diff = Date.now() - new Date(date).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return `${mins} menit lalu`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days} hari lalu`;
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const toggleReplies = (commentId) => {
    expandedReplies.value[commentId] = !expandedReplies.value[commentId];
};

const startReply = (comment, topLevelCommentId = null) => {
    // If replying to a reply, use the top-level parent's ID
    parentCommentId.value = topLevelCommentId || comment.id;
    replyingTo.value = comment.id;
    const username = comment.user?.name || comment.admin?.name || 'Admin';
    replyingToUser.value = username;
    replyText.value = `@${username} `;
};

const cancelReply = () => {
    replyingTo.value = null;
    replyingToUser.value = null;
    parentCommentId.value = null;
    replyText.value = '';
};

const setRating = (rating) => {
    commentRating.value = rating;
};

const submitComment = async (commentId = null) => {
    // For replies, always use parentCommentId (the top-level comment)
    const actualParentId = commentId ? parentCommentId.value : null;
    const text = commentId ? replyText.value : commentText.value;
    if (!text.trim() || isSubmitting.value) return;
    
    isSubmitting.value = true;
    try {
        const payload = { content: text, parent_id: actualParentId };
        // Only include rating for main comments (not replies)
        if (!actualParentId && commentRating.value > 0) {
            payload.rating = commentRating.value;
        }
        
        const response = await fetch(`/destinations/${props.destination.id}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            if (actualParentId) {
                const parent = localComments.value.find(c => c.id === actualParentId);
                if (parent) {
                    if (!parent.replies) parent.replies = [];
                    parent.replies.push(data.comment);
                    expandedReplies.value[actualParentId] = true;
                }
                replyingTo.value = null;
                replyingToUser.value = null;
                parentCommentId.value = null;
                replyText.value = '';
            } else {
                localComments.value.unshift(data.comment);
                commentText.value = '';
                commentRating.value = 0;
            }
        } else if (response.status === 401) {
            alert('Silakan login untuk mengirim komentar');
        } else if (data.message) {
            alert(data.message);
        }
    } catch (e) {
        console.error('Failed to submit comment', e);
    } finally {
        isSubmitting.value = false;
    }
};

const totalCommentsCount = computed(() => {
    let count = localComments.value.length;
    localComments.value.forEach(c => {
        if (c.replies) count += c.replies.length;
    });
    return count;
});

const averageRating = computed(() => {
    const rated = localComments.value.filter(c => c.rating && c.rating > 0);
    if (rated.length === 0) return 0;
    const sum = rated.reduce((acc, c) => acc + c.rating, 0);
    return (sum / rated.length).toFixed(1);
});

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.comment-card', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out', scrollTrigger: { trigger: sectionRef.value, start: 'top 80%' } });
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div ref="sectionRef" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                    <MessageCircle class="w-4 h-4 text-white" />
                </div>
                <h2 class="text-sm font-bold text-gray-900">Komentar & Rating ({{ totalCommentsCount }})</h2>
            </div>
            <div v-if="averageRating > 0" class="flex items-center gap-1.5">
                <div class="flex items-center">
                    <Star v-for="i in 5" :key="i" :class="['w-3.5 h-3.5', i <= Math.round(averageRating) ? 'text-amber-400 fill-amber-400' : 'text-gray-200']" />
                </div>
                <span class="text-[11px] font-bold text-gray-700">{{ averageRating }}</span>
            </div>
        </div>

        <!-- Comment Form -->
        <div v-if="user" class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 border-b border-gray-100">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white font-bold text-[11px] flex-shrink-0">
                    {{ user?.name?.[0] || 'U' }}
                </div>
                <div class="flex-1">
                    <!-- Star Rating -->
                    <div class="mb-2">
                        <p class="text-[10px] text-gray-500 mb-1">Berikan rating:</p>
                        <div class="flex items-center gap-0.5">
                            <button v-for="i in 5" :key="i" @click="setRating(i)" @mouseenter="hoverRating = i" @mouseleave="hoverRating = 0"
                                class="p-0.5 transition-transform hover:scale-110">
                                <Star :class="['w-5 h-5 transition-colors', (hoverRating || commentRating) >= i ? 'text-amber-400 fill-amber-400' : 'text-gray-300']" />
                            </button>
                            <span v-if="commentRating > 0" class="ml-2 text-[10px] text-amber-600 font-medium">{{ commentRating }}/5</span>
                        </div>
                    </div>
                    
                    <textarea v-model="commentText" rows="2" placeholder="Tulis komentar Anda..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none text-[11px]"></textarea>
                    <div class="flex justify-end mt-2">
                        <button @click="submitComment()" :disabled="isSubmitting || !commentText.trim()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[11px] font-semibold rounded-xl hover:shadow-lg hover:shadow-emerald-500/25 transition-all disabled:opacity-50">
                            <Send class="w-3.5 h-3.5" />
                            {{ isSubmitting ? 'Mengirim...' : 'Kirim' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 border-b border-gray-100 text-center">
            <p class="text-[11px] text-gray-500 mb-2">Silakan login untuk mengirim komentar</p>
            <Link href="/login" class="text-[11px] font-semibold text-emerald-600 hover:underline">Login Sekarang</Link>
        </div>

        <!-- Comments List -->
        <div class="p-4 space-y-3 max-h-[450px] overflow-y-auto">
            <div v-for="comment in localComments" :key="comment.id" 
                :class="['comment-card p-3 rounded-xl transition-all duration-300', comment.is_pinned ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50 hover:bg-gray-100/80']">
                <div class="flex items-start gap-2.5">
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0',
                        comment.admin ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : 'bg-gradient-to-br from-gray-400 to-gray-500']">
                        {{ comment.user?.name?.[0] || comment.admin?.name?.[0] || 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                            <span class="font-semibold text-gray-900 text-[11px]">{{ comment.user?.name || comment.admin?.name || 'Admin' }}</span>
                            <!-- Star Rating Display -->
                            <div v-if="comment.rating && comment.rating > 0" class="flex items-center">
                                <Star v-for="i in 5" :key="i" :class="['w-2.5 h-2.5', i <= comment.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200']" />
                            </div>
                            <span v-if="comment.admin" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[9px] font-medium rounded-full">
                                <Shield class="w-2.5 h-2.5" />Admin
                            </span>
                            <span v-if="comment.is_pinned" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[9px] font-medium rounded-full">
                                <Pin class="w-2.5 h-2.5" />Disematkan
                            </span>
                            <span class="text-[9px] text-gray-400">{{ formatTimeAgo(comment.created_at) }}</span>
                        </div>
                        <p class="text-gray-700 text-[11px] mb-2 leading-relaxed" v-html="comment.content.replace(/@(\w+)/g, '<span class=\'text-emerald-600 font-semibold\'>@$1</span>')"></p>
                        
                        <div class="flex items-center gap-3">
                            <button v-if="user" @click="startReply(comment)"
                                class="inline-flex items-center gap-1 text-[10px] text-gray-500 hover:text-emerald-600 transition-colors font-medium">
                                <Reply class="w-3 h-3" />Balas
                            </button>
                            <button v-if="comment.replies?.length > 0" @click="toggleReplies(comment.id)"
                                class="inline-flex items-center gap-1 text-[10px] text-gray-500 hover:text-emerald-600 transition-colors font-medium">
                                <component :is="expandedReplies[comment.id] ? ChevronUp : ChevronDown" class="w-3 h-3" />
                                {{ comment.replies.length }} balasan
                            </button>
                        </div>

                        <!-- Reply Form -->
                        <div v-if="replyingTo === comment.id" class="mt-3 flex items-start gap-2">
                            <textarea v-model="replyText" rows="2" :placeholder="`Balas ke @${replyingToUser}...`"
                                class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none text-[10px]"></textarea>
                            <div class="flex flex-col gap-1">
                                <button @click="submitComment(comment.id)" :disabled="isSubmitting || !replyText.trim()"
                                    class="px-3 py-2 bg-emerald-500 text-white text-[10px] font-semibold rounded-lg hover:bg-emerald-600 transition-colors disabled:opacity-50">
                                    <Send class="w-3 h-3" />
                                </button>
                                <button @click="cancelReply" class="px-3 py-2 bg-gray-200 text-gray-600 text-[10px] font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                                    Batal
                                </button>
                            </div>
                        </div>

                        <!-- Replies -->
                        <div v-if="expandedReplies[comment.id] && comment.replies?.length > 0" class="mt-3 space-y-2 pl-3 border-l-2 border-emerald-200">
                            <div v-for="reply in comment.replies" :key="reply.id" class="flex items-start gap-2">
                                <div :class="['w-6 h-6 rounded-full flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0',
                                    reply.admin ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : 'bg-gradient-to-br from-gray-400 to-gray-500']">
                                    {{ reply.user?.name?.[0] || reply.admin?.name?.[0] || 'A' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 mb-0.5">
                                        <span class="font-semibold text-gray-900 text-[10px]">{{ reply.user?.name || reply.admin?.name || 'Admin' }}</span>
                                        <span v-if="reply.admin" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[8px] font-medium rounded-full">
                                            <Shield class="w-2 h-2" />Admin
                                        </span>
                                        <span class="text-[8px] text-gray-400">{{ formatTimeAgo(reply.created_at) }}</span>
                                    </div>
                                    <p class="text-gray-700 text-[10px] leading-relaxed" v-html="reply.content.replace(/@(\w+)/g, '<span class=\'text-emerald-600 font-semibold\'>@$1</span>')"></p>
                                    <!-- Reply to Reply Button -->
                                    <button v-if="user" @click="startReply(reply, comment.id)"
                                        class="inline-flex items-center gap-1 text-[9px] text-gray-400 hover:text-emerald-600 transition-colors font-medium mt-1">
                                        <Reply class="w-2.5 h-2.5" />Balas
                                    </button>
                                    <!-- Reply Form for Reply -->
                                    <div v-if="replyingTo === reply.id" class="mt-2 flex items-start gap-2">
                                        <textarea v-model="replyText" rows="2" :placeholder="`Balas ke @${replyingToUser}...`"
                                            class="flex-1 px-2 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none text-[9px]"></textarea>
                                        <div class="flex flex-col gap-1">
                                            <button @click="submitComment(reply.id)" :disabled="isSubmitting || !replyText.trim()"
                                                class="px-2 py-1.5 bg-emerald-500 text-white text-[9px] font-semibold rounded-lg hover:bg-emerald-600 transition-colors disabled:opacity-50">
                                                <Send class="w-2.5 h-2.5" />
                                            </button>
                                            <button @click="cancelReply" class="px-2 py-1.5 bg-gray-200 text-gray-600 text-[9px] font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="localComments.length === 0" class="text-center py-8">
                <MessageCircle class="w-10 h-10 text-gray-200 mx-auto mb-2" />
                <p class="text-[11px] text-gray-400">Belum ada komentar. Jadilah yang pertama!</p>
            </div>
        </div>
    </div>
</template>
