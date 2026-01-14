<script setup>
import { useForm, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import ImageEditorModal from '@/Components/Admin/ImageEditorModal.vue';
import { gsap } from 'gsap';
import { 
    Newspaper, ArrowLeft, Save, Image, Type, AlignLeft, Star, Eye, User, 
    Calendar, Clock, Search, Pin, History, Settings, CheckCircle, Wand2, Loader2, AlertTriangle,
    Languages, Pencil, RefreshCw, ExternalLink, Sparkles
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted, onUnmounted, onBeforeUnmount } from 'vue';
import { handleAIResponse } from '@/Utils/aiToast';

defineOptions({ layout: AdminLayout });

let gsapCtx;
const showFloatingSave = ref(true);

onMounted(() => {
    gsapCtx = gsap.context(() => {
        gsap.fromTo('.header-content', { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' });
        gsap.fromTo('.form-section', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'power2.out' });
        gsap.fromTo('.sidebar-section', { opacity: 0, x: 20 }, { opacity: 1, x: 0, duration: 0.5, stagger: 0.1, delay: 0.3, ease: 'power2.out' });
    });
});
onBeforeUnmount(() => { if (gsapCtx) gsapCtx.revert(); });

const props = defineProps({
    news: { type: Object, required: true },
    locales: { type: Object, default: () => ({ id: 'Indonesia', en: 'English' }) }
});

const page = usePage();
const adminName = computed(() => page.props.auth?.admin?.name || 'Admin');

const form = useForm({
    title: props.news.title || '',
    excerpt: props.news.excerpt || '',
    content: props.news.content || '',
    featured_image: null,
    author_name: props.news.author_name || adminName.value,
    is_featured: props.news.is_featured || false,
    is_published: props.news.is_published || false,
    is_pinned: props.news.is_pinned || false,
    scheduled_at: props.news.scheduled_at ? props.news.scheduled_at.slice(0, 16) : '',
    meta_title: props.news.meta_title || '',
    meta_description: props.news.meta_description || '',
    meta_keywords: props.news.meta_keywords || '',
    locale: props.news.locale || 'id',
});

const imagePreview = ref(props.news.image_url || null);
const showSeoPanel = ref(false);
const showRevisionsPanel = ref(false);
const revisions = ref([]);
const lastAutoSave = ref(props.news.last_auto_save || null);
const autoSaveStatus = ref('');
const isAutoSaving = ref(false);
const autoSaveEnabled = ref(true);

const wordCount = computed(() => {
    const text = form.content?.replace(/<[^>]*>/g, '') || '';
    return text.split(/\s+/).filter(w => w.length > 0).length;
});
const readingTime = computed(() => Math.max(1, Math.ceil(wordCount.value / 200)));

const TITLE_MAX = 100;
const EXCERPT_MAX = 300;
const META_TITLE_MAX = 60;
const META_DESC_MAX = 160;

const titleCharColor = computed(() => {
    const len = form.title?.length || 0;
    if (len > TITLE_MAX) return 'text-red-500';
    if (len > TITLE_MAX * 0.8) return 'text-amber-500';
    return 'text-gray-400';
});

const excerptCharColor = computed(() => {
    const len = form.excerpt?.length || 0;
    if (len > EXCERPT_MAX) return 'text-red-500';
    if (len > EXCERPT_MAX * 0.8) return 'text-amber-500';
    return 'text-gray-400';
});

let autoSaveInterval = null;
let autoSaveTimeout = null;

const autoSave = async () => {
    if (!autoSaveEnabled.value || isAutoSaving.value || form.processing) return;
    isAutoSaving.value = true;
    autoSaveStatus.value = 'Menyimpan...';
    
    try {
        const response = await fetch(`/admin/news/${props.news.id}/auto-save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: form.title, content: form.content, excerpt: form.excerpt,
                author_name: form.author_name, is_featured: form.is_featured,
                is_published: form.is_published, is_pinned: form.is_pinned,
                scheduled_at: form.scheduled_at, meta_title: form.meta_title,
                meta_description: form.meta_description, meta_keywords: form.meta_keywords, locale: form.locale,
            })
        });
        const data = await response.json();
        if (data.success) lastAutoSave.value = data.last_auto_save;
    } catch (e) { console.error('Auto-save failed:', e); }
    finally { isAutoSaving.value = false; }
};

watch(() => [
    form.title, form.content, form.excerpt, form.author_name,
    form.is_featured, form.is_published, form.is_pinned,
    form.scheduled_at, form.meta_title, form.meta_description, form.meta_keywords, form.locale
], () => {
    if (!autoSaveEnabled.value) return;
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(autoSave, 2000);
}, { deep: true });

onUnmounted(() => { clearInterval(autoSaveInterval); clearTimeout(autoSaveTimeout); });

const loadRevisions = async () => {
    try {
        const response = await fetch(`/admin/news/${props.news.id}/revisions`);
        const data = await response.json();
        revisions.value = data.revisions || [];
        showRevisionsPanel.value = true;
    } catch (e) { console.error('Failed to load revisions'); }
};

const restoreRevision = async (revisionId) => {
    if (!confirm('Kembalikan ke revisi ini?')) return;
    try {
        const response = await fetch(`/admin/news/${props.news.id}/restore-revision/${revisionId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (data.success) { alert('Revisi berhasil dipulihkan!'); window.location.reload(); }
        else { alert('Gagal memulihkan revisi'); }
    } catch (e) { console.error(e); alert('Gagal memulihkan revisi'); }
};

const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) { 
        if (!allowedTypes.includes(file.type)) { alert('Format tidak didukung! Hanya JPG, PNG, dan WebP yang diizinkan.'); e.target.value = ''; return; }
        form.featured_image = file; imagePreview.value = URL.createObjectURL(file); 
    }
    e.target.value = '';
};

const submit = () => {
    form.transform(data => ({ ...data, _method: 'PUT' })).post(`/admin/news/${props.news.id}`, { 
        forceFormData: true, onSuccess: () => router.visit('/admin/news') 
    });
};

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const aiLoadingTitle = ref(false);
const aiLoadingExcerpt = ref(false);
const aiLoadingSeo = ref(false);
const aiTranslatingTitle = ref(false);
const aiTranslatingExcerpt = ref(false);
const showTopicModal = ref(false);
const topicInput = ref('');
const showImageEditor = ref(false);

const handleEditedImage = (dataUrl) => {
    imagePreview.value = dataUrl;
    fetch(dataUrl).then(res => res.blob()).then(blob => {
        form.featured_image = new File([blob], 'edited-image.png', { type: 'image/png' });
    });
};

const isFormValid = computed(() => form.title?.trim() && form.content?.trim() &&
           form.excerpt?.trim() && form.author_name?.trim() &&
           form.meta_title?.trim() && form.meta_description?.trim() && form.meta_keywords?.trim());

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

const generateTitle = async (topic = '') => {
    aiLoadingTitle.value = true;
    try {
        const res = await fetch('/admin/ai/headlines', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ content: topic || form.content.replace(/<[^>]*>/g, '').substring(0, 2000), count: 1 })
        });
        const data = await res.json();
        if (data.success && data.content) {
            const match = data.content.match(/1\.\s*(.+)/);
            form.title = match ? match[1].replace(/[\*\"]/g, '').trim() : data.content.split('\n')[0].replace(/^[\d\.\*\-\s]+/, '').replace(/[\*\"]/g, '').trim();
        }
    } catch (e) { console.error(e); }
    finally { aiLoadingTitle.value = false; }
};

const openTopicModal = () => { topicInput.value = ''; showTopicModal.value = true; };
const confirmGenerateTitle = () => { showTopicModal.value = false; generateTitle(topicInput.value); };

const translateTitle = async (direction) => {
    if (!form.title?.trim()) { alert('Masukkan judul terlebih dahulu'); return; }
    aiTranslatingTitle.value = true;
    try {
        const res = await fetch('/admin/ai/translate', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ text: form.title, direction })
        });
        const data = await res.json();
        if (data.success && data.content) form.title = data.content;
    } catch (e) { console.error(e); }
    finally { aiTranslatingTitle.value = false; }
};

