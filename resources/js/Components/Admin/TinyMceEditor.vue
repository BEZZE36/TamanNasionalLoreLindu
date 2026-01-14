<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Zap, RefreshCw, Maximize2, Minus, Search, X, Sparkles, Languages } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: String, default: '' },
    name: { type: String, default: 'content' },
    id: { type: String, default: 'editor' },
    height: { type: Number, default: 450 },
    placeholder: { type: String, default: 'Mulai menulis...' }
});

const emit = defineEmits(['update:modelValue']);

const page = usePage();
const loading = ref(false);
const panelOpen = ref(false);
const panelMode = ref('');
const panelTitle = ref('');
const panelSubtitle = ref('');
const generateTopic = ref('');
const generateType = ref('description');
const rewriteStyle = ref('formal');
const errorMessage = ref('');
const seoResultsOpen = ref(false);
const seoResults = ref('');
const toastMessage = ref('');
const toastType = ref('success');
const wordCount = ref(0);

let editorInstance = null;

const initTinyMCE = () => {
    if (typeof window.tinymce === 'undefined') {
        console.error('TinyMCE not loaded');
        return;
    }
    
    if (window.tinymce.get(props.id)) {
        window.tinymce.get(props.id).remove();
    }
    
    window.tinymce.init({
        selector: `#${props.id}`,
        height: props.height,
        menubar: 'edit view insert format tools table help',
        plugins: 'preview searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
        toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | charmap emoticons | fullscreen preview | image media link anchor codesample',
        toolbar_sticky: true,
        skin: 'oxide',
        content_style: `body { font-family: 'Plus Jakarta Sans', Inter, system-ui, sans-serif; font-size: 16px; line-height: 1.8; color: #1e293b; padding: 1.5rem; }`,
        placeholder: props.placeholder,
        setup: (editor) => {
            editorInstance = editor;
            editor.on('init', () => {
                editor.setContent(props.modelValue || '');
            });
            editor.on('change keyup blur', () => {
                emit('update:modelValue', editor.getContent());
            });
            editor.on('WordCountUpdate', (e) => {
                wordCount.value = e.wordCount.words;
            });
        }
    });
};

onMounted(() => {
    // Load TinyMCE script if not already loaded
    if (!window.tinymce) {
        const script = document.createElement('script');
        script.src = `https://cdn.tiny.cloud/1/${page.props.tinymce_api_key || 'no-api-key'}/tinymce/6/tinymce.min.js`;
        script.referrerPolicy = 'origin';
        script.onload = () => initTinyMCE();
        document.head.appendChild(script);
    } else {
        initTinyMCE();
    }
});

onUnmounted(() => {
    if (editorInstance) {
        editorInstance.remove();
    }
});

watch(() => props.modelValue, (newVal) => {
    if (editorInstance && editorInstance.getContent() !== newVal) {
        editorInstance.setContent(newVal || '');
    }
});

const showToast = (msg, type = 'success') => {
    toastMessage.value = msg;
    toastType.value = type;
    setTimeout(() => toastMessage.value = '', 3000);
};

const formatAIContent = (content) => {
    return content
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>');
};

const openAIPanel = (mode) => {
    panelMode.value = mode;
    errorMessage.value = '';
    if (mode === 'generate') {
        panelTitle.value = 'Studio Generasi AI';
        panelSubtitle.value = 'Buat konten berkualitas tinggi dengan AI';
    } else if (mode === 'rewrite') {
        panelTitle.value = 'Editor Tulis Ulang AI';
        panelSubtitle.value = 'Sempurnakan gaya bahasa';
    }
    panelOpen.value = true;
};

const closePanel = () => { panelOpen.value = false; errorMessage.value = ''; };

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

const executeAI = async () => {
    if (panelMode.value === 'generate') await generateContent();
    else if (panelMode.value === 'rewrite') await rewriteContent();
};

