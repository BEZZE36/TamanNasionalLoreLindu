<script setup>
/**
 * DetailReviews.vue - Reviews Section
 * Design system: text-sm title, text-[11px] text, w-3.5 icons
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Star, MessageSquare, User, Send } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ destination: { type: Object, required: true } });
const page = usePage();
const user = page.props.auth?.user;
const sectionRef = ref(null);
let ctx;

const form = useForm({ rating: 5, comment: '' });
const submit = () => { form.post(`/destinations/${props.destination.slug}/reviews`, { preserveScroll: true, onSuccess: () => form.reset() }); };

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.review-card', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out', scrollTrigger: { trigger: sectionRef.value, start: 'top 80%' } });
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div ref="sectionRef" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                    <MessageSquare class="w-4 h-4 text-amber-600" />
                </div>
                <h2 class="text-sm font-bold text-gray-900">Ulasan Pengunjung</h2>
            </div>
            <!-- Rating Summary -->
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-100">
                <Star class="w-4 h-4 text-amber-500 fill-amber-500" />
                <span class="text-sm font-bold text-amber-700">{{ (parseFloat(destination.rating) || 4.8).toFixed(1) }}</span>
                <span class="text-[10px] text-amber-600/70">({{ destination.review_count || destination.reviews?.length || 0 }})</span>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="p-4 space-y-3 max-h-[400px] overflow-y-auto">
            <div v-for="review in destination.reviews?.slice(0, 5)" :key="review.id" class="review-card p-3 rounded-xl bg-gray-50 border border-gray-100">
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-[11px] flex-shrink-0">
                        {{ review.user?.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[11px] font-semibold text-gray-900 truncate">{{ review.user?.name || 'User' }}</span>
                            <div class="flex items-center gap-0.5">
                                <Star v-for="i in 5" :key="i" :class="['w-3 h-3', i <= review.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200']" />
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-600 leading-relaxed">{{ review.comment }}</p>
                        <p class="text-[9px] text-gray-400 mt-1.5">{{ new Date(review.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                    </div>
                </div>
            </div>
            
            <div v-if="!destination.reviews?.length" class="text-center py-8">
                <MessageSquare class="w-10 h-10 text-gray-200 mx-auto mb-2" />
                <p class="text-[11px] text-gray-400">Belum ada ulasan</p>
            </div>
        </div>

        <!-- Review Form -->
        <div v-if="user" class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 border-t border-gray-100">
            <p class="text-[11px] font-semibold text-gray-700 mb-2.5">Tulis Ulasan</p>
            
            <form @submit.prevent="submit">
                <!-- Rating -->
                <div class="flex items-center gap-1 mb-2.5">
                    <button v-for="i in 5" :key="i" type="button" @click="form.rating = i" class="focus:outline-none">
                        <Star :class="['w-5 h-5 transition-colors', i <= form.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-300 hover:text-amber-200']" />
                    </button>
                </div>

                <!-- Comment -->
                <div class="flex gap-2">
                    <input v-model="form.comment" type="text" placeholder="Bagikan pengalaman Anda..." class="flex-1 px-3 py-2.5 rounded-lg border border-gray-200 text-[11px] focus:outline-none focus:border-emerald-400 bg-white" required>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 text-white disabled:opacity-50">
                        <Send class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
        <div v-else class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 border-t border-gray-100 text-center">
            <p class="text-[11px] text-gray-500 mb-2">Login untuk menulis ulasan</p>
            <a href="/login" class="text-[11px] font-semibold text-emerald-600 hover:underline">Login Sekarang</a>
        </div>
    </div>
</template>
