<script setup>
/**
 * News Detail Page - Premium Redesign
 * Modern, animated, responsive design following destination hero pattern
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage, Link, Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ArticleInteractions from '@/Components/ArticleInteractions.vue';
import SocialShareButtons from '@/Components/SocialShareButtons.vue';
import { 
    ChevronRight, ArrowLeft, Calendar, Eye, Clock, Share2, MessageCircle, 
    User, Send, Tag, Heart, Bookmark, Pin, Shield, Reply, ChevronDown, 
    ChevronUp, Sparkles, Newspaper, BookOpen
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    article: Object,
    relatedNews: Array,
    comments: Array,
});

const { auth } = usePage().props;
const isLoggedIn = computed(() => !!auth?.user);
const heroRef = ref(null);
let ctx;

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
const formatTimeAgo = (date) => {
    const diff = Date.now() - new Date(date).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 60) return `${mins} menit lalu`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days} hari lalu`;
    return formatDate(date);
};

const shareUrl = computed(() => typeof window !== 'undefined' ? window.location.href : '');

// Comments
const commentText = ref('');
const isSubmitting = ref(false);
const localComments = ref(props.comments || []);
const replyingTo = ref(null);
const replyingToUser = ref(null);
const parentCommentId = ref(null);
const replyText = ref('');
const expandedReplies = ref({});

const toggleReplies = (commentId) => {
    expandedReplies.value[commentId] = !expandedReplies.value[commentId];
};

const startReply = (comment, topLevelCommentId = null) => {
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

const submitComment = async (commentId = null) => {
    const actualParentId = commentId ? parentCommentId.value : null;
    const text = commentId ? replyText.value : commentText.value;
    if (!text.trim() || isSubmitting.value) return;
    
    isSubmitting.value = true;
    try {
        const response = await fetch(`/articles/${props.article.id}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ content: text, parent_id: actualParentId })
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

// Live Reading Time Counter
const readingStartTime = ref(null);
const elapsedSeconds = ref(0);
const readingTimeInterval = ref(null);

const liveReadingTime = computed(() => {
    const totalSeconds = elapsedSeconds.value;
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    
    if (minutes === 0) {
        return `${seconds} detik`;
    } else {
        return `${minutes}m ${seconds}s`;
    }
});

const updateReadingTime = () => {
    if (readingStartTime.value && !document.hidden) {
        elapsedSeconds.value = Math.floor((Date.now() - readingStartTime.value) / 1000);
    }
};

// GSAP Animations
onMounted(() => {
    // Start reading time counter
    readingStartTime.value = Date.now();
    readingTimeInterval.value = setInterval(updateReadingTime, 1000);
    
    ctx = gsap.context(() => {
        const tl = gsap.timeline({ delay: 0.2 });
        tl.fromTo('.hero-breadcrumb', { opacity: 0, y: -15 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' })
          .fromTo('.hero-badges > *', { opacity: 0, scale: 0.7, y: 10 }, { opacity: 1, scale: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'back.out(3)' }, '-=0.2')
          .fromTo('.hero-title', { opacity: 0, y: 25, filter: 'blur(8px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.5, ease: 'power3.out' }, '-=0.2')
          .fromTo('.hero-meta > *', { opacity: 0, x: -10 }, { opacity: 1, x: 0, duration: 0.3, stagger: 0.06 }, '-=0.2');

        // Ken Burns effect on background
        gsap.to('.hero-bg-image', {
            scale: 1.1,
            duration: 15,
            ease: 'none',
            repeat: -1,
            yoyo: true
        });

        // Content animations
        gsap.fromTo('.content-card', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', delay: 0.5 }
        );

        gsap.fromTo('.sidebar-card',
            { opacity: 0, x: 30 },
            { opacity: 1, x: 0, duration: 0.5, delay: 0.8, ease: 'power2.out' }
        );
    }, heroRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
    if (readingTimeInterval.value) clearInterval(readingTimeInterval.value);
});
</script>

<template>
    <div ref="heroRef">
        <Head>
            <title>{{ article.title }} - Berita TNLL</title>
            <meta name="description" :content="article.excerpt || article.meta_description">
            <meta property="og:title" :content="article.title">
            <meta property="og:description" :content="article.excerpt">
            <meta property="og:image" :content="article.image_url">
        </Head>

        <!-- Premium Hero Section -->
        <section class="relative min-h-[55vh] overflow-hidden">
            <!-- Background Image with Ken Burns -->
            <div class="absolute inset-0">
                <img 
                    :src="article.image_url || '/images/placeholder-no-image.svg'" 
                    :alt="article.title"
                    class="hero-bg-image w-full h-full object-cover"
                    @error="(e) => e.target.src = '/images/placeholder-no-image.svg'"
                >
                <!-- Premium Gradient Overlays -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/30"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-rose-900/30 to-transparent"></div>
            </div>

            <!-- Floating Decorative Elements -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-[15%] right-[10%] w-24 h-24 border border-white/5 rounded-full animate-pulse-slow"></div>
                <div class="absolute bottom-[25%] left-[5%] w-16 h-16 border border-rose-500/10 rounded-2xl rotate-12 animate-float"></div>
                <div class="absolute top-[40%] right-[25%] w-8 h-8 bg-rose-500/10 rounded-full blur-xl animate-pulse"></div>
            </div>

            <!-- Wave Transition -->
            <svg class="absolute bottom-0 left-0 right-0 w-full h-20 sm:h-24" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
                <path d="M0,50L60,45C120,40,240,30,360,35C480,40,600,60,720,65C840,70,960,60,1080,50C1200,40,1320,30,1380,25L1440,20L1440,100L0,100Z" fill="white"/>
            </svg>

            <!-- Hero Content -->
            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 sm:pt-28 pb-28 sm:pb-32">
                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb flex items-center gap-1.5 text-[10px] sm:text-xs text-white/60 mb-5 font-medium flex-wrap">
                    <Link href="/" class="hover:text-white transition-colors">Beranda</Link>
                    <ChevronRight class="w-3 h-3" />
                    <Link href="/news" class="hover:text-white transition-colors">Berita</Link>
                    <ChevronRight class="w-3 h-3" />
                    <span class="text-white/90 truncate max-w-[200px]">{{ article.title }}</span>
                </nav>

                <!-- Badges -->
                <div class="hero-badges flex flex-wrap items-center gap-2 mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xl border border-white/15 text-white text-[10px] sm:text-xs font-semibold">
                        <Newspaper class="w-3 h-3" />
                        Berita
                    </span>
                    <span v-if="article.is_featured" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[9px] sm:text-[10px] font-bold uppercase shadow-lg shadow-amber-500/30">
                        <Sparkles class="w-3 h-3" />
                        Unggulan
                    </span>
                    <!-- Live Reading Time Badge -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/95 text-gray-900 text-[10px] sm:text-xs font-bold shadow-lg">
                        <Clock class="w-3 h-3 text-rose-500 animate-pulse" />
                        <span>Dibaca:</span>
                        <span class="text-rose-600">{{ liveReadingTime }}</span>
                    </span>
                </div>

                <!-- Title -->
                <h1 class="hero-title text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-4 max-w-4xl">
                    {{ article.title }}
                </h1>

                <!-- Meta Info -->
                <div class="hero-meta flex flex-wrap items-center gap-2 text-white/70 text-[10px] sm:text-[11px]">
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                        <Calendar class="w-3.5 h-3.5 text-rose-400" />
                        {{ formatDate(article.published_at) }}
                    </span>
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                        <Eye class="w-3.5 h-3.5 text-emerald-400" />
                        {{ article.views_count || 0 }} views
                    </span>
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                        <Heart class="w-3.5 h-3.5 text-rose-400" />
                        {{ article.likes_count || 0 }} suka
                    </span>
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-sm">
                        <MessageCircle class="w-3.5 h-3.5 text-blue-400" />
                        {{ totalCommentsCount }} komentar
                    </span>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section class="py-8 sm:py-12 md:py-16 bg-gradient-to-b from-white to-gray-50/50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Interactions Bar -->
                        <div class="content-card bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/80 p-4">
                            <ArticleInteractions 
                                :article-id="article.id" 
                                :initial-likes-count="article.likes_count || 0"
                                :initial-views-count="article.views_count || 0"
                            />
                        </div>

                        <!-- Article Content -->
                        <div class="content-card bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/80 p-5 sm:p-6 md:p-8">
                            <div class="article-content prose prose-sm sm:prose-base md:prose-lg max-w-none 
                                prose-headings:font-bold prose-headings:text-gray-900
                                prose-p:text-gray-700 prose-p:leading-relaxed
                                prose-a:text-rose-600 prose-a:no-underline hover:prose-a:underline
                                prose-strong:text-gray-900
                                prose-img:rounded-xl prose-img:shadow-lg" 
                                v-html="article.content">
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="article.tags?.length > 0" class="content-card flex flex-wrap gap-2">
                            <Link v-for="tag in article.tags" :key="tag.id" :href="`/news?tag=${tag.slug}`"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 rounded-full text-xs sm:text-sm font-medium hover:bg-rose-100 hover:shadow-md transition-all duration-300">
                                <span class="text-rose-400">#</span>{{ tag.name }}
                            </Link>
                        </div>

                        <!-- Social Share -->
                        <div class="content-card bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/80 p-5 sm:p-6">
                            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm sm:text-base">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center">
                                    <Share2 class="w-4 h-4 text-white" />
                                </div>
                                Bagikan Berita
                            </h3>
                            <SocialShareButtons :url="shareUrl" :title="article.title" :description="article.excerpt" />
                        </div>

                        <!-- Comments Section -->
                        <div class="content-card bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/80 p-5 sm:p-6">
                            <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2 text-sm sm:text-base">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center">
                                    <MessageCircle class="w-4 h-4 text-white" />
                                </div>
                                Komentar ({{ totalCommentsCount }})
                            </h3>

                            <!-- Comment Form -->
                            <div v-if="isLoggedIn" class="mb-6">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                        {{ auth.user?.name?.[0] || 'U' }}
                                    </div>
                                    <div class="flex-1">
                                        <textarea v-model="commentText" rows="3" placeholder="Tulis komentar Anda..."
                                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 resize-none text-xs sm:text-sm transition-all"></textarea>
                                        <div class="flex justify-end mt-2">
                                            <button @click="submitComment()" :disabled="isSubmitting || !commentText.trim()"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-rose-500 to-red-600 text-white text-xs sm:text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-rose-500/25 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                                <Send class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                                {{ isSubmitting ? 'Mengirim...' : 'Kirim' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mb-6 p-4 bg-gradient-to-r from-rose-50 to-red-50 rounded-xl text-center border border-rose-100">
                                <p class="text-gray-600 text-xs sm:text-sm mb-2">Silakan login untuk mengirim komentar</p>
                                <Link href="/login" class="inline-flex items-center gap-1.5 text-rose-600 font-semibold hover:underline text-xs sm:text-sm">
                                    Login sekarang
                                    <ChevronRight class="w-3.5 h-3.5" />
                                </Link>
                            </div>

                            <!-- Comments List -->
                            <div class="space-y-4">
                                <div v-for="comment in localComments" :key="comment.id" 
                                    :class="['p-3 sm:p-4 rounded-xl transition-all duration-300', comment.is_pinned ? 'bg-amber-50 border border-amber-200 shadow-amber-100/50 shadow-md' : 'bg-gray-50 hover:bg-gray-100/80']">
                                    <div class="flex items-start gap-2.5 sm:gap-3">
                                        <div :class="['w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center text-white text-xs sm:text-sm font-bold flex-shrink-0',
                                            comment.admin ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : 'bg-gradient-to-br from-gray-400 to-gray-500']">
                                            {{ comment.user?.name?.[0] || comment.admin?.name?.[0] || 'A' }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-1">
                                                <span class="font-semibold text-gray-900 text-xs sm:text-sm">{{ comment.user?.name || comment.admin?.name || 'Admin' }}</span>
                                                <span v-if="comment.admin" class="inline-flex items-center gap-0.5 sm:gap-1 px-1.5 sm:px-2 py-0.5 bg-purple-100 text-purple-700 text-[10px] sm:text-xs font-medium rounded-full">
                                                    <Shield class="w-2.5 h-2.5 sm:w-3 sm:h-3" />Admin
                                                </span>
                                                <span v-if="comment.is_pinned" class="inline-flex items-center gap-0.5 sm:gap-1 px-1.5 sm:px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] sm:text-xs font-medium rounded-full">
                                                    <Pin class="w-2.5 h-2.5 sm:w-3 sm:h-3" />Disematkan
                                                </span>
                                                <span class="text-[10px] sm:text-xs text-gray-400">{{ formatTimeAgo(comment.created_at) }}</span>
                                            </div>
                                            <p class="text-gray-700 text-xs sm:text-sm mb-2 leading-relaxed" v-html="comment.content.replace(/@(\w+)/g, '<span class=\'text-rose-600 font-semibold\'>@$1</span>')"></p>
                                            
                                            <div class="flex items-center gap-3">
                                                <button v-if="isLoggedIn" @click="startReply(comment)"
                                                    class="inline-flex items-center gap-1 text-[10px] sm:text-xs text-gray-500 hover:text-rose-600 transition-colors font-medium">
                                                    <Reply class="w-3 h-3 sm:w-3.5 sm:h-3.5" />Balas
                                                </button>
                                                <button v-if="comment.replies?.length > 0" @click="toggleReplies(comment.id)"
                                                    class="inline-flex items-center gap-1 text-[10px] sm:text-xs text-gray-500 hover:text-rose-600 transition-colors font-medium">
                                                    <component :is="expandedReplies[comment.id] ? ChevronUp : ChevronDown" class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                                                    {{ comment.replies.length }} balasan
                                                </button>
                                            </div>

                                            <!-- Reply Form -->
                                            <div v-if="replyingTo === comment.id" class="mt-3 flex items-start gap-2">
                                                <textarea v-model="replyText" rows="2" :placeholder="`Balas ke @${replyingToUser}...`"
                                                    class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 resize-none text-xs sm:text-sm"></textarea>
                                                <div class="flex flex-col gap-1">
                                                    <button @click="submitComment(comment.id)" :disabled="isSubmitting || !replyText.trim()"
                                                        class="px-3 py-2 bg-rose-500 text-white text-[10px] sm:text-xs font-semibold rounded-lg hover:bg-rose-600 transition-colors disabled:opacity-50">
                                                        <Send class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                                                    </button>
                                                    <button @click="cancelReply" class="px-3 py-2 bg-gray-200 text-gray-600 text-[10px] sm:text-xs font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                                                        Batal
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Replies -->
                                            <div v-if="expandedReplies[comment.id] && comment.replies?.length > 0" class="mt-3 space-y-3 pl-3 sm:pl-4 border-l-2 border-rose-200">
                                                <div v-for="reply in comment.replies" :key="reply.id" class="flex items-start gap-2">
                                                    <div :class="['w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-white text-[10px] sm:text-xs font-bold flex-shrink-0',
                                                        reply.admin ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : 'bg-gradient-to-br from-gray-400 to-gray-500']">
                                                        {{ reply.user?.name?.[0] || reply.admin?.name?.[0] || 'A' }}
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-0.5">
                                                            <span class="font-semibold text-gray-900 text-[10px] sm:text-xs">{{ reply.user?.name || reply.admin?.name || 'Admin' }}</span>
                                                            <span v-if="reply.admin" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[9px] sm:text-[10px] font-medium rounded-full">
                                                                <Shield class="w-2 h-2 sm:w-2.5 sm:h-2.5" />Admin
                                                            </span>
                                                            <span class="text-[9px] sm:text-[10px] text-gray-400">{{ formatTimeAgo(reply.created_at) }}</span>
                                                        </div>
                                                        <p class="text-gray-700 text-[10px] sm:text-xs leading-relaxed" v-html="reply.content.replace(/@(\w+)/g, '<span class=\'text-rose-600 font-semibold\'>@$1</span>')"></p>
                                                        <!-- Reply to Reply Button -->
                                                        <button v-if="isLoggedIn" @click="startReply(reply, comment.id)"
                                                            class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] text-gray-400 hover:text-rose-600 transition-colors font-medium mt-1">
                                                            <Reply class="w-2.5 h-2.5" />Balas
                                                        </button>
                                                        <!-- Reply Form for Reply -->
                                                        <div v-if="replyingTo === reply.id" class="mt-2 flex items-start gap-2">
                                                            <textarea v-model="replyText" rows="2" :placeholder="`Balas ke @${replyingToUser}...`"
                                                                class="flex-1 px-2 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 resize-none text-[10px] sm:text-xs"></textarea>
                                                            <div class="flex flex-col gap-1">
                                                                <button @click="submitComment(reply.id)" :disabled="isSubmitting || !replyText.trim()"
                                                                    class="px-2 py-1.5 bg-rose-500 text-white text-[9px] sm:text-[10px] font-semibold rounded-lg hover:bg-rose-600 transition-colors disabled:opacity-50">
                                                                    <Send class="w-2.5 h-2.5" />
                                                                </button>
                                                                <button @click="cancelReply" class="px-2 py-1.5 bg-gray-200 text-gray-600 text-[9px] sm:text-[10px] font-semibold rounded-lg hover:bg-gray-300 transition-colors">
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
                                <p v-if="localComments.length === 0" class="text-center text-gray-500 py-8 text-sm">Belum ada komentar. Jadilah yang pertama!</p>
                            </div>
                        </div>

                        <!-- Related News -->
                        <div v-if="relatedNews?.length > 0" class="content-card pt-6 sm:pt-8">
                            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-4 sm:mb-6 flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center">
                                    <Newspaper class="w-4 h-4 text-white" />
                                </div>
                                Berita Terkait
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                                <Link v-for="item in relatedNews" :key="item.id" :href="`/news/${item.slug}`" 
                                    class="group bg-white rounded-xl overflow-hidden shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                    <div class="relative h-28 sm:h-32 overflow-hidden">
                                        <img :src="item.image_url || '/images/placeholder-no-image.svg'" :alt="item.title" 
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                                            @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    </div>
                                    <div class="p-3 sm:p-4">
                                        <h3 class="font-bold text-gray-900 text-xs sm:text-sm group-hover:text-rose-600 transition-colors line-clamp-2">{{ item.title }}</h3>
                                        <div class="flex items-center gap-3 mt-2 text-[10px] sm:text-xs text-gray-400">
                                            <span class="flex items-center gap-1"><Eye class="w-3 h-3" />{{ item.views_count || 0 }}</span>
                                            <span class="flex items-center gap-1"><Heart class="w-3 h-3" />{{ item.likes_count || 0 }}</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <div class="sidebar-card bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100/80 p-5 sm:p-6 sticky top-24">
                            <!-- Author -->
                            <div class="mb-6">
                                <h4 class="text-[10px] sm:text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Penulis</h4>
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br from-rose-100 to-rose-200 flex items-center justify-center text-rose-600 font-bold text-base sm:text-lg shadow-lg shadow-rose-100">
                                        {{ article.author_name?.[0] || article.author?.name?.[0] || 'A' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm sm:text-base">{{ article.author_name || article.author?.name || 'Admin TNLL' }}</p>
                                        <p class="text-[10px] sm:text-xs text-gray-500">Penulis</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Excerpt -->
                            <div v-if="article.excerpt" class="mb-6 p-4 bg-gradient-to-br from-rose-50 to-red-50 rounded-xl border border-rose-100/50">
                                <p class="text-xs sm:text-sm text-gray-600 italic leading-relaxed line-clamp-4">{{ article.excerpt }}</p>
                            </div>

                            <hr class="border-gray-200 my-6">
                            
                            <Link href="/news" class="w-full py-3 flex items-center justify-center gap-2 bg-gradient-to-r from-rose-500 to-red-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 hover:-translate-y-0.5 transition-all">
                                <ArrowLeft class="w-4 h-4" />Kembali ke Berita
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(12deg); }
    50% { transform: translateY(-15px) rotate(12deg); }
}
.animate-float { animation: float 6s ease-in-out infinite; }
</style>
