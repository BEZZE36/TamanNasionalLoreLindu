<script setup>
import { Server, Database, HardDrive, Cpu, Zap } from 'lucide-vue-next';

defineProps({
    systemStatus: Object
});
</script>

<template>
    <div v-if="systemStatus" class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-slate-500 to-gray-600 shadow-lg shadow-slate-500/30">
                    <Server class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Status Sistem</h2>
                    <p class="text-[11px] text-gray-500">Monitoring server dan database</p>
                </div>
            </div>
        </div>
        <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-100">
                <div class="flex items-center gap-2 mb-2">
                    <Database class="w-4 h-4 text-emerald-600" />
                    <span class="text-sm font-medium text-emerald-700">Database</span>
                </div>
                <p class="text-lg font-bold text-emerald-700">{{ systemStatus.database || 'OK' }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100">
                <div class="flex items-center gap-2 mb-2">
                    <HardDrive class="w-4 h-4 text-blue-600" />
                    <span class="text-sm font-medium text-blue-700">Storage</span>
                </div>
                <p class="text-lg font-bold text-blue-700">{{ systemStatus.storage || 'OK' }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-purple-50 to-violet-50 border border-purple-100">
                <div class="flex items-center gap-2 mb-2">
                    <Cpu class="w-4 h-4 text-purple-600" />
                    <span class="text-sm font-medium text-purple-700">Cache</span>
                </div>
                <p class="text-lg font-bold text-purple-700">{{ systemStatus.cache || 'OK' }}</p>
            </div>
            <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100">
                <div class="flex items-center gap-2 mb-2">
                    <Zap class="w-4 h-4 text-amber-600" />
                    <span class="text-sm font-medium text-amber-700">Queue</span>
                </div>
                <p class="text-lg font-bold text-amber-700">{{ systemStatus.queue || 'OK' }}</p>
            </div>
        </div>
    </div>
</template>
