<script setup>
/**
 * DetailDescription.vue - Rich Description with Premium Styling
 * Shows description from TinyMCE with proper prose formatting
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { FileText, Info, Quote } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ destination: { type: Object, required: true } });
const contentRef = ref(null);
let ctx;

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo(contentRef.value, { opacity: 0, y: 25 }, { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out', scrollTrigger: { trigger: contentRef.value, start: 'top 85%' } });
    }, contentRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div ref="contentRef" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <FileText class="w-5 h-5 text-white" />
            </div>
            <div>
                <h2 class="text-sm font-bold text-gray-900">Tentang Destinasi</h2>
                <p class="text-[10px] text-gray-500">Deskripsi lengkap</p>
            </div>
        </div>

        <!-- Short Description Quote -->
        <div v-if="destination.short_description" class="px-5 pt-5">
            <div class="relative p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100">
                <Quote class="absolute top-3 left-3 w-6 h-6 text-emerald-200" />
                <p class="text-[11px] text-emerald-800 font-medium leading-relaxed pl-6 italic">
                    "{{ destination.short_description }}"
                </p>
            </div>
        </div>

        <!-- Full Description (from TinyMCE) -->
        <div class="p-5">
            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed prose-headings:text-gray-900 prose-headings:font-bold prose-headings:text-sm prose-p:text-[11px] prose-p:leading-relaxed prose-ul:text-[11px] prose-ol:text-[11px] prose-li:text-gray-600 prose-strong:text-gray-900 prose-a:text-emerald-600 prose-img:rounded-xl prose-img:shadow-lg" v-html="destination.description"></div>
            
            <!-- Source Note -->
            <div class="flex items-center gap-2 mt-5 pt-4 border-t border-gray-100">
                <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <Info class="w-3 h-3 text-emerald-600" />
                </div>
                <span class="text-[10px] text-gray-400 font-medium">Informasi resmi dari Balai Besar Taman Nasional Lore Lindu</span>
            </div>
        </div>
    </div>
</template>
