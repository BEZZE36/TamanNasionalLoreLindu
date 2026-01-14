<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Bell, Check, Trash2, CheckCheck, ExternalLink, BellOff, ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    notifications: Object
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const processing = ref(false);

const markAsRead = (id) => {
    router.post(`/admin/notifications/${id}/read`, {}, {
        preserveScroll: true
    });
};

const markAllRead = () => {
    processing.value = true;
    router.post('/admin/notifications/mark-all-read', {}, {
        preserveScroll: true,
        onFinish: () => processing.value = false
    });
};

const deleteNotification = (id) => {
    router.delete(`/admin/notifications/${id}`, {
        preserveScroll: true
    });
};

const clearAll = () => {
    if (!confirm('Hapus semua notifikasi? Aksi ini tidak dapat dibatalkan.')) return;
    processing.value = true;
    router.delete('/admin/notifications/clear', {
        preserveScroll: true,
        onFinish: () => processing.value = false
    });
};

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    
    if (diff < 60000) return 'Baru saja';
    if (diff < 3600000) return `${Math.floor(diff / 60000)} menit lalu`;
    if (diff < 86400000) return `${Math.floor(diff / 3600000)} jam lalu`;
    if (diff < 604800000) return `${Math.floor(diff / 86400000)} hari lalu`;
    
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                        <Bell class="w-4 h-4 text-white" />
                    </div>
                    Notifikasi
                </h1>
                <p class="text-gray-500 mt-1">Semua notifikasi sistem dan aktivitas</p>
            </div>
            <div class="flex gap-3">
                <button @click="markAllRead" :disabled="processing"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all disabled:opacity-50">
                    <CheckCheck class="w-4 h-4" />
                    Tandai Semua Dibaca
                </button>
                <button @click="clearAll" :disabled="processing"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-100 transition-all disabled:opacity-50">
                    <Trash2 class="w-4 h-4" />
                    Hapus Semua
                </button>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="fade">
            <div v-if="flash.success" class="flex items-center gap-3 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700">
                <Check class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium">{{ flash.success }}</span>
            </div>
        </Transition>

        <!-- Notifications List -->
        <div class="space-y-4">
            <TransitionGroup name="list">
                <div v-for="notification in notifications.data" :key="notification.id"
                    :class="[
                        'bg-white rounded-2xl shadow-lg border overflow-hidden transition-all hover:shadow-xl',
                        !notification.read_at ? 'border-l-4 border-l-indigo-500 bg-gradient-to-r from-indigo-50/50 to-white' : 'border-gray-100'
                    ]">
                    <div class="p-5 flex items-start gap-3">
                        <!-- Icon -->
                        <div :class="[
                            'w-14 h-14 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0',
                            notification.data?.color || 'bg-blue-100'
                        ]">
                            {{ notification.data?.icon || '📌' }}
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 :class="['font-semibold', !notification.read_at ? 'text-gray-900' : 'text-gray-700']">
                                        {{ notification.data?.title || 'Notifikasi' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ notification.data?.message || '' }}</p>
                                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                                        <Bell class="w-3 h-3" />
                                        {{ formatTime(notification.created_at) }}
                                    </p>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button v-if="!notification.read_at" @click="markAsRead(notification.id)"
                                        class="p-2.5 text-gray-400 hover:text-green-600 rounded-xl hover:bg-green-50 transition-all"
                                        title="Tandai dibaca">
                                        <Check class="w-5 h-5" />
                                    </button>
                                    <button @click="deleteNotification(notification.id)"
                                        class="p-2.5 text-gray-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition-all"
                                        title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            
                            <!-- URL Link -->
                            <a v-if="notification.data?.url" :href="notification.data.url"
                                class="inline-flex items-center gap-1 mt-3 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                                Lihat Detail <ExternalLink class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </TransitionGroup>

            <!-- Empty State -->
            <div v-if="!notifications.data?.length" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <BellOff class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak ada notifikasi</h3>
                <p class="text-gray-500">Anda akan menerima notifikasi saat ada aktivitas penting</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="notifications.meta?.last_page > 1" class="flex items-center justify-between">
            <p class="text-[11px] text-gray-500">
                Menampilkan {{ notifications.meta.from }} - {{ notifications.meta.to }} dari {{ notifications.meta.total }} notifikasi
            </p>
            <div class="flex items-center gap-2">
                <Link v-if="notifications.links?.prev" :href="notifications.links.prev"
                    class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                    <ChevronLeft class="w-5 h-5" />
                </Link>
                <span class="px-4 py-2 text-sm font-medium text-gray-700">
                    {{ notifications.meta?.current_page }} / {{ notifications.meta?.last_page }}
                </span>
                <Link v-if="notifications.links?.next" :href="notifications.links.next"
                    class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                    <ChevronRight class="w-5 h-5" />
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.list-enter-active, .list-leave-active {
    transition: all 0.3s ease;
}
.list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}
.list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
