<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import { gsap } from 'gsap';
import { Shield, Save, CheckCircle, Loader2, FileText, Eye } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ content: { type: String, default: '' } });

const page = usePage();
const flash = computed(() => page.props.flash || {});

const form = useForm({ content: props.content });
const showPreview = ref(false);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.section-card', 
            { opacity: 0, y: 20, scale: 0.98 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const submit = () => {
    form.put('/admin/site-info/privacy', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 via-cyan-600 to-emerald-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-cyan-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-cyan-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Shield class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Kebijakan Privasi</h1>
                        <p class="text-cyan-100/80 text-xs">Edit konten halaman kebijakan privasi website</p>
                    </div>
                </div>

                <!-- Preview Toggle -->
                <button 
                    type="button"
                    @click="showPreview = !showPreview"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 backdrop-blur-sm text-white text-xs font-semibold hover:bg-white/30 transition-all"
                >
                    <Eye class="w-4 h-4" />
                    {{ showPreview ? 'Tutup Preview' : 'Lihat Preview' }}
                </button>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Editor Section -->
            <div class="section-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-teal-50 via-cyan-50 to-teal-50 px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg shadow-teal-500/30">
                            <FileText class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Editor Konten</h2>
                            <p class="text-[10px] text-gray-500">Gunakan TinyMCE untuk format teks kaya</p>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <TinyMceEditor v-model="form.content" id="privacy-editor" :height="400" placeholder="Tulis kebijakan privasi di sini..." />
                </div>
            </div>

            <!-- Preview Section -->
            <Transition name="fade">
                <div v-if="showPreview" class="section-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-gray-500 to-gray-600 shadow-lg">
                                <Eye class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Preview Konten</h2>
                                <p class="text-[10px] text-gray-500">Tampilan di halaman publik</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="prose prose-sm max-w-none" v-html="form.content"></div>
                    </div>
                </div>
            </Transition>

            <!-- Save Button -->
            <div class="sticky bottom-4 flex justify-end z-20">
                <button type="submit" :disabled="form.processing"
                    class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 via-cyan-600 to-emerald-600 px-6 py-3 text-white text-xs font-bold shadow-2xl shadow-teal-500/40 hover:shadow-teal-500/60 hover:-translate-y-0.5 hover:scale-105 transition-all duration-300 disabled:opacity-50">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
            </div>
        </form>
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
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
