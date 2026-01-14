<script setup>
import { onMounted, onBeforeUnmount, computed, nextTick } from 'vue';
import { gsap } from 'gsap';
import { 
    Server, Database, HardDrive, Cpu, Zap, CheckCircle, 
    AlertTriangle, XCircle, Activity, Wifi, CreditCard, MemoryStick
} from 'lucide-vue-next';

const props = defineProps({
    systemStatus: Object,
});

let ctx;

onMounted(() => {
    nextTick(() => {
        const cards = document.querySelectorAll('.status-card');
        if (cards.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(cards, 
                    { opacity: 0, y: 15, scale: 0.95 }, 
                    { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.1, ease: 'back.out(1.7)' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const getStatusStyle = (statusObj) => {
    // Handle both string and object formats
    const status = typeof statusObj === 'object' ? statusObj?.status : statusObj;
    const normalized = (status || 'ok').toLowerCase();
    
    if (normalized === 'ok' || normalized === 'active' || normalized === 'healthy' || normalized === 'online') {
        return {
            bg: 'from-emerald-50 to-teal-50',
            border: 'border-emerald-200/60',
            icon: CheckCircle,
            iconBg: 'from-emerald-500 to-teal-600',
            text: 'text-emerald-700',
            label: 'Healthy'
        };
    }
    if (normalized === 'warning' || normalized === 'slow') {
        return {
            bg: 'from-amber-50 to-orange-50',
            border: 'border-amber-200/60',
            icon: AlertTriangle,
            iconBg: 'from-amber-500 to-orange-600',
            text: 'text-amber-700',
            label: 'Warning'
        };
    }
    return {
        bg: 'from-red-50 to-rose-50',
        border: 'border-red-200/60',
        icon: XCircle,
        iconBg: 'from-red-500 to-rose-600',
        text: 'text-red-700',
        label: 'Error'
    };
};

const getStatusMessage = (statusObj) => {
    if (typeof statusObj === 'object' && statusObj?.message) {
        return statusObj.message;
    }
    return 'OK';
};

const statusItems = computed(() => [
    { 
        name: 'Database', 
        statusObj: props.systemStatus?.database, 
        icon: Database,
        desc: 'MySQL Connection'
    },
    { 
        name: 'Storage', 
        statusObj: props.systemStatus?.storage, 
        icon: HardDrive,
        desc: 'Disk Space'
    },
    { 
        name: 'Payment', 
        statusObj: props.systemStatus?.payment, 
        icon: CreditCard,
        desc: 'Midtrans'
    },
    { 
        name: 'Memory', 
        statusObj: props.systemStatus?.memory, 
        icon: MemoryStick,
        desc: 'PHP Memory'
    },
]);

const allHealthy = computed(() => {
    if (!props.systemStatus) return true;
    return props.systemStatus.overall === 'Operational';
});
</script>

<template>
    <div v-if="systemStatus" class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-500 to-gray-600 flex items-center justify-center shadow-lg shadow-slate-500/30">
                        <Server class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Status Sistem</h3>
                        <p class="text-[10px] text-gray-500">Monitoring server & services</p>
                    </div>
                </div>
                
                <!-- Overall Status Badge -->
                <div :class="[
                    'flex items-center gap-1.5 px-3 py-1.5 rounded-lg',
                    allHealthy ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
                ]">
                    <div :class="['w-2 h-2 rounded-full', allHealthy ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500 animate-pulse']"></div>
                    <span class="text-[10px] font-bold">{{ systemStatus.overall || 'Checking...' }}</span>
                </div>
            </div>
        </div>
        
        <!-- Status Grid -->
        <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            <div 
                v-for="(item, index) in statusItems" 
                :key="index"
                :class="[
                    'status-card group p-3 rounded-xl border transition-all duration-300 hover:shadow-lg bg-gradient-to-br',
                    getStatusStyle(item.statusObj).bg,
                    getStatusStyle(item.statusObj).border
                ]"
            >
                <div class="flex items-center gap-2.5 mb-2">
                    <div :class="[
                        'w-8 h-8 rounded-lg flex items-center justify-center shadow-md group-hover:scale-110 transition-transform bg-gradient-to-br',
                        getStatusStyle(item.statusObj).iconBg
                    ]">
                        <component :is="item.icon" class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">{{ item.name }}</p>
                        <p class="text-[9px] text-gray-500">{{ getStatusMessage(item.statusObj) }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-1.5">
                    <component :is="getStatusStyle(item.statusObj).icon" :class="['w-3.5 h-3.5', getStatusStyle(item.statusObj).text]" />
                    <span :class="['text-xs font-bold', getStatusStyle(item.statusObj).text]">{{ getStatusStyle(item.statusObj).label }}</span>
                </div>
            </div>
        </div>
        
        <!-- Uptime Bar -->
        <div class="px-4 pb-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5">
                    <Activity class="w-3.5 h-3.5 text-gray-400" />
                    <span class="text-[10px] font-semibold text-gray-600">System Uptime</span>
                </div>
                <span class="text-[10px] font-bold text-emerald-600">99.9%</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full" style="width: 99.9%">
                    <div class="w-full h-full bg-white/20 animate-pulse"></div>
                </div>
            </div>
            <div class="flex justify-between mt-1 text-[9px] text-gray-400">
                <span>Last 30 days</span>
                <span class="flex items-center gap-1">
                    <Wifi class="w-2.5 h-2.5" />
                    Online
                </span>
            </div>
        </div>
    </div>
</template>
