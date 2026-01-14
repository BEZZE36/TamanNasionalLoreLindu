<script setup>
import { onMounted, onBeforeUnmount, computed, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { 
    AlertCircle, Clock, MessageSquare, CheckCircle, UserPlus, 
    Bell, ChevronRight, Sparkles
} from 'lucide-vue-next';

const props = defineProps({
    attention: Object,
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const items = document.querySelectorAll('.attention-item');
        if (items.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(items, 
                    { opacity: 0, x: -15, scale: 0.95 }, 
                    { opacity: 1, x: 0, scale: 1, duration: 0.4, stagger: 0.1, ease: 'back.out(1.7)' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const attentionItems = computed(() => {
    const items = [];
    
    if (props.attention?.pending_bookings > 0) {
        items.push({
            label: 'Booking Pending',
            desc: 'Butuh konfirmasi',
            count: props.attention.pending_bookings,
            href: '/admin/bookings?status=pending',
            icon: Clock,
            gradient: 'from-amber-500 to-orange-600',
            bgClass: 'from-amber-50 to-orange-50',
            textClass: 'text-amber-900',
            descClass: 'text-amber-600',
            borderClass: 'border-amber-200/60',
            urgent: true
        });
    }
    
    if (props.attention?.unread_feedback > 0) {
        items.push({
            label: 'Feedback Baru',
            desc: 'Belum dibaca',
            count: props.attention.unread_feedback,
            href: '/admin/feedback',
            icon: MessageSquare,
            gradient: 'from-blue-500 to-indigo-600',
            bgClass: 'from-blue-50 to-indigo-50',
            textClass: 'text-blue-900',
            descClass: 'text-blue-600',
            borderClass: 'border-blue-200/60'
        });
    }
    
    if (props.attention?.today_users > 0) {
        items.push({
            label: 'Pengguna Baru',
            desc: 'Pendaftar hari ini',
            count: props.attention.today_users,
            href: '/admin/users',
            icon: UserPlus,
            gradient: 'from-emerald-500 to-teal-600',
            bgClass: 'from-emerald-50 to-teal-50',
            textClass: 'text-emerald-900',
            descClass: 'text-emerald-600',
            borderClass: 'border-emerald-200/60'
        });
    }
    
    return items;
});

const hasAttention = computed(() => attentionItems.value.length > 0);
</script>

<template>
    <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/30">
                        <AlertCircle class="w-4 h-4 text-white" />
                    </div>
                    <div v-if="hasAttention" class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-red-500 animate-ping"></div>
                    <div v-if="hasAttention" class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-red-500"></div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Perlu Perhatian</h3>
                    <p class="text-[10px] text-gray-500">{{ hasAttention ? `${attentionItems.length} item` : 'Semua beres!' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
            <!-- Attention Items -->
            <div v-if="hasAttention" class="space-y-2.5">
                <Link 
                    v-for="(item, index) in attentionItems" 
                    :key="index"
                    :href="item.href"
                    :class="[
                        'attention-item group flex items-center justify-between p-3 rounded-xl border transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 bg-gradient-to-r',
                        item.bgClass,
                        item.borderClass
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <div :class="['w-9 h-9 rounded-lg flex items-center justify-center shadow-md group-hover:scale-110 transition-transform bg-gradient-to-br', item.gradient]">
                            <component :is="item.icon" class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <span :class="['text-xs font-bold', item.textClass]">{{ item.label }}</span>
                            <p :class="['text-[10px]', item.descClass]">{{ item.desc }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span :class="['px-2.5 py-1 rounded-lg text-xs font-black text-white shadow-md bg-gradient-to-r', item.gradient, item.urgent ? 'animate-pulse' : '']">
                            {{ item.count }}
                        </span>
                        <ChevronRight :class="['w-4 h-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all', item.textClass]" />
                    </div>
                </Link>
            </div>
            
            <!-- All Clear State -->
            <div v-else class="text-center py-6">
                <div class="w-14 h-14 mx-auto rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center mb-3">
                    <CheckCircle class="w-7 h-7 text-emerald-600" />
                </div>
                <p class="text-sm font-bold text-gray-700">Semua Beres!</p>
                <p class="text-[11px] text-gray-500 mt-1">Tidak ada yang perlu ditangani</p>
                <div class="flex items-center justify-center gap-1 mt-2">
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                    <span class="text-[10px] text-amber-600 font-medium">Kerja bagus!</span>
                </div>
            </div>
        </div>
    </div>
</template>
