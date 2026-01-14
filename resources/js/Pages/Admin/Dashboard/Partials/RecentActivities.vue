<script setup>
import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { 
    Activity, ChevronRight, Plus, Edit, Trash2, Eye, 
    CheckCircle, Settings, User, Calendar
} from 'lucide-vue-next';

const props = defineProps({
    activities: Array,
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const items = document.querySelectorAll('.activity-item');
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

const getActivityIcon = (title) => {
    const lowerTitle = title?.toLowerCase() || '';
    if (lowerTitle.includes('create') || lowerTitle.includes('tambah') || lowerTitle.includes('buat')) return Plus;
    if (lowerTitle.includes('update') || lowerTitle.includes('edit') || lowerTitle.includes('ubah')) return Edit;
    if (lowerTitle.includes('delete') || lowerTitle.includes('hapus')) return Trash2;
    if (lowerTitle.includes('view') || lowerTitle.includes('lihat')) return Eye;
    if (lowerTitle.includes('confirm') || lowerTitle.includes('konfirmasi')) return CheckCircle;
    if (lowerTitle.includes('setting') || lowerTitle.includes('pengaturan')) return Settings;
    if (lowerTitle.includes('login') || lowerTitle.includes('user')) return User;
    return Activity;
};

const getActivityColor = (color) => {
    const colors = {
        emerald: 'from-emerald-500 to-teal-600',
        blue: 'from-blue-500 to-indigo-600',
        amber: 'from-amber-500 to-orange-600',
        red: 'from-red-500 to-rose-600',
        violet: 'from-violet-500 to-purple-600',
        pink: 'from-pink-500 to-rose-600',
        cyan: 'from-cyan-500 to-blue-600',
        gray: 'from-gray-500 to-slate-600',
    };
    return colors[color] || colors.gray;
};

const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const now = new Date();
    const diffMs = now - d;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays < 7) return `${diffDays} hari lalu`;
    
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <Activity class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Aktivitas Terbaru</h3>
                        <p class="text-[10px] text-gray-500">Riwayat aktivitas sistem</p>
                    </div>
                </div>
                <Link 
                    href="/admin/activity-logs" 
                    class="group flex items-center gap-1 text-[10px] font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                >
                    <span>Semua Log</span>
                    <ChevronRight class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
        
        <!-- Timeline -->
        <div class="p-4">
            <div class="relative">
                <!-- Timeline line -->
                <div class="absolute left-[17px] top-2 bottom-2 w-0.5 bg-gradient-to-b from-blue-200 via-indigo-200 to-transparent"></div>
                
                <!-- Activity Items -->
                <div class="space-y-3">
                    <div 
                        v-for="(activity, index) in activities" 
                        :key="index"
                        class="activity-item group relative flex items-start gap-3 pl-1"
                    >
                        <!-- Icon -->
                        <div class="relative z-10">
                            <div :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center shadow-md transition-transform group-hover:scale-110 bg-gradient-to-br',
                                getActivityColor(activity.color)
                            ]">
                                <component :is="getActivityIcon(activity.title)" class="w-3.5 h-3.5 text-white" />
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0 pb-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ activity.title }}</p>
                                    <p class="text-[11px] text-gray-500 truncate">{{ activity.description }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-[9px] text-gray-400 whitespace-nowrap">
                                    <Calendar class="w-2.5 h-2.5" />
                                    {{ formatDate(activity.time) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-if="!activities?.length" class="text-center py-6">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                        <Activity class="w-6 h-6 text-gray-400" />
                    </div>
                    <p class="text-xs font-semibold text-gray-600">Belum ada aktivitas</p>
                    <p class="text-[10px] text-gray-400">Aktivitas akan muncul di sini</p>
                </div>
            </div>
        </div>
    </div>
</template>