const generateContent = async () => {
    if (!generateTopic.value.trim()) { errorMessage.value = 'Topik tidak boleh kosong!'; return; }
    loading.value = true; errorMessage.value = '';
    try {
        const res = await fetch('/admin/ai/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ prompt: generateTopic.value, type: generateType.value })
        });
        const data = await res.json();
        if (data.success && data.content) {
            editorInstance.insertContent(formatAIContent(data.content));
            closePanel(); generateTopic.value = ''; showToast('Konten berhasil dibuat!');
        } else { errorMessage.value = data.error || 'Generasi gagal.'; }
    } catch { errorMessage.value = 'Terjadi kesalahan jaringan.'; }
    finally { loading.value = false; }
};

const rewriteContent = async () => {
    const selectedText = editorInstance.selection.getContent({ format: 'text' });
    if (!selectedText.trim()) { errorMessage.value = 'Pilih teks yang ingin ditulis ulang.'; return; }
    loading.value = true;
    try {
        const res = await fetch('/admin/ai/rewrite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ content: selectedText, style: rewriteStyle.value })
        });
        const data = await res.json();
        if (data.success && data.content) {
            editorInstance.selection.setContent(formatAIContent(data.content));
            closePanel(); showToast('Rewrite berhasil!');
        } else { errorMessage.value = data.error || 'Rewrite gagal.'; }
    } catch { errorMessage.value = 'Terjadi kesalahan jaringan.'; }
    finally { loading.value = false; }
};

const executeDirectAction = async (action, options = {}) => {
    // For translation, use HTML format to preserve formatting
    const isTranslate = action === 'translate_id_en' || action === 'translate_en_id';
    const format = isTranslate ? 'html' : 'text';
    
    const selectedContent = editorInstance.selection.getContent({ format });
    const allContent = editorInstance.getContent({ format });
    const content = selectedContent.trim() ? selectedContent : allContent;
    if (!content.trim()) { showToast('Tambahkan konten terlebih dahulu', 'error'); return; }
    loading.value = true;
    try {
        // Map action to URL
        let url = '/admin/ai/' + action;
        let bodyData = { content };
        
        if (action === 'translate_id_en') {
            url = '/admin/ai/translate';
            bodyData.direction = 'id_to_en';
        } else if (action === 'translate_en_id') {
            url = '/admin/ai/translate';
            bodyData.direction = 'en_to_id';
        }
        
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify(bodyData)
        });
        const data = await res.json();
        if (data.success && data.content) {
            if (action === 'seo') { seoResults.value = formatAIContent(data.content); seoResultsOpen.value = true; }
            else {
                // For translation, don't format - it's already HTML
                const resultContent = isTranslate ? data.content : formatAIContent(data.content);
                if (selectedContent.trim()) editorInstance.selection.setContent(resultContent);
                else editorInstance.setContent(resultContent);
                showToast('AI berhasil!');
            }
        } else { showToast(data.error || 'AI gagal', 'error'); }
    } catch { showToast('Terjadi kesalahan jaringan', 'error'); }
    finally { loading.value = false; }
};
</script>

