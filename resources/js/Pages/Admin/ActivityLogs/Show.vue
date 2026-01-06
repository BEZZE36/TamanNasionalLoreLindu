<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Activity, Calendar, User, Monitor, Globe, Code, ChevronRight } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    activityLog: Object
});

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const getActionColor = (action) => {
    const colors = { create: 'emerald', update: 'blue', delete: 'red', login: 'purple', logout: 'gray', view: 'sky' };
    return colors[action] || 'gray';
};

const parseProperties = (props) => {
    if (!props) return null;
    if (typeof props === 'string') {
        try { return JSON.parse(props); } catch { return null; }
    }
    return props;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-slate-600 to-gray-800 rounded-2xl flex items-center justify-center shadow-xl">
                    <Activity class="w-7 h-7 text-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Detail <span class="text-transparent bg-clip-text bg-gradient-to-r from-slate-600 to-gray-800">Log Aktivitas</span></h1>
                    <p class="text-gray-500 text-sm">#{{ activityLog?.id }}</p>
                </div>
            </div>
            <Link href="/admin/activity-logs" class="px-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all flex items-center gap-2">
                <ArrowLeft class="w-4 h-4" /> Kembali
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Activity Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-start gap-4 mb-6">
                        <div :class="[`w-12 h-12 bg-${getActionColor(activityLog?.action)}-100 rounded-xl flex items-center justify-center`]">
                            <Activity :class="`w-6 h-6 text-${getActionColor(activityLog?.action)}-600`" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">{{ activityLog?.description || 'Aktivitas' }}</h3>
                            <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                <div class="flex items-center gap-1"><Calendar class="w-4 h-4" /> {{ formatDate(activityLog?.created_at) }}</div>
                                <span :class="`px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-${getActionColor(activityLog?.action)}-100 text-${getActionColor(activityLog?.action)}-700`">{{ activityLog?.action }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Model</div>
                            <div class="font-semibold text-gray-900">{{ activityLog?.subject_type?.split('\\').pop() || '-' }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Subject ID</div>
                            <div class="font-semibold text-gray-900">#{{ activityLog?.subject_id || '-' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Properties -->
                <div v-if="parseProperties(activityLog?.properties)" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><Code class="w-5 h-5 text-gray-500" /> Properties</h3>
                    <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 overflow-x-auto text-sm font-mono">{{ JSON.stringify(parseProperties(activityLog?.properties), null, 2) }}</pre>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Actor -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><User class="w-5 h-5 text-purple-500" /> Aktor</h3>
                    <div class="space-y-3">
                        <div v-if="activityLog?.admin" class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl">
                            <div class="w-10 h-10 bg-purple-200 rounded-full flex items-center justify-center font-bold text-purple-700">{{ activityLog.admin.name?.charAt(0) }}</div>
                            <div><div class="font-semibold text-gray-900">{{ activityLog.admin.name }}</div><div class="text-xs text-gray-500">Admin</div></div>
                        </div>
                        <div v-else-if="activityLog?.user" class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl">
                            <div class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center font-bold text-blue-700">{{ activityLog.user.name?.charAt(0) }}</div>
                            <div><div class="font-semibold text-gray-900">{{ activityLog.user.name }}</div><div class="text-xs text-gray-500">User</div></div>
                        </div>
                        <div v-else class="text-gray-500 text-sm">Sistem / Guest</div>
                    </div>
                </div>

                <!-- Technical Info -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><Monitor class="w-5 h-5 text-blue-500" /> Info Teknis</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">IP Address</span>
                            <span class="font-mono text-gray-900">{{ activityLog?.ip_address || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">User Agent</span>
                            <span class="text-gray-900 text-xs truncate max-w-[150px]" :title="activityLog?.user_agent">{{ activityLog?.user_agent || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-500">Waktu</span>
                            <span class="text-gray-900">{{ formatDate(activityLog?.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