const translateExcerpt = async (direction) => {
    if (!form.excerpt?.trim()) { alert('Masukkan ringkasan terlebih dahulu'); return; }
    aiTranslatingExcerpt.value = true;
    try {
        const res = await fetch('/admin/ai/translate', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ text: form.excerpt, direction })
        });
        const data = await res.json();
        if (data.success && data.content) form.excerpt = data.content;
    } catch (e) { console.error(e); }
    finally { aiTranslatingExcerpt.value = false; }
};

const generateExcerpt = async () => {
    if (!form.content?.trim()) { alert('Tulis konten terlebih dahulu'); return; }
    aiLoadingExcerpt.value = true;
    try {
        const res = await fetch('/admin/ai/summarize', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ content: form.content.replace(/<[^>]*>/g, '').substring(0, 5000), sentences: 2 })
        });
        const data = await res.json();
        if (data.success && data.content) form.excerpt = data.content.substring(0, 500);
    } catch (e) { console.error(e); }
    finally { aiLoadingExcerpt.value = false; }
};

const generateSeo = async () => {
    if (!form.content?.trim() && !form.title?.trim()) { alert('Tulis judul atau konten terlebih dahulu'); return; }
    aiLoadingSeo.value = true;
    try {
        const res = await fetch('/admin/ai/generate-seo-tags', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ content: (form.title + '\n\n' + form.content.replace(/<[^>]*>/g, '')).substring(0, 5000) })
        });
        const data = await res.json();
        if (data.success && data.content) {
            if (data.content.meta_title) form.meta_title = data.content.meta_title.substring(0, 60);
            if (data.content.meta_description) form.meta_description = data.content.meta_description.substring(0, 160);
            if (data.content.keywords) form.meta_keywords = data.content.keywords;
        }
    } catch (e) { console.error(e); }
    finally { aiLoadingSeo.value = false; }
};
</script>