<template>
    <div class="tinymce-ai-wrapper relative">
        <!-- AI Toolbar -->
        <div class="ai-toolbar relative rounded-t-2xl overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-violet-900 to-slate-900"></div>
            <div class="relative p-4 flex flex-col gap-3">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                    <button type="button" @click="openAIPanel('generate')" class="ai-btn from-emerald-400 to-cyan-500">
                        <Zap class="w-4 h-4" /><span>Generate</span>
                    </button>
                    <button type="button" @click="openAIPanel('rewrite')" class="ai-btn from-blue-400 to-indigo-500">
                        <RefreshCw class="w-4 h-4" /><span>Rewrite</span>
                    </button>
                    <button type="button" @click="executeDirectAction('expand')" class="ai-btn from-amber-400 to-orange-500">
                        <Maximize2 class="w-4 h-4" /><span>Expand</span>
                    </button>
                    <button type="button" @click="executeDirectAction('shorten')" class="ai-btn from-pink-400 to-rose-500">
                        <Minus class="w-4 h-4" /><span>Shorten</span>
                    </button>
                    <button type="button" @click="executeDirectAction('translate_id_en')" class="ai-btn from-teal-400 to-cyan-500">
                        <Languages class="w-4 h-4" /><span>ID → EN</span>
                    </button>
                    <button type="button" @click="executeDirectAction('translate_en_id')" class="ai-btn from-cyan-400 to-blue-500">
                        <Languages class="w-4 h-4" /><span>EN → ID</span>
                    </button>
                    <button type="button" @click="executeDirectAction('seo')" class="ai-btn from-purple-400 to-violet-500">
                        <Search class="w-4 h-4" /><span>SEO</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- TinyMCE Editor -->
        <div class="editor-container relative bg-white overflow-hidden border-2 border-t-0 border-gray-100 shadow-xl">
            <textarea :id="id" :name="name"></textarea>
            <!-- AI Loading Skeleton Overlay - Plain Design -->
            <div v-if="loading" class="absolute inset-0 bg-white/95 backdrop-blur-sm z-50 flex items-center justify-center p-8">
                <div class="w-full max-w-lg space-y-4">
                    <div class="skeleton-plain h-5 w-3/4 rounded"></div>
                    <div class="skeleton-plain h-4 w-full rounded"></div>
                    <div class="skeleton-plain h-4 w-11/12 rounded"></div>
                    <div class="skeleton-plain h-4 w-full rounded"></div>
                    <div class="skeleton-plain h-4 w-4/5 rounded"></div>
                    <div class="skeleton-plain h-4 w-full rounded"></div>
                    <div class="skeleton-plain h-4 w-9/12 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-slate-50 to-gray-50 border-2 border-t-0 border-gray-100 rounded-b-2xl">
            <div class="text-xs text-gray-400">
                <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-[10px] font-mono border border-gray-200">Alt+0</kbd> Help
            </div>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                <span class="text-sm font-bold text-gray-600">{{ wordCount }} words</span>
            </div>
        </div>

        <!-- AI Panel Modal -->
        <div v-if="panelOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closePanel">
            <div class="bg-gradient-to-br from-slate-900 via-violet-900/90 to-slate-900 rounded-3xl p-8 w-full max-w-lg shadow-2xl border border-white/10">
                <div class="flex items-center justify-between mb-6">
                    <div><h3 class="text-xl font-bold text-white">{{ panelTitle }}</h3><p class="text-white/60 text-sm">{{ panelSubtitle }}</p></div>
                    <button @click="closePanel" class="text-white/40 hover:text-white"><X class="w-6 h-6" /></button>
                </div>
                <div v-if="panelMode === 'generate'" class="space-y-4">
                    <input v-model="generateTopic" type="text" placeholder="Masukkan topik..."
                        class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/40 focus:border-violet-400">
                    <div class="flex gap-2">
                        <button type="button" @click="generateType = 'description'" :class="['flex-1 py-2 rounded-lg border text-white text-sm font-medium', generateType === 'description' ? 'bg-violet-600 border-violet-500' : 'bg-white/5 border-white/10']">Deskripsi</button>
                        <button type="button" @click="generateType = 'news'" :class="['flex-1 py-2 rounded-lg border text-white text-sm font-medium', generateType === 'news' ? 'bg-cyan-600 border-cyan-500' : 'bg-white/5 border-white/10']">Berita</button>
                        <button type="button" @click="generateType = 'article'" :class="['flex-1 py-2 rounded-lg border text-white text-sm font-medium', generateType === 'article' ? 'bg-emerald-600 border-emerald-500' : 'bg-white/5 border-white/10']">Artikel</button>
                    </div>
                </div>
                <div v-if="panelMode === 'rewrite'" class="space-y-4">
                    <p class="text-white/60 text-sm">Pilih teks di editor, lalu pilih gaya:</p>
                    <div class="flex gap-2">
                        <button type="button" @click="rewriteStyle = 'formal'" :class="['flex-1 py-2 rounded-lg border text-white text-sm font-medium', rewriteStyle === 'formal' ? 'bg-violet-600 border-violet-500' : 'bg-white/5 border-white/10']">Formal</button>
                        <button type="button" @click="rewriteStyle = 'casual'" :class="['flex-1 py-2 rounded-lg border text-white text-sm font-medium', rewriteStyle === 'casual' ? 'bg-violet-600 border-violet-500' : 'bg-white/5 border-white/10']">Casual</button>
                        <button type="button" @click="rewriteStyle = 'seo'" :class="['flex-1 py-2 rounded-lg border text-white text-sm font-medium', rewriteStyle === 'seo' ? 'bg-violet-600 border-violet-500' : 'bg-white/5 border-white/10']">SEO</button>
                    </div>
                </div>
                <div v-if="errorMessage" class="mt-4 p-3 rounded-lg bg-red-500/20 border border-red-500/30 text-red-300 text-sm">{{ errorMessage }}</div>
                <button type="button" @click="executeAI" :disabled="loading"
                    class="w-full mt-6 py-3 rounded-xl bg-gradient-to-r from-violet-500 to-fuchsia-500 text-white font-bold hover:opacity-90 disabled:opacity-50">
                    {{ loading ? 'Memproses...' : 'Mulai' }}
                </button>
            </div>
        </div>

        <!-- SEO Results Modal -->
        <div v-if="seoResultsOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="seoResultsOpen = false">
            <div class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[80vh] overflow-y-auto shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Hasil Analisis SEO</h3>
                    <button @click="seoResultsOpen = false" class="text-gray-400 hover:text-gray-600"><X class="w-6 h-6" /></button>
                </div>
                <div class="prose prose-sm max-w-none" v-html="seoResults"></div>
            </div>
        </div>

        <!-- Toast -->
        <div v-if="toastMessage"
            class="fixed bottom-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg transition-all"
            :class="toastType === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'">
            {{ toastMessage }}
        </div>
    </div>
