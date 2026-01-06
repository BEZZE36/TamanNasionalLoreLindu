<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Lock, AlertTriangle, Eye } from 'lucide-vue-next';

/**
 * ReadOnlyBanner Component
 * 
 * Shows a banner when user is in read-only mode.
 * Provides visual feedback that forms are locked.
 */
const page = usePage();
const admin = computed(() => page.props.admin);
const permissionLevel = computed(() => admin.value?.permissionLevel || 'write');
const isReadOnly = computed(() => permissionLevel.value === 'read');
</script>

<template>
    <div v-if="isReadOnly" 
        class="mb-4 flex items-center gap-3 rounded-xl bg-amber-50 border-2 border-amber-300 px-5 py-4 shadow-lg shadow-amber-100">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
            <Eye class="w-6 h-6 text-white" />
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <Lock class="w-4 h-4 text-amber-700" />
                <p class="text-sm font-bold text-amber-800">Mode Baca Saja</p>
            </div>
            <p class="text-xs text-amber-600">
                Anda hanya memiliki akses untuk <strong>melihat data</strong> di halaman ini. 
                Semua form dan tombol aksi dinonaktifkan.
            </p>
        </div>
        <div class="px-3 py-1.5 bg-amber-200 rounded-lg">
            <span class="text-xs font-bold text-amber-800">READ ONLY</span>
        </div>
    </div>
</template>
