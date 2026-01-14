<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { 
    Bell, Check, Trash2, Clock, ChevronRight, Compass, 
    CreditCard, ExternalLink
} from 'lucide-vue-next';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    notifications: Object // Paginated
});

// Mark all as read
const markAllAsRead = () => {
    router.post('/notifications/mark-all-read', {}, {
        preserveScroll: true,
        preserveState: true
    });
};

// Clear all notifications
const clearAll = () => {
    if (confirm('Apakah Anda yakin ingin menghapus semua notifikasi?')) {
        router.delete('/notifications/clear-all', {
            preserveScroll: true,
            preserveState: true
        });
    }
};

// Delete single notification
const deleteNotification = (id) => {
    if (confirm('Hapus notifikasi ini?')) {
        router.delete(`/notifications/${id}`, {
            preserveScroll: true,
            preserveState: true
        });
    }
};

// Mark as read and navigate
const viewNotification = (notification) => {
    if (!notification.is_read) {
        router.post(`/notifications/${notification.id}/read`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.link) {
                    window.location.href = notification.link;
                }
            }
        });
    } else if (notification.link) {
        window.location.href = notification.link;
    }
};
</script>

<template>
    <Head title="Notifikasi" />

    <div>
        <!-- Hero Header -->
        <section class="relative pt-32 pb-16 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-cyan-400/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 animate-pulse"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="animate-fade-in">
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white/90 text-xs font-medium mb-4">
                            <Bell class="w-4 h-4" />
                            Notifikasi
                        </span>
                        <h1 class="text-3xl md:text-4xl font-black text-white leading-tight">Notifikasi 🔔</h1>
                        <p class="text-white/70 mt-2 max-w-lg">Pantau status transaksi dan informasi terbaru Anda</p>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div v-if="notifications.data?.length > 0" class="flex gap-3">
                        <button @click="markAllAsRead"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-xl hover:bg-white/20 transition-all text-sm font-medium">
                            <Check class="w-4 h-4" />
                            Tandai Semua Dibaca
                        </button>
                        <button @click="clearAll"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-500/20 backdrop-blur-md border border-red-400/30 text-white rounded-xl hover:bg-red-500/30 transition-all text-sm font-medium">
                            <Trash2 class="w-4 h-4" />
                            Hapus Semua
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content -->
        <section class="py-12 bg-gradient-to-b from-gray-50 to-white min-h-[60vh] relative overflow-hidden">
            <div class="absolute top-0 right-0 w-72 h-72 bg-primary-500/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Notification List -->
                    <template v-if="notifications.data?.length > 0">
                        <div class="divide-y divide-gray-100">
                            <div v-for="(notification, index) in notifications.data" :key="notification.id"
                                :class="['p-5 hover:bg-gray-50/80 transition-all duration-300 group',
                                         !notification.is_read ? 'bg-primary-50/50 border-l-4 border-primary-500' : '']"
                                :style="{ animationDelay: `${index * 0.05}s` }">
                                <div class="flex gap-4">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0">
                                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-sm transition-transform duration-300 group-hover:scale-110', notification.color_class || 'bg-gray-100']">
                                            {{ notification.icon_emoji || '🔔' }}
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h4 :class="['font-bold', !notification.is_read ? 'text-primary-700' : 'text-gray-900']">
                                                    {{ notification.title }}
                                                    <span v-if="!notification.is_read" class="inline-block w-2 h-2 bg-primary-500 rounded-full ml-2 animate-pulse"></span>
                                                </h4>
                                                <p class="text-gray-600 mt-1 text-sm">{{ notification.message }}</p>
                                                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                                                    <Clock class="w-3 h-3" />
                                                    {{ notification.time_ago }}
                                                </p>
                                            </div>
                                            
                                            <div class="flex items-center gap-3">
                                                <button v-if="notification.link" @click="viewNotification(notification)"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition-colors text-sm font-semibold">
                                                    Lihat Detail
                                                    <ChevronRight class="w-4 h-4" />
                                                </button>

                                                <button @click="deleteNotification(notification.id)"
                                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                                    <Trash2 class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Button for pending payments -->
                                        <div v-if="notification.type === 'payment_pending' && notification.link" class="mt-4">
                                            <a :href="notification.link" 
                                               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-xl hover:from-primary-700 hover:to-primary-800 transition-all text-sm font-semibold shadow-lg shadow-primary-500/25">
                                                <CreditCard class="w-4 h-4" />
                                                Bayar Sekarang
                                                <ExternalLink class="w-4 h-4" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="notifications.links" class="p-6 border-t border-gray-100 bg-gray-50/50 flex justify-center gap-2">
                            <template v-for="link in notifications.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url"
                                    :class="['px-4 py-2 rounded-lg text-sm font-medium transition-colors', 
                                             link.active ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 shadow']"
                                    v-html="link.label" />
                                <span v-else class="px-4 py-2 text-gray-400 text-sm" v-html="link.label" />
                            </template>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center shadow-lg animate-bounce">
                            <Bell class="w-12 h-12 text-gray-400" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Tidak ada notifikasi</h3>
                        <p class="text-gray-500 mb-6">Anda akan menerima pemberitahuan di sini saat ada aktivitas baru.</p>
                        <Link href="/destinations" 
                            class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                            <Compass class="w-5 h-5" />
                            Jelajahi Destinasi
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
