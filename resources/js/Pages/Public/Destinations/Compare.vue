<script setup>
/**
 * Compare.vue - Destination Comparison Page
 * Design system: text-sm titles, text-[11px] text, w-3.5 icons
 */
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Repeat, Star, MapPin, Ticket, X, ChevronRight, ArrowLeft, Check, Minus, Compass } from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const heroRef = ref(null);
const destinations = ref([]);
const loading = ref(false);
let ctx;

const fetchDestinations = async () => {
    const ids = JSON.parse(localStorage.getItem('compareDestinations') || '[]');
    if (ids.length === 0) return;
    loading.value = true;
    try {
        const res = await fetch(`/api/destinations?ids=${ids.join(',')}`);
        const data = await res.json();
        destinations.value = data.data || [];
    } catch (e) {} finally { loading.value = false; }
};

const removeFromCompare = (id) => {
    const ids = JSON.parse(localStorage.getItem('compareDestinations') || '[]');
    localStorage.setItem('compareDestinations', JSON.stringify(ids.filter(i => i !== id)));
    destinations.value = destinations.value.filter(d => d.id !== id);
};

const clearAll = () => { localStorage.setItem('compareDestinations', '[]'); destinations.value = []; };

const formatPrice = (dest) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(dest?.prices?.[0]?.price || dest?.adult_price || 0);

onMounted(() => {
    fetchDestinations();
    ctx = gsap.context(() => {
        gsap.fromTo('.hero-content > *', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4, stagger: 0.1, ease: 'power2.out', delay: 0.2 });
    }, heroRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <Head><title>Bandingkan Destinasi - TNLL Explore</title></Head>

    <div class="min-h-screen bg-gray-50">
        <!-- Hero -->
        <section ref="heroRef" class="relative py-16 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-emerald-900 to-teal-900"></div>
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-1/3 -left-1/4 w-[400px] h-[400px] rounded-full bg-emerald-600/20 blur-[60px] animate-float-slow"></div>
                <div class="absolute -bottom-1/4 -right-1/4 w-[300px] h-[300px] rounded-full bg-cyan-600/15 blur-[50px] animate-float-medium"></div>
            </div>
            <svg class="absolute bottom-0 left-0 right-0 w-full h-12" viewBox="0 0 1440 60" fill="none" preserveAspectRatio="none">
                <path d="M0,30L60,27C120,24,240,18,360,20C480,22,600,32,720,35C840,38,960,34,1080,30C1200,26,1320,22,1380,20L1440,18L1440,60L0,60Z" fill="#f9fafb"/>
            </svg>
            
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center hero-content">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-xl border border-white/15 mb-3">
                    <Repeat class="w-3 h-3 text-emerald-400" />
                    <span class="text-[10px] font-semibold text-white/80 uppercase tracking-wide">Bandingkan</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-white mb-2">Bandingkan <span class="text-emerald-300">Destinasi</span></h1>
                <p class="text-white/60 text-xs sm:text-sm max-w-md mx-auto">Lihat perbandingan detail untuk memilih destinasi terbaik</p>
            </div>
        </section>

        <!-- Content -->
        <section class="py-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Actions -->
                <div class="flex items-center justify-between mb-6">
                    <Link href="/destinations" class="flex items-center gap-1.5 text-[11px] font-semibold text-gray-600 hover:text-emerald-600">
                        <ArrowLeft class="w-3.5 h-3.5" />
                        Kembali
                    </Link>
                    <button v-if="destinations.length > 0" @click="clearAll" class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-[10px] font-semibold hover:bg-red-100 transition-colors">
                        <X class="w-3 h-3" />
                        Hapus Semua
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="!loading && destinations.length === 0" class="text-center py-16">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center">
                        <Repeat class="w-8 h-8 text-emerald-500" />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Belum Ada Destinasi</h3>
                    <p class="text-[11px] text-gray-500 mb-4 max-w-sm mx-auto">Tambahkan destinasi ke perbandingan dari halaman destinasi</p>
                    <Link href="/destinations" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-bold rounded-xl shadow-lg">
                        <Compass class="w-4 h-4" />
                        Jelajahi Destinasi
                    </Link>
                </div>

                <!-- Comparison Grid -->
                <div v-else-if="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="dest in destinations" :key="dest.id" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden group">
                        <!-- Image -->
                        <div class="relative aspect-video overflow-hidden">
                            <img :src="dest.primary_image_url || '/images/placeholder-no-image.svg'" :alt="dest.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" @error="(e) => e.target.src = '/images/placeholder-no-image.svg'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <button @click="removeFromCompare(dest.id)" class="absolute top-2 right-2 w-7 h-7 rounded-lg bg-red-500/80 backdrop-blur-sm flex items-center justify-center text-white hover:bg-red-600 transition-colors">
                                <X class="w-4 h-4" />
                            </button>
                            <div class="absolute bottom-2 left-2 right-2">
                                <h3 class="text-sm font-bold text-white line-clamp-1">{{ dest.name }}</h3>
                                <div class="flex items-center gap-1 text-white/70 text-[10px]">
                                    <MapPin class="w-3 h-3" />
                                    {{ dest.city }}
                                </div>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="p-4 space-y-3">
                            <!-- Rating & Price -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-amber-50 text-amber-600 text-[10px] font-bold">
                                    <Star class="w-3 h-3 fill-current" />
                                    {{ dest.rating || '4.8' }}
                                </div>
                                <span class="text-sm font-bold text-emerald-600">{{ formatPrice(dest) }}</span>
                            </div>

                            <!-- Category -->
                            <div class="flex items-center gap-2 text-[11px]">
                                <span class="text-gray-500">Kategori:</span>
                                <span class="font-semibold text-gray-900">{{ dest.category?.name || 'Wisata' }}</span>
                            </div>

                            <!-- Facilities -->
                            <div>
                                <p class="text-[10px] text-gray-500 mb-1.5">Fasilitas:</p>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="facility in dest.facilities?.slice(0, 4)" :key="facility" class="flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-gray-100 text-[9px] text-gray-600">
                                        <Check class="w-2.5 h-2.5 text-emerald-500" />
                                        {{ facility }}
                                    </span>
                                    <span v-if="dest.facilities?.length > 4" class="px-1.5 py-0.5 rounded bg-gray-100 text-[9px] text-gray-500">+{{ dest.facilities.length - 4 }}</span>
                                </div>
                            </div>

                            <!-- CTA -->
                            <Link :href="`/destinations/${dest.slug}`" class="flex items-center justify-center gap-1 w-full py-2.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[11px] font-bold shadow-md">
                                Lihat Detail
                                <ChevronRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div v-else class="text-center py-16">
                    <div class="w-8 h-8 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto"></div>
                    <p class="text-[11px] text-gray-500 mt-3">Memuat data...</p>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes float-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
.animate-float-slow { animation: float-slow 8s ease-in-out infinite; }
@keyframes float-medium { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
.animate-float-medium { animation: float-medium 6s ease-in-out infinite; }
</style>
