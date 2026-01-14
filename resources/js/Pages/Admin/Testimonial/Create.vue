<script setup>
/**
 * Admin Testimonial Create - Premium Design
 * Features: GSAP animations, glassmorphism, interactive star rating
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    ArrowLeft, Save, Quote, Star, MapPin, Power, Eye, 
    User, MessageSquare, Sparkles, Loader2
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ destinations: { type: Array, default: () => [] } });

const form = useForm({
    display_name: '',
    destination_id: '',
    message: '',
    rating: 5,
    status: 'unread',
    is_published: false
});

const hoverRating = ref(0);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 20, scale: 0.98 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.4)' }
        );
        gsap.fromTo('.header-content', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, delay: 0.2, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const setRating = (value) => { form.rating = value; };
const submit = () => { form.post('/admin/testimonial'); };

const getRatingEmoji = (rating) => {
    const emojis = ['😞', '😕', '😐', '🙂', '😍'];
    return emojis[rating - 1] || '😐';
};
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-4">
        <!-- Premium Header -->
        <div class="form-card relative overflow-hidden rounded-xl bg-gradient-to-r from-pink-600 via-fuchsia-600 to-purple-600 p-4 shadow-xl">
            <!-- Animated Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl animate-pulse"></div>
                <div class="absolute bottom-0 left-1/4 w-24 h-24 bg-purple-400/20 rounded-full blur-xl animate-pulse" style="animation-delay: 0.5s"></div>
            </div>
            
            <!-- Floating Particles -->
            <div class="absolute top-3 right-16 w-1.5 h-1.5 bg-white/40 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute bottom-4 right-1/3 w-1 h-1 bg-pink-300/50 rounded-full animate-bounce" style="animation-delay: 0.6s"></div>
            
            <div class="header-content relative flex items-center gap-3">
                <Link href="/admin/testimonial" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm text-white hover:bg-white/30 transition-all hover:scale-105">
                    <ArrowLeft class="w-4 h-4" />
                </Link>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm shadow-lg">
                        <Quote class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white tracking-tight">Tambah Testimonial</h1>
                        <p class="text-[10px] text-pink-100/80">Buat testimonial secara manual</p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
            <!-- Info Card -->
            <div class="form-card bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-pink-50 to-fuchsia-50 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <MessageSquare class="w-4 h-4 text-pink-500" />
                        Informasi Testimonial
                    </h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                <User class="w-3 h-3 inline mr-1" />Nama Pengirim <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.display_name" 
                                type="text" 
                                required 
                                placeholder="Nama pengunjung"
                                class="w-full px-3 py-2 text-xs rounded-lg border-2 border-gray-200 bg-gray-50/50 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/10 focus:bg-white transition-all"
                            >
                            <p v-if="form.errors.display_name" class="text-[10px] text-red-500 mt-1">{{ form.errors.display_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                <MapPin class="w-3 h-3 inline mr-1" />Destinasi
                            </label>
                            <select 
                                v-model="form.destination_id" 
                                class="w-full px-3 py-2 text-xs rounded-lg border-2 border-gray-200 bg-gray-50/50 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/10 focus:bg-white transition-all cursor-pointer"
                            >
                                <option value="">Pilih Destinasi (Opsional)</option>
                                <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">
                            Pesan Testimonial <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            v-model="form.message" 
                            rows="3" 
                            required 
                            placeholder="Tulis pesan testimonial..."
                            class="w-full px-3 py-2 text-xs rounded-lg border-2 border-gray-200 bg-gray-50/50 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/10 focus:bg-white transition-all resize-none"
                        ></textarea>
                        <p v-if="form.errors.message" class="text-[10px] text-red-500 mt-1">{{ form.errors.message }}</p>
                    </div>
                    
                    <!-- Interactive Star Rating -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">
                            <Star class="w-3 h-3 inline mr-1" />Rating <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="flex gap-1">
                                <button
                                    v-for="i in 5"
                                    :key="i"
                                    type="button"
                                    @click="setRating(i)"
                                    @mouseenter="hoverRating = i"
                                    @mouseleave="hoverRating = 0"
                                    class="group relative p-1 transition-all duration-200 hover:scale-125"
                                >
                                    <Star 
                                        :class="[
                                            'w-6 h-6 transition-all duration-200',
                                            i <= (hoverRating || form.rating) 
                                                ? 'text-amber-400 fill-amber-400 drop-shadow-lg' 
                                                : 'text-gray-300 group-hover:text-amber-200'
                                        ]"
                                    />
                                    <div 
                                        v-if="i <= (hoverRating || form.rating)"
                                        class="absolute inset-0 bg-amber-400/20 rounded-full blur-md -z-10"
                                    ></div>
                                </button>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-lg border border-amber-100">
                                <span class="text-lg">{{ getRatingEmoji(hoverRating || form.rating) }}</span>
                                <span class="text-xs font-bold text-amber-700">{{ hoverRating || form.rating }}/5</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="form-card bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <Power class="w-4 h-4 text-blue-500" />
                        Status & Publikasi
                    </h2>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Status</label>
                        <select 
                            v-model="form.status" 
                            class="w-full px-3 py-2 text-xs rounded-lg border-2 border-gray-200 bg-gray-50/50 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:bg-white transition-all cursor-pointer"
                        >
                            <option value="unread">Belum Dibaca</option>
                            <option value="read">Sudah Dibaca</option>
                            <option value="replied">Sudah Dibalas</option>
                        </select>
                    </div>
                    
                    <label 
                        :class="[
                            'flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all duration-300',
                            form.is_published 
                                ? 'border-emerald-300 bg-gradient-to-r from-emerald-50 to-teal-50 shadow-lg shadow-emerald-100' 
                                : 'border-gray-200 bg-gray-50/50 hover:border-gray-300'
                        ]"
                    >
                        <div :class="[
                            'relative w-10 h-5 rounded-full transition-all duration-300',
                            form.is_published ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gray-300'
                        ]">
                            <div :class="[
                                'absolute top-0.5 w-4 h-4 bg-white rounded-full shadow-md transition-all duration-300',
                                form.is_published ? 'left-5' : 'left-0.5'
                            ]"></div>
                        </div>
                        <input type="checkbox" v-model="form.is_published" class="sr-only">
                        <Eye :class="['w-4 h-4 transition-colors', form.is_published ? 'text-emerald-500' : 'text-gray-400']" />
                        <div>
                            <span :class="['text-xs font-semibold', form.is_published ? 'text-emerald-700' : 'text-gray-600']">
                                Tampilkan ke Publik
                            </span>
                            <p class="text-[10px] text-gray-500">Testimonial akan ditampilkan di website</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-card flex items-center justify-between pt-2">
                <Link 
                    href="/admin/testimonial" 
                    class="px-4 py-2 text-xs font-medium text-gray-600 hover:text-gray-900 transition-colors"
                >
                    Batal
                </Link>
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white text-xs font-bold shadow-lg shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                >
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4 group-hover:scale-110 transition-transform" />
                    <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Testimonial' }}</span>
                    <Sparkles class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                </button>
            </div>
        </form>
    </div>
</template>
