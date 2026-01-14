<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';

const props = defineProps({
    collapsed: Boolean,
    admin: Object,
});

const page = usePage();

// Get admin from page.props (shared via HandleInertiaRequests) or from prop
const adminData = computed(() => page.props.admin || props.admin);

const logout = () => {
    router.post('/admin/logout');
};
</script>

<template>
    <div class="relative p-3 border-t border-white/10 bg-gradient-to-t from-black/20 to-transparent">
        <!-- User Info Card -->
        <div 
            v-if="!collapsed"
            class="flex items-center gap-2.5 p-2.5 rounded-lg bg-white/5 hover:bg-white/10 transition-all cursor-pointer border border-white/5"
        >
            <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-green-600 rounded-lg flex items-center justify-center shadow-lg ring-2 ring-emerald-500/20">
                <span class="text-white font-bold text-xs">{{ adminData?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ adminData?.name || 'Admin' }}</p>
                <p class="text-[10px] text-emerald-400/80">
                    {{ adminData?.role?.replace('_', ' ')?.replace(/\b\w/g, l => l.toUpperCase()) || 'Administrator' }}
                </p>
            </div>
        </div>

        <!-- Collapsed Avatar -->
        <div v-else class="flex justify-center">
            <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-green-600 rounded-lg flex items-center justify-center shadow-lg ring-2 ring-emerald-500/20">
                <span class="text-white font-bold text-xs">{{ adminData?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
            </div>
        </div>

        <!-- Logout Button -->
        <button 
            @click="logout"
            class="w-full flex items-center justify-center gap-1.5 px-3 py-2 mt-2 text-xs text-slate-400 hover:text-white hover:bg-red-500/20 rounded-lg transition-all border border-transparent hover:border-red-500/20"
        >
            <LogOut class="w-3.5 h-3.5" />
            <span v-if="!collapsed">Keluar</span>
        </button>
    </div>
</template>
