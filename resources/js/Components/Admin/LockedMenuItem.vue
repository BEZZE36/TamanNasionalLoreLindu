<script setup>
/**
 * LockedMenuItem Component
 * 
 * Displays a menu item that is visible but locked (not clickable).
 * Used when user has "access" permission but not "read" permission.
 */
import { Lock } from 'lucide-vue-next';

defineProps({
    icon: {
        type: Object,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    collapsed: {
        type: Boolean,
        default: false
    },
    colorClass: {
        type: String,
        default: 'text-gray-500'
    }
});
</script>

<template>
    <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-500 cursor-not-allowed opacity-50">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-500/20 to-gray-600/30 border-gray-500/20 flex items-center justify-center border relative">
            <component :is="icon" class="w-4 h-4 text-gray-500" />
            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-slate-700 border border-slate-600 flex items-center justify-center">
                <Lock class="w-2.5 h-2.5 text-slate-400" />
            </div>
        </div>
        <span v-if="!collapsed" class="text-sm font-medium">{{ label }}</span>
        <Lock v-if="!collapsed" class="w-3 h-3 text-slate-500 ml-auto" />
    </div>
</template>
