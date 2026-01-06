<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TinyMceEditor from '@/Components/Admin/TinyMceEditor.vue';
import { gsap } from 'gsap';
import { 
    Send, Save, Eye, ArrowLeft, Mail, Users, AlertCircle, Sparkles, Zap
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    activeSubscribers: Number,
});

let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const form = useForm({
    subject: 'Update TNLL Explore — Alam, Budaya, dan Konservasi',
    content: '',
});

const submit = () => {
    form.post('/admin/newsletter/campaigns', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 p-6 shadow-2xl">
            <!-- Animated Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-center gap-4">
                <Link 
                    href="/admin/newsletter/campaigns"
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
                        <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Buat Campaign Baru</h1>
                        <Sparkles class="w-4 h-4 text-amber-400" />
                    </div>
                    <p class="text-purple-100/80 text-xs">Compose newsletter untuk subscriber Anda</p>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="form-card rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/60 p-5 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30">
                    <Users class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="font-bold text-blue-800 text-sm">{{ activeSubscribers }} Subscriber Aktif</p>
                    <p class="text-xs text-blue-600">Newsletter akan dikirim ke semua subscriber aktif</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
            <!-- Subject -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 p-5 hover:shadow-xl transition-shadow">
                <label class="block text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                        <Mail class="w-3.5 h-3.5 text-white" />
                    </div>
                    Subjek Email
                </label>
                <input 
                    type="text"
                    v-model="form.subject"
                    placeholder="Masukkan subjek email..."
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 focus:bg-white transition-all text-sm font-medium bg-gray-50/50"
                    :class="{ 'border-red-400': form.errors.subject }"
                    required
                />
                <p v-if="form.errors.subject" class="mt-2 text-xs text-red-600 flex items-center gap-1">
                    <AlertCircle class="w-3.5 h-3.5" />
                    {{ form.errors.subject }}
                </p>
            </div>

            <!-- Content -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 p-5 hover:shadow-xl transition-shadow">
                <label class="block text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                        <Zap class="w-3.5 h-3.5 text-white" />
                    </div>
                    Konten Newsletter
                </label>
                <TinyMceEditor 
                    v-model="form.content"
                    :height="400"
                    placeholder="Tulis konten newsletter Anda di sini..."
                />
                <p v-if="form.errors.content" class="mt-2 text-xs text-red-600 flex items-center gap-1">
                    <AlertCircle class="w-3.5 h-3.5" />
                    {{ form.errors.content }}
                </p>
            </div>

            <!-- Actions -->
            <div class="form-card flex flex-col sm:flex-row gap-3 justify-end">
                <Link 
                    href="/admin/newsletter/campaigns"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all"
                >
                    Batal
                </Link>
                <button 
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-xs font-bold hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-50 shadow-lg"
                >
                    <Save v-if="!form.processing" class="w-4 h-4" />
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Draft</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-300" />
                </button>
            </div>
        </form>
    </div>
</template>