</template>

<style scoped>
.ai-btn {
    display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.875rem;
    border-radius: 0.625rem; font-size: 0.75rem; font-weight: 600; color: white;
    transition: all 0.2s; border: 1px solid rgba(255,255,255,0.2);
    background: linear-gradient(to right, var(--tw-gradient-stops));
}
.ai-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

/* Premium Skeleton Animations */
@keyframes float-slow {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}

@keyframes glow-ring {
    0%, 100% { transform: scale(1); opacity: 0.4; }
    50% { transform: scale(1.15); opacity: 0.6; }
}

@keyframes breathe {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.03); }
}

@keyframes neural {
    0%, 100% { opacity: 0.5; transform: scale(0.8); }
    50% { opacity: 1; transform: scale(1.1); }
}

@keyframes core-pulse {
    0%, 100% { opacity: 0.9; transform: scale(1); box-shadow: 0 0 10px rgba(255,255,255,0.5); }
    50% { opacity: 1; transform: scale(1.2); box-shadow: 0 0 20px rgba(255,255,255,0.8); }
}

@keyframes progress-wave {
    0% { width: 0%; }
    25% { width: 40%; }
    50% { width: 65%; }
    75% { width: 85%; }
    100% { width: 100%; }
}

@keyframes shimmer-fast {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes bounce-stagger {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-5px); }
}

@keyframes premium-skeleton {
    0% { background-position: -200% 0; opacity: 0.6; }
    50% { opacity: 1; }
    100% { background-position: 200% 0; opacity: 0.6; }
}

.animate-float-slow { animation: float-slow 6s ease-in-out infinite; }
.animate-glow-ring { animation: glow-ring 2s ease-in-out infinite; }
.animate-breathe { animation: breathe 2.5s ease-in-out infinite; }
.animate-neural { animation: neural 1.5s ease-in-out infinite; }
.animate-core-pulse { animation: core-pulse 1.5s ease-in-out infinite; }
.animate-progress-wave { animation: progress-wave 2.5s ease-in-out infinite; }
.animate-shimmer-fast { animation: shimmer-fast 1.5s ease-in-out infinite; }
.animate-bounce-stagger { animation: bounce-stagger 1s ease-in-out infinite; }

.skeleton-premium {
    background: linear-gradient(90deg, 
        rgba(139, 92, 246, 0.15) 0%, 
        rgba(217, 70, 239, 0.25) 25%, 
        rgba(236, 72, 153, 0.15) 50%,
        rgba(217, 70, 239, 0.25) 75%,
        rgba(139, 92, 246, 0.15) 100%);
    background-size: 200% 100%;
    animation: premium-skeleton 2s ease-in-out infinite;
}

@keyframes plain-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.skeleton-plain {
    background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
    background-size: 200% 100%;
    animation: plain-shimmer 1.5s ease-in-out infinite;
}
</style>