<template>
    <div class="max-w-6xl mx-auto space-y-4">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-red-600 via-rose-600 to-pink-600 p-4 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-16 -right-16 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="absolute top-4 left-16 w-1.5 h-1.5 bg-white/30 rounded-full animate-bounce"></div>
            
            <div class="header-content relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link href="/admin/news" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm ring-1 ring-white/30 hover:bg-white/30 transition-all">
                        <ArrowLeft class="w-4 h-4 text-white" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-bold text-white tracking-tight">Edit Berita</h1>
                            <Sparkles class="w-3.5 h-3.5 text-amber-300" />
                        </div>
                        <p class="text-red-100/80 text-[10px] line-clamp-1 max-w-md">{{ news.title }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <div v-if="autoSaveStatus" class="flex items-center gap-1 px-2 py-1 bg-white/20 backdrop-blur-sm rounded-md text-[10px] font-medium text-white">
                        <CheckCircle v-if="!isAutoSaving" class="w-3 h-3 text-emerald-300" />
                        <Loader2 v-else class="w-3 h-3 animate-spin" />
                        <span>{{ autoSaveStatus }}</span>
                    </div>
                    <div class="px-2 py-1 bg-white/20 backdrop-blur-sm rounded-md text-[10px] font-medium text-white">
                        {{ wordCount }} kata • {{ readingTime }}m
                    </div>
                    <a v-if="news.is_published" :href="`/news/${news.slug}`" target="_blank" 
                       class="inline-flex items-center gap-1 px-2 py-1 bg-white text-red-600 rounded-md text-[10px] font-bold hover:bg-red-50 transition-all shadow-md">
                        <ExternalLink class="w-3 h-3" />Lihat
                    </a>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <div class="form-section bg-white rounded-xl shadow-md border border-gray-100 p-4 hover:shadow-lg transition-shadow">
                    <h3 class="text-xs font-bold text-gray-900 mb-3 flex items-center gap-2"><Type class="w-4 h-4 text-red-500" />Judul & Ringkasan</h3>
                    <div class="space-y-3">
                        <div class="relative">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-medium text-gray-700">Judul Berita *</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="translateTitle('id_to_en')" :disabled="aiTranslatingTitle" class="px-1.5 py-0.5 text-[9px] font-medium text-blue-600 hover:bg-blue-50 rounded transition-colors disabled:opacity-50">EN</button>
                                    <button type="button" @click="translateTitle('en_to_id')" :disabled="aiTranslatingTitle" class="px-1.5 py-0.5 text-[9px] font-medium text-green-600 hover:bg-green-50 rounded transition-colors disabled:opacity-50">ID</button>
                                    <button type="button" @click="openTopicModal" :disabled="aiLoadingTitle" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] font-medium text-violet-600 hover:bg-violet-50 rounded transition-colors disabled:opacity-50"><Wand2 class="w-2.5 h-2.5" />AI</button>
                                </div>
                            </div>
                            <div v-if="aiLoadingTitle || aiTranslatingTitle" class="absolute inset-0 z-10 flex items-center bg-white rounded-lg mt-5">
                                <div class="w-full px-3 py-2"><div class="h-5 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 rounded animate-pulse"></div></div>
                            </div>
                            <input v-model="form.title" type="text" required placeholder="Masukkan judul berita..."
                                :class="['w-full px-3 py-2 rounded-lg border focus:ring-2 text-sm font-medium transition-colors', form.title?.trim() ? 'border-gray-200 focus:border-red-500 focus:ring-red-500/20' : 'border-red-300 focus:border-red-500 focus:ring-red-500/20']">
                            <div class="flex justify-between mt-1">
                                <p v-if="form.errors.title" class="text-red-500 text-[10px]">{{ form.errors.title }}</p>
                                <p :class="['text-[10px] ml-auto', titleCharColor]">{{ form.title?.length || 0 }}/{{ TITLE_MAX }}</p>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-medium text-gray-700">Ringkasan *</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="translateExcerpt('id_to_en')" :disabled="aiTranslatingExcerpt" class="px-1.5 py-0.5 text-[9px] font-medium text-blue-600 hover:bg-blue-50 rounded transition-colors disabled:opacity-50">EN</button>
                                    <button type="button" @click="translateExcerpt('en_to_id')" :disabled="aiTranslatingExcerpt" class="px-1.5 py-0.5 text-[9px] font-medium text-green-600 hover:bg-green-50 rounded transition-colors disabled:opacity-50">ID</button>
                                    <button type="button" @click="generateExcerpt" :disabled="aiLoadingExcerpt" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] font-medium text-violet-600 hover:bg-violet-50 rounded transition-colors disabled:opacity-50"><Wand2 class="w-2.5 h-2.5" />AI</button>
                                </div>
                            </div>
                            <div v-if="aiLoadingExcerpt || aiTranslatingExcerpt" class="absolute inset-0 z-10 flex items-start pt-6 bg-white rounded-lg">
                                <div class="w-full px-3 space-y-1.5"><div class="h-3 w-full bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 rounded animate-pulse"></div><div class="h-3 w-11/12 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 rounded animate-pulse"></div></div>
                            </div>
                            <textarea v-model="form.excerpt" rows="2" placeholder="Ringkasan singkat berita..." class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm resize-none"></textarea>
                            <p :class="['text-[10px] text-right', excerptCharColor]">{{ form.excerpt?.length || 0 }}/{{ EXCERPT_MAX }}</p>
                        </div>
                    </div>
                </div>

                <div class="form-section bg-white rounded-xl shadow-md border border-gray-100 p-4 hover:shadow-lg transition-shadow">
                    <h3 class="text-xs font-bold text-gray-900 mb-3 flex items-center gap-2"><AlignLeft class="w-4 h-4 text-red-500" />Konten Berita *</h3>
                    <TinyMceEditor v-model="form.content" id="news-content-edit" :height="350" placeholder="Tulis konten berita..." />
                </div>

                <div class="form-section bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <button type="button" @click="showSeoPanel = !showSeoPanel" class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <h3 class="text-xs font-bold text-gray-900 flex items-center gap-2"><Search class="w-4 h-4 text-green-500" />SEO & Meta</h3>
                        <span class="text-gray-400 text-xs">{{ showSeoPanel ? '▲' : '▼' }}</span>
                    </button>
                    <div v-if="showSeoPanel" class="px-4 pb-4 space-y-3 border-t border-gray-100 pt-3">
                        <button type="button" @click="generateSeo" :disabled="aiLoadingSeo" class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-violet-500 to-fuchsia-500 text-white rounded-lg text-[11px] font-medium hover:opacity-90 transition-all disabled:opacity-50">
                            <Loader2 v-if="aiLoadingSeo" class="w-3 h-3 animate-spin" /><Wand2 v-else class="w-3 h-3" />
                            <span>{{ aiLoadingSeo ? 'Generating...' : 'Generate SEO dengan AI' }}</span>
                        </button>
                        <div class="relative">
                            <label class="text-[11px] font-medium text-gray-700 mb-1 block">Meta Title *</label>
                            <input v-model="form.meta_title" type="text" placeholder="Judul untuk mesin pencari..." class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 text-sm">
                            <p class="text-[10px] text-gray-400 mt-0.5 text-right">{{ form.meta_title?.length || 0 }}/{{ META_TITLE_MAX }}</p>
                        </div>
                        <div class="relative">
                            <label class="text-[11px] font-medium text-gray-700 mb-1 block">Meta Description *</label>
                            <textarea v-model="form.meta_description" rows="2" placeholder="Deskripsi untuk hasil pencarian..." class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 text-sm resize-none"></textarea>
                            <p class="text-[10px] text-gray-400 mt-0.5 text-right">{{ form.meta_description?.length || 0 }}/{{ META_DESC_MAX }}</p>
                        </div>
                        <div class="relative">
                            <label class="text-[11px] font-medium text-gray-700 mb-1 block">Meta Keywords *</label>
                            <input v-model="form.meta_keywords" type="text" placeholder="kata kunci, dipisah, koma..." class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="sidebar-section bg-gradient-to-br from-red-500 to-rose-600 rounded-xl p-3 text-white">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold">Status</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="form.is_published ? 'bg-white/20' : 'bg-yellow-400 text-yellow-900'">
                            {{ form.is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <div class="text-[10px] opacity-80 space-y-0.5">
                        <p>Dibuat: {{ formatDate(news.created_at) }}</p>
                        <p v-if="form.is_published && news.published_at">Dipublikasikan: {{ formatDate(news.published_at) }}</p>
                        <p v-else-if="!form.is_published" class="text-yellow-200">Belum dipublikasikan</p>
                        <p v-if="lastAutoSave && autoSaveEnabled">Terakhir auto-save: {{ lastAutoSave }}</p>
                    </div>
                </div>

                <div class="sidebar-section bg-white rounded-xl shadow-md border border-gray-100 p-3 hover:shadow-lg transition-shadow">
                    <h3 class="text-xs font-bold text-gray-900 mb-2 flex items-center gap-2"><Image class="w-4 h-4 text-red-500" />Gambar Utama</h3>
                    <div class="space-y-2">
                        <div class="relative aspect-video rounded-lg overflow-hidden bg-gray-100 group">
                            <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex items-center justify-center"><Image class="w-8 h-8 text-gray-300" /></div>
                            <div v-if="imagePreview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button type="button" @click="showImageEditor = true" class="flex items-center gap-1 px-2 py-1 bg-white/90 text-gray-800 rounded-md text-[10px] font-medium hover:bg-white transition-colors shadow"><Pencil class="w-3 h-3" />Edit</button>
                                <label class="flex items-center gap-1 px-2 py-1 bg-red-500 text-white rounded-md text-[10px] font-medium hover:bg-red-600 transition-colors cursor-pointer shadow"><RefreshCw class="w-3 h-3" />Ganti<input type="file" accept=".jpg,.jpeg,.png,.webp" @change="handleImageChange" class="hidden"></label>
                            </div>
                        </div>
                        <label v-if="!imagePreview" class="block w-full py-2 bg-red-50 text-red-600 font-medium text-center rounded-lg cursor-pointer hover:bg-red-100 transition-colors text-xs">
                            <input type="file" accept=".jpg,.jpeg,.png,.webp" @change="handleImageChange" class="hidden">Pilih Gambar
                        </label>
                    </div>
                </div>

                <div class="sidebar-section bg-white rounded-xl shadow-md border border-gray-100 p-3 hover:shadow-lg transition-shadow">
                    <h3 class="text-xs font-bold text-gray-900 mb-2 flex items-center gap-2"><Settings class="w-4 h-4 text-gray-500" />Pengaturan</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="text-[11px] font-medium text-gray-700 mb-1 flex items-center gap-1"><User class="w-3 h-3" />Penulis</label>
                            <input v-model="form.author_name" type="text" placeholder="Nama penulis..." class="w-full px-2 py-1.5 rounded-lg border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-xs">
                        </div>
                    </div>
                </div>

                <div class="sidebar-section bg-white rounded-xl shadow-md border border-gray-100 p-3 hover:shadow-lg transition-shadow">
                    <h3 class="text-xs font-bold text-gray-900 mb-2 flex items-center gap-2"><Calendar class="w-4 h-4 text-blue-500" />Penjadwalan</h3>
                    <div>
                        <label class="text-[11px] font-medium text-gray-700 mb-1 block">Jadwal Publikasi</label>
                        <input v-model="form.scheduled_at" type="datetime-local" class="w-full px-2 py-1.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-xs">
                        <p class="text-[10px] text-gray-400 mt-0.5">Opsional - atur jadwal publikasi</p>
                    </div>
                </div>

                <div class="sidebar-section bg-white rounded-xl shadow-md border border-gray-100 p-3 space-y-2 hover:shadow-lg transition-shadow">
                    <label class="flex items-center gap-2 p-2 bg-blue-50 rounded-lg cursor-pointer hover:bg-blue-100 transition-colors">
                        <input v-model="autoSaveEnabled" type="checkbox" class="w-4 h-4 rounded text-blue-500 focus:ring-blue-500">
                        <span class="font-medium text-blue-700 flex items-center gap-1 text-[11px]"><Save class="w-3 h-3" />Auto-save</span>
                        <span v-if="autoSaveEnabled" class="ml-auto text-[10px] text-blue-500">Aktif</span>
                        <span v-else class="ml-auto text-[10px] text-gray-400">Nonaktif</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-yellow-50 rounded-lg cursor-pointer hover:bg-yellow-100 transition-colors">
                        <input v-model="form.is_featured" type="checkbox" class="w-4 h-4 rounded text-yellow-500 focus:ring-yellow-500">
                        <span class="font-medium text-yellow-700 flex items-center gap-1 text-[11px]"><Star class="w-3 h-3" />Jadikan Headline</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-purple-50 rounded-lg cursor-pointer hover:bg-purple-100 transition-colors">
                        <input v-model="form.is_pinned" type="checkbox" class="w-4 h-4 rounded text-purple-500 focus:ring-purple-500">
                        <span class="font-medium text-purple-700 flex items-center gap-1 text-[11px]"><Pin class="w-3 h-3" />Pin di Atas</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-green-50 rounded-lg cursor-pointer hover:bg-green-100 transition-colors">
                        <input v-model="form.is_published" type="checkbox" class="w-4 h-4 rounded text-green-500 focus:ring-green-500">
                        <span class="font-medium text-green-700 flex items-center gap-1 text-[11px]"><Eye class="w-3 h-3" />Publikasikan</span>
                    </label>
                </div>

                <div class="sidebar-section bg-white rounded-xl shadow-md border border-gray-100 p-3 hover:shadow-lg transition-shadow">
                    <button type="button" @click="loadRevisions" class="w-full flex items-center justify-center gap-2 py-1.5 text-xs font-medium text-gray-600 hover:text-red-600 transition-colors">
                        <History class="w-3.5 h-3.5" /> Riwayat Revisi ({{ news.revision_count || 0 }})
                    </button>
                </div>

                <div v-if="!isFormValid" class="flex items-center gap-1.5 px-2 py-1.5 bg-amber-50 border border-amber-200 rounded-lg text-[10px] text-amber-700">
                    <AlertTriangle class="w-3 h-3 flex-shrink-0" /><span>Lengkapi: Judul, Konten, Ringkasan, Penulis, SEO</span>
                </div>
            </div>
        </form>

        <!-- Revisions Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showRevisionsPanel" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><History class="w-4 h-4" /> Riwayat Revisi</h3>
                            <button @click="showRevisionsPanel = false" class="text-gray-400 hover:text-gray-600">✕</button>
                        </div>
                        <div class="p-3 max-h-80 overflow-y-auto space-y-2">
                            <div v-for="rev in revisions" :key="rev.id" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-900">{{ rev.title }}</p>
                                        <p class="text-[10px] text-gray-500">{{ formatDate(rev.created_at) }} oleh {{ rev.admin?.name || 'Admin' }}</p>
                                    </div>
                                    <button @click="restoreRevision(rev.id)" class="px-2 py-1 text-[10px] font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors">Pulihkan</button>
                                </div>
                            </div>
                            <p v-if="!revisions.length" class="text-center text-gray-500 py-6 text-xs">Belum ada revisi</p>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Topic Prompt Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showTopicModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-4">
                        <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><Wand2 class="w-4 h-4 text-violet-600" />Masukkan Topik</h3>
                        <p class="text-xs text-gray-500 mb-3">Masukkan topik untuk membuat judul berita dengan AI</p>
                        <input v-model="topicInput" type="text" placeholder="Contoh: Kegiatan konservasi di TNLL..."
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 text-sm mb-3" @keyup.enter="confirmGenerateTitle">
                        <div class="flex gap-2">
                            <button type="button" @click="showTopicModal = false" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors text-xs">Batal</button>
                            <button type="button" @click="confirmGenerateTitle" class="flex-1 px-3 py-2 bg-gradient-to-r from-violet-500 to-fuchsia-500 text-white font-bold rounded-lg hover:opacity-90 transition-all text-xs">Generate</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
        
        <ImageEditorModal :show="showImageEditor" :imageUrl="imagePreview" @close="showImageEditor = false" @save="handleEditedImage" />

        <!-- Floating Action Buttons -->
        <Transition name="float">
            <div v-if="showFloatingSave" class="fixed bottom-6 right-6 z-50 flex items-center gap-3">
                <Link href="/admin/news" 
                    class="flex items-center gap-2 px-4 py-3 bg-white text-gray-700 rounded-full shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all border border-gray-200">
                    <span class="font-bold text-sm">Batal</span>
                </Link>
                <button type="button" @click="submit" :disabled="form.processing || !isFormValid"
                    class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-full shadow-2xl shadow-red-500/40 hover:shadow-red-500/60 hover:-translate-y-1 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <Save class="w-5 h-5" /><span class="font-bold text-sm">{{ form.processing ? 'Menyimpan...' : 'Simpan Berita' }}</span>
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.float-enter-active, .float-leave-active { transition: all 0.3s ease; }
.float-enter-from, .float-leave-to { opacity: 0; transform: translateY(20px) scale(0.9); }
</style>
