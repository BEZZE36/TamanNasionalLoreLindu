<script setup>
import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { MapPin, ChevronRight, Crown, Medal, Award, TrendingUp } from 'lucide-vue-next';

const props = defineProps({
    destinations: Array,
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const items = document.querySelectorAll('.dest-item');
        if (items.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(items, 
                    { opacity: 0, x: -15 }, 
                    { opacity: 1, x: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const getRankStyle = (index) => {
    const styles = {
        0: { gradient: 'from-amber-400 to-yellow-500', shadow: 'shadow-amber-400/40', icon: Crown },
        1: { gradient: 'from-gray-300 to-gray-400', shadow: 'shadow-gray-300/40', icon: Medal },
        2: { gradient: 'from-amber-600 to-amber-700', shadow: 'shadow-amber-600/40', icon: Award },
    };
    return styles[index] || { gradient: 'from-violet-500 to-purple-600', shadow: 'shadow-violet-500/30', icon: null };
};

const maxBookings = () => {
    if (!props.destinations?.length) return 1;
    return Math.max(...props.destinations.map(d => d.bookings_count || 0), 1);
};
</script>

<template>
    <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-violet-50 to-purple-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <MapPin class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Destinasi Populer</h3>
                        <p class="text-[10px] text-gray-500">Top 5 berdasarkan booking</p>
                    </div>
                </div>
                <Link 
                    href="/admin/destinations" 
                    class="group flex items-center gap-1 text-[10px] font-semibold text-violet-600 hover:text-violet-700 transition-colors"
                >
                    <span>Semua</span>
                    <ChevronRight class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
        
        <!-- List -->
        <div class="p-4 space-y-2">
            <Link 
                v-for="(dest, index) in destinations?.slice(0, 5)" 
                :key="dest.id"
                :href="`/admin/destinations/${dest.id}/edit`"
                class="dest-item group flex items-center gap-3 p-2.5 rounded-xl hover:bg-gradient-to-r hover:from-violet-50 hover:to-purple-50 transition-all duration-300"
            >
                <!-- Rank Badge -->
                <div :class="[
                    'w-9 h-9 rounded-lg flex items-center justify-center font-black text-sm shadow-lg transition-transform group-hover:scale-110 bg-gradient-to-br text-white',
                    getRankStyle(index).gradient,
                    getRankStyle(index).shadow
                ]">
                    <component v-if="getRankStyle(index).icon && index < 3" :is="getRankStyle(index).icon" class="w-4 h-4" />
                    <span v-else>{{ index + 1 }}</span>
                </div>
                
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-gray-800 truncate group-hover:text-violet-600 transition-colors">{{ dest.name }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <!-- Progress Bar -->
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-violet-500 to-purple-600 rounded-full transition-all duration-500"
                                :style="{ width: `${((dest.bookings_count || 0) / maxBookings()) * 100}%` }"
                            ></div>
                        </div>
                        <span class="text-[10px] font-bold text-gray-600">{{ dest.bookings_count || 0 }}</span>
                    </div>
                </div>
                
                <!-- Arrow -->
                <ChevronRight class="w-4 h-4 text-gray-300 group-hover:text-violet-500 group-hover:translate-x-0.5 transition-all" />
            </Link>
            
            <!-- Empty State -->
            <div v-if="!destinations?.length" class="text-center py-6">
                <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                    <MapPin class="w-6 h-6 text-gray-400" />
                </div>
                <p class="text-xs font-semibold text-gray-600">Belum ada data</p>
                <p class="text-[10px] text-gray-400">Destinasi populer akan muncul di sini</p>
            </div>
        </div>
    </div>
</template>
