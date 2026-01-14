<script setup>
/**
 * DetailRules.vue - Enhanced Rules Section
 * With icon mapping, warning styling, collapsible list
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { AlertTriangle, ChevronDown, Ban, Camera, Volume2, Cigarette, Flame, Dog, Shirt, Trash2, Hand, Clock, Users, Car, Leaf, Fish, Footprints, Shield, Info, XCircle, AlertCircle } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ destination: { type: Object, required: true } });

const sectionRef = ref(null);
const isExpanded = ref(false);
let ctx;

const iconMap = {
    'sampah': Trash2, 'buang': Trash2, 'kotor': Trash2, 'limbah': Trash2,
    'coret': Ban, 'vandalisme': Ban, 'rusak': Ban,
    'foto': Camera, 'kamera': Camera, 'flash': Camera,
    'suara': Volume2, 'berisik': Volume2, 'teriak': Volume2, 'musik': Volume2,
    'rokok': Cigarette, 'merokok': Cigarette, 'asap': Cigarette,
    'api': Flame, 'bakar': Flame, 'asap': Flame, 'kompor': Flame,
    'hewan': Dog, 'peliharaan': Dog, 'anjing': Dog, 'kucing': Dog,
    'pakaian': Shirt, 'baju': Shirt, 'sopan': Shirt, 'busana': Shirt,
    'sentuh': Hand, 'pegang': Hand, 'cabut': Hand,
    'jam': Clock, 'waktu': Clock, 'tutup': Clock, 'buka': Clock,
    'rombongan': Users, 'kelompok': Users, 'grup': Users,
    'kendaraan': Car, 'parkir': Car, 'motor': Car, 'mobil': Car,
    'tanaman': Leaf, 'tumbuhan': Leaf, 'pohon': Leaf, 'bunga': Leaf,
    'ikan': Fish, 'memancing': Fish, 'tangkap': Fish,
    'jalur': Footprints, 'jalan': Footprints, 'trek': Footprints,
    'default': AlertCircle
};

const getIcon = (rule) => {
    const lower = rule.toLowerCase();
    for (const [key, icon] of Object.entries(iconMap)) {
        if (lower.includes(key)) return icon;
    }
    return iconMap.default;
};

const displayedRules = ref(props.destination.rules?.slice(0, 4) || []);
const hasMore = (props.destination.rules?.length || 0) > 4;

const toggle = () => {
    isExpanded.value = !isExpanded.value;
    displayedRules.value = isExpanded.value ? (props.destination.rules || []) : (props.destination.rules?.slice(0, 4) || []);
};

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.rule-item', { opacity: 0, x: -15 }, { opacity: 1, x: 0, duration: 0.4, stagger: 0.06, ease: 'power2.out', scrollTrigger: { trigger: sectionRef.value, start: 'top 85%' } });
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div v-if="destination.rules?.length > 0" ref="sectionRef" class="bg-gradient-to-br from-amber-50/50 via-orange-50/30 to-rose-50/50 rounded-2xl shadow-lg border border-amber-200/50 overflow-hidden">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-amber-200/50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                <AlertTriangle class="w-5 h-5 text-white" />
            </div>
            <div>
                <h2 class="text-sm font-bold text-gray-900">Peraturan Wisata</h2>
                <p class="text-[10px] text-gray-500">{{ destination.rules.length }} peraturan berlaku</p>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Warning Banner -->
            <div class="flex items-center gap-2.5 px-4 py-3 bg-amber-100/60 rounded-xl mb-4 border border-amber-200/60">
                <AlertTriangle class="w-4 h-4 text-amber-600 flex-shrink-0" />
                <p class="text-[10px] text-amber-800 font-medium">Harap patuhi semua peraturan demi kenyamanan dan keselamatan bersama</p>
            </div>

            <!-- Rules List -->
            <ul class="space-y-2">
                <li v-for="(rule, idx) in displayedRules" :key="idx" class="rule-item group flex items-start gap-2.5 p-3 rounded-xl bg-white/70 hover:bg-white border border-amber-100/50 hover:border-amber-200 transition-all duration-300">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center flex-shrink-0 group-hover:from-amber-200 group-hover:to-orange-200 transition-colors duration-300">
                        <component :is="getIcon(rule)" class="w-3.5 h-3.5 text-amber-600" />
                    </div>
                    <span class="text-[11px] text-gray-700 leading-relaxed">{{ rule }}</span>
                </li>
            </ul>

            <!-- Show More Button -->
            <button v-if="hasMore" @click="toggle" class="mt-4 w-full flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-amber-100/50 hover:bg-amber-100 text-amber-700 text-[10px] font-semibold transition-all duration-300">
                <span>{{ isExpanded ? 'Tampilkan Lebih Sedikit' : `Lihat ${destination.rules.length - 4} Aturan Lainnya` }}</span>
                <ChevronDown :class="['w-4 h-4 transition-transform duration-300', isExpanded && 'rotate-180']" />
            </button>
        </div>
    </div>
</template>
